import fs from "node:fs";
import path from "node:path";
import https from "node:https";

const SPREADSHEET_ID = "1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4";
const OUTPUT_FILE = path.resolve("storage/app/competitions.json");

// Custom lightweight env loader to read from Laravel's .env file
function loadEnv() {
  try {
    const envPath = path.resolve(".env");
    if (fs.existsSync(envPath)) {
      const envContent = fs.readFileSync(envPath, "utf8");
      for (const line of envContent.split("\n")) {
        const match = line.match(/^\s*([^#\s=]+)\s*=\s*(.*)$/);
        if (match) {
          let val = match[2].trim();
          if (val.startsWith('"') && val.endsWith('"')) {
            val = val.slice(1, -1);
          }
          if (val.startsWith("'") && val.endsWith("'")) {
            val = val.slice(1, -1);
          }
          process.env[match[1]] = val;
        }
      }
    }
  } catch (e) {
    // Ignore error loading env
  }
}

// RFC-4180 compliant CSV Parser to handle nested quotes and newlines
function parseCSV(text) {
  const result = [];
  let row = [""];
  let inQuotes = false;

  for (let i = 0; i < text.length; i++) {
    const char = text[i];
    const nextChar = text[i + 1];

    if (char === '"') {
      if (inQuotes && nextChar === '"') {
        row[row.length - 1] += '"';
        i++; // skip next quote
      } else {
        inQuotes = !inQuotes;
      }
    } else if (char === "," && !inQuotes) {
      row.push("");
    } else if ((char === "\r" || char === "\n") && !inQuotes) {
      if (char === "\r" && nextChar === "\n") {
        i++; // skip linefeed
      }
      result.push(row);
      row = [""];
    } else {
      row[row.length - 1] += char;
    }
  }

  if (row.length > 1 || row[0] !== "") {
    result.push(row);
  }

  return result;
}

function fetchURL(url) {
  return new Promise((resolve, reject) => {
    https
      .get(url, (res) => {
        // Automatically follow HTTP redirects
        if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
          const redirectUrl = new URL(res.headers.location, url).toString();
          resolve(fetchURL(redirectUrl));
          return;
        }

        if (res.statusCode !== 200) {
          reject(new Error(`HTTP request failed: Status ${res.statusCode}`));
          return;
        }

        let data = "";
        res.on("data", (chunk) => {
          data += chunk;
        });
        res.on("end", () => resolve(data));
      })
      .on("error", reject);
  });
}

// Extract sheet names and GIDs using official API
async function getSheetsViaAPI(apiKey) {
  console.log("Using Google Sheets API to fetch spreadsheet tabs...");
  const apiUrl = `https://sheets.googleapis.com/v4/spreadsheets/${SPREADSHEET_ID}?key=${apiKey}`;
  const responseText = await fetchURL(apiUrl);
  const data = JSON.parse(responseText);

  if (!data.sheets) {
    throw new Error("Invalid response format from Google Sheets API");
  }

  return data.sheets.map((s) => ({
    gid: s.properties.sheetId.toString(),
    name: s.properties.title,
  }));
}

// Fallback: Extract sheet names and GIDs from public HTML view page
async function getSheetsViaScraping() {
  console.log("No API key found. Falling back to public HTML view page parsing...");
  const editPageUrl = `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}/edit`;
  const html = await fetchURL(editPageUrl);

  // Unescape string payload characters in HTML source
  const cleanHtml = html
    .replace(/\\"/g, '"')
    .replace(/\\{/g, "{")
    .replace(/\\}/g, "}");

  const regexGidName = /"(\d+)"\s*,\s*\[\s*\{\s*"1"\s*:\s*\[\s*\[\s*0\s*,\s*0\s*,\s*"([^"]+)"/g;
  const sheets = [];
  let match;

  while ((match = regexGidName.exec(cleanHtml)) !== null) {
    sheets.push({
      gid: match[1],
      name: match[2],
    });
  }

  return sheets;
}

async function main() {
  loadEnv();
  const apiKey = process.env.GOOGLE_API_KEY;

  try {
    let sheets = [];
    if (apiKey) {
      sheets = await getSheetsViaAPI(apiKey);
    } else {
      sheets = await getSheetsViaScraping();
    }

    // Filter sheets containing 'lomba' in their name
    const lombaSheets = sheets.filter((s) => /lomba/i.test(s.name));
    console.log(`Found ${lombaSheets.length} matching competition sheets:`);
    for (const s of lombaSheets) {
      console.log(`- Tab Name: "${s.name}" (GID: ${s.gid})`);
    }

    const allCompetitions = [];

    // Process each matching sheet
    for (const sheet of lombaSheets) {
      // Parse the month (e.g. "Mei - Lomba" -> "Mei")
      const monthMatch = sheet.name.match(/^([^-]+)/);
      const month = monthMatch ? monthMatch[1].trim() : "";

      console.log(`Processing sheet: "${sheet.name}" (Month: ${month})...`);

      const csvUrl = `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}/export?format=csv&gid=${sheet.gid}`;
      const csvText = await fetchURL(csvUrl);
      const parsed = parseCSV(csvText);

      if (parsed.length <= 1) {
        continue;
      }

      for (let i = 1; i < parsed.length; i++) {
        const row = parsed[i];
        if (row.length < 2) {
          continue;
        }

        const name = row[1]?.trim() || "";
        const organizer = row[2]?.trim() || "";

        // Skip empty spacer rows
        if (!name && !organizer) {
          continue;
        }

        allCompetitions.push({
          no: Number.parseInt(row[0]?.trim() || i.toString(), 10),
          name: name,
          organizer: organizer,
          description: row[3]?.trim() || "",
          timeline: row[4]?.trim() || "",
          status: row[5]?.trim() || "Open",
          qr_code_link: row[6]?.trim() || null,
          link: row[7]?.trim() || "",
          month: month, // Associated month from sheet tab name
        });
      }
    }

    // Ensure output target directory exists
    fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });

    // Output formatted JSON list
    fs.writeFileSync(
      OUTPUT_FILE,
      JSON.stringify(allCompetitions, null, 4),
      "utf8",
    );
    console.log(`Successfully compiled ${allCompetitions.length} total competition items.`);
    console.log(`Saved output payload to: ${OUTPUT_FILE}`);
    process.exit(0); // Exit process to clear active keep-alive socket handles
  } catch (error) {
    console.error("Execution failed:", error);
    process.exit(1);
  }
}

main();
