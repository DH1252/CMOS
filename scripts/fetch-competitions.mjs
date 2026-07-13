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

// Fetch and parse using Google Sheets API (handles cell rich links)
async function fetchCompetitionsViaAPI(apiKey, sheetName, month) {
  console.log(`Fetching sheet data via API for "${sheetName}"...`);
  const apiUrl = `https://sheets.googleapis.com/v4/spreadsheets/${SPREADSHEET_ID}?key=${apiKey}&includeGridData=true&ranges=${encodeURIComponent(sheetName)}!A1:Z100`;
  const responseText = await fetchURL(apiUrl);
  const data = JSON.parse(responseText);

  const sheet = data.sheets?.[0];
  const rows = sheet?.data?.[0]?.rowData || [];

  if (rows.length <= 1) {
    return [];
  }

  // Parse headers from the first row
  const headerCells = rows[0].values || [];
  const headers = headerCells.map((c) => (c.formattedValue || "").trim().toLowerCase());

  const getIndex = (aliases) => headers.findIndex((h) => aliases.includes(h));

  const noIdx = getIndex(["no"]);
  const nameIdx = getIndex(["nama lomba", "lomba", "name"]);
  const organizerIdx = getIndex(["penyelenggara", "organizer"]);
  const descIdx = getIndex(["deskripsi", "jenis perlombaan", "description"]);
  const timelineIdx = getIndex(["timeline lomba", "timeline pendaftaran", "timeline"]);
  const statusIdx = getIndex(["status"]);
  const qrIdx = getIndex(["qr code link", "qr code", "guidebook"]);
  const linkIdx = getIndex(["link", "link pendaftaran"]);
  const feeIdx = getIndex(["biaya pendaftaran"]);
  const memberIdx = getIndex(["jumlah anggota"]);

  const getCellText = (cell) => {
    if (!cell) return "";
    return cell.formattedValue || (cell.userEnteredValue && cell.userEnteredValue.stringValue) || "";
  };

  const getCellLink = (cell) => {
    if (!cell) return "";
    if (cell.hyperlink) return cell.hyperlink;
    if (cell.textFormatRuns && cell.textFormatRuns[0] && cell.textFormatRuns[0].format && cell.textFormatRuns[0].format.link) {
      return cell.textFormatRuns[0].format.link.uri || "";
    }
    return "";
  };

  const competitions = [];

  for (let i = 1; i < rows.length; i++) {
    const cells = rows[i].values || [];
    if (cells.length === 0) continue;

    const name = nameIdx !== -1 ? getCellText(cells[nameIdx]) : "";
    const organizer = organizerIdx !== -1 ? getCellText(cells[organizerIdx]) : "";

    // Skip empty spacer rows
    if (!name && !organizer) continue;

    let description = descIdx !== -1 ? getCellText(cells[descIdx]) : "";
    if (feeIdx !== -1) {
      const fee = getCellText(cells[feeIdx]);
      if (fee) description += `\n\n💰 Biaya Pendaftaran: ${fee}`;
    }
    if (memberIdx !== -1) {
      const members = getCellText(cells[memberIdx]);
      if (members) description += `\n👥 Jumlah Anggota: ${members}`;
    }

    // Link parsing (prefer extracted hyperlink URI, fallback to formatted value)
    const link = linkIdx !== -1 ? getCellLink(cells[linkIdx]) || getCellText(cells[linkIdx]) : "";
    const qrLink = qrIdx !== -1 ? getCellLink(cells[qrIdx]) || getCellText(cells[qrIdx]) : "";

    competitions.push({
      no: noIdx !== -1 ? Number.parseInt(getCellText(cells[noIdx]) || i, 10) : i,
      name,
      organizer,
      description,
      timeline: timelineIdx !== -1 ? getCellText(cells[timelineIdx]) : "",
      status: statusIdx !== -1 ? getCellText(cells[statusIdx]) : "Open",
      qr_code_link: qrLink || null,
      link: link,
      month: month,
    });
  }

  return competitions;
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

      if (apiKey) {
        // Use Sheets API with GridData to retrieve rich hyperlinks
        const sheetCompetitions = await fetchCompetitionsViaAPI(apiKey, sheet.name, month);
        allCompetitions.push(...sheetCompetitions);
      } else {
        // Fallback: download CSV (loses rich links but preserves dynamic layout mapping)
        console.log(`Processing sheet: "${sheet.name}" (Month: ${month}) via CSV Fallback...`);
        const csvUrl = `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}/export?format=csv&gid=${sheet.gid}`;
        const csvText = await fetchURL(csvUrl);
        const parsed = parseCSV(csvText);

        if (parsed.length <= 1) {
          continue;
        }

        // Dynamically discover header indices
        const headers = parsed[0].map((h) => h.trim().toLowerCase());
        
        const getIndex = (aliases) => {
          return headers.findIndex((h) => aliases.includes(h));
        };

        const noIdx = getIndex(["no"]);
        const nameIdx = getIndex(["nama lomba", "lomba", "name"]);
        const organizerIdx = getIndex(["penyelenggara", "organizer"]);
        const descIdx = getIndex(["deskripsi", "jenis perlombaan", "description"]);
        const timelineIdx = getIndex(["timeline lomba", "timeline pendaftaran", "timeline"]);
        const statusIdx = getIndex(["status"]);
        const qrIdx = getIndex(["qr code link", "qr code", "guidebook"]);
        const linkIdx = getIndex(["link", "link pendaftaran"]);
        const feeIdx = getIndex(["biaya pendaftaran"]);
        const memberIdx = getIndex(["jumlah anggota"]);

        for (let i = 1; i < parsed.length; i++) {
          const row = parsed[i];
          if (row.length < 2) {
            continue;
          }

          const name = nameIdx !== -1 ? row[nameIdx]?.trim() || "" : "";
          const organizer = organizerIdx !== -1 ? row[organizerIdx]?.trim() || "" : "";

          // Skip empty spacer rows
          if (!name && !organizer) {
            continue;
          }

          let description = descIdx !== -1 ? row[descIdx]?.trim() || "" : "";
          if (feeIdx !== -1 && row[feeIdx]?.trim()) {
            description += `\n\n💰 Biaya Pendaftaran: ${row[feeIdx].trim()}`;
          }
          if (memberIdx !== -1 && row[memberIdx]?.trim()) {
            description += `\n👥 Jumlah Anggota: ${row[memberIdx].trim()}`;
          }

          allCompetitions.push({
            no: noIdx !== -1 ? Number.parseInt(row[noIdx]?.trim() || i.toString(), 10) : i,
            name: name,
            organizer: organizer,
            description: description,
            timeline: timelineIdx !== -1 ? row[timelineIdx]?.trim() || "" : "",
            status: statusIdx !== -1 ? row[statusIdx]?.trim() || "Open" : "Open",
            qr_code_link: qrIdx !== -1 ? row[qrIdx]?.trim() || null : null,
            link: linkIdx !== -1 ? row[linkIdx]?.trim() || "" : "",
            month: month, // Associated month from sheet tab name
          });
        }
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
