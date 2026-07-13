import fs from "node:fs";
import path from "node:path";
import https from "node:https";

const SHEET_URL =
  "https://docs.google.com/spreadsheets/d/1rHMZoGB3RgzDVwRqW0QalCahShWGMdLTOQVO62x5EK4/export?format=csv&gid=373337509";
const OUTPUT_FILE = path.resolve("storage/app/competitions.json");

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

function fetchCSV(url) {
  return new Promise((resolve, reject) => {
    https
      .get(url, (res) => {
        // Automatically follow HTTP redirects
        if (res.statusCode >= 300 && res.statusCode < 400 && res.headers.location) {
          const redirectUrl = new URL(res.headers.location, url).toString();
          resolve(fetchCSV(redirectUrl));
          return;
        }

        if (res.statusCode !== 200) {
          reject(new Error(`Failed to fetch sheet: HTTP ${res.statusCode}`));
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

async function main() {
  console.log(`[${new Date().toISOString()}] Fetching competitions from Google Sheets...`);
  try {
    const csvText = await fetchCSV(SHEET_URL);
    const parsed = parseCSV(csvText);

    if (parsed.length <= 1) {
      console.error("No data rows found in spreadsheet.");
      process.exit(1);
    }

    // Header structure: No, Nama Lomba, Penyelenggara, Deskripsi, Timeline Lomba, Status, QR Code Link, Link
    const headers = parsed[0].map((h) => h.trim());
    console.log("Sheet Headers:", headers.join(" | "));

    const competitions = [];

    for (let i = 1; i < parsed.length; i++) {
      const row = parsed[i];
      if (row.length < 2) continue;

      const name = row[1]?.trim() || "";
      const organizer = row[2]?.trim() || "";

      // Skip blank rows or empty placeholder rows
      if (!name && !organizer) {
        continue;
      }

      competitions.push({
        no: Number.parseInt(row[0]?.trim() || i.toString(), 10),
        name: name,
        organizer: organizer,
        description: row[3]?.trim() || "",
        timeline: row[4]?.trim() || "",
        status: row[5]?.trim() || "Open",
        qr_code_link: row[6]?.trim() || null,
        link: row[7]?.trim() || "",
      });
    }

    // Ensure output target folder directory exists
    fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });

    // Output formatted JSON
    fs.writeFileSync(
      OUTPUT_FILE,
      JSON.stringify(competitions, null, 4),
      "utf8",
    );
    console.log(
      `Successfully loaded and parsed ${competitions.length} competitions.`,
    );
    console.log(`Saved output to: ${OUTPUT_FILE}`);
  } catch (error) {
    console.error("Error loading or parsing Google Sheet:", error);
    process.exit(1);
  }
}

main();
