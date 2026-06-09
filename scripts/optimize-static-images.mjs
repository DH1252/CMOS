import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";
import { optimize } from "svgo";

const root = process.cwd();
const publicImagesDir = path.join(root, "public/images");
const sourcePattern = /\.(?:png|jpe?g|svg)$/i;
const maxDimension = 1600;

const fileExists = async (filePath) => {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
};

if (!(await fileExists(publicImagesDir))) {
  process.exit(0);
}

async function walk(dir) {
  let files = [];
  const list = await fs.readdir(dir, { withFileTypes: true });
  for (const entry of list) {
    const res = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files = files.concat(await walk(res));
    } else if (entry.isFile()) {
      files.push(res);
    }
  }
  return files;
}

const files = await walk(publicImagesDir);
const sourceFiles = files.filter((file) => sourcePattern.test(file));

console.log(`Found ${sourceFiles.length} images/vectors to optimize...`);

for (const file of sourceFiles) {
  const parsed = path.parse(file);
  
  // Skip our own temporary files or files starting with _temp_
  if (parsed.name.startsWith("_temp_")) {
    continue;
  }

  console.log(`Optimizing ${path.relative(root, file)}...`);

  try {
    if (parsed.ext.toLowerCase() === ".svg") {
      const content = await fs.readFile(file, "utf-8");
      const result = optimize(content, {
        path: file,
        multipass: true,
        plugins: [
          {
            name: "preset-default",
            params: {
              overrides: {
                cleanupIds: false,
              },
            },
          },
        ],
      });
      await fs.writeFile(file, result.data, "utf-8");
      console.log(`Successfully optimized SVG: ${parsed.base}`);
      continue;
    }

    const tempPath = path.join(parsed.dir, `_temp_${parsed.base}`);

    const pipeline = sharp(file, { animated: false }).rotate().resize({
      width: maxDimension,
      height: maxDimension,
      fit: "inside",
      withoutEnlargement: true,
    });

    // 1. Generate WebP
    await pipeline
      .clone()
      .webp({ quality: 82, effort: 6 })
      .toFile(path.join(parsed.dir, `${parsed.name}.webp`));

    // 2. Generate AVIF
    await pipeline
      .clone()
      .avif({ quality: 58, effort: 7 })
      .toFile(path.join(parsed.dir, `${parsed.name}.avif`));

    // 3. Compress original in-place
    if (parsed.ext.toLowerCase() === ".png") {
      await pipeline
        .clone()
        .png({ quality: 80, compressionLevel: 9, palette: true })
        .toFile(tempPath);
    } else {
      await pipeline
        .clone()
        .jpeg({ quality: 80 })
        .toFile(tempPath);
    }

    // Overwrite the original file
    await fs.rename(tempPath, file);
    console.log(`Successfully optimized ${parsed.base}`);
  } catch (error) {
    console.error(`Failed to optimize ${parsed.base}:`, error);
  }
}
