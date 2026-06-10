import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";
import { optimize } from "svgo";

const root = process.cwd();
const publicImagesDir = path.join(root, "public/images");
const sourcePattern = /\.(?:png|jpe?g|svg)$/i;
const maxDimension = 1600;

const cacheDir = path.join(root, "node_modules/.cache");
const cachePath = path.join(cacheDir, "optimize-static-images.json");

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

// Load Cache
let cache = {};
try {
  const cacheData = await fs.readFile(cachePath, "utf-8");
  cache = JSON.parse(cacheData);
} catch {
  // Cache doesn't exist yet
}

async function saveCache() {
  try {
    await fs.mkdir(cacheDir, { recursive: true });
    await fs.writeFile(cachePath, JSON.stringify(cache, null, 2), "utf-8");
  } catch (error) {
    console.error("Failed to save optimization cache:", error);
  }
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

  // Get current file statistics
  let stats;
  try {
    stats = await fs.stat(file);
  } catch (error) {
    console.error(`Failed to stat ${parsed.base}:`, error);
    continue;
  }

  const isSvg = parsed.ext.toLowerCase() === ".svg";

  // Check if we can skip this file
  let canSkip = false;
  if (cache[file] && cache[file].mtimeMs === stats.mtimeMs && cache[file].size === stats.size) {
    if (isSvg) {
      canSkip = true;
    } else {
      const webpPath = path.join(parsed.dir, `${parsed.name}.webp`);
      const avifPath = path.join(parsed.dir, `${parsed.name}.avif`);
      const webpExists = await fileExists(webpPath);
      const avifExists = await fileExists(avifPath);
      if (webpExists && avifExists) {
        canSkip = true;
      }
    }
  }

  if (canSkip) {
    console.log(`Skipping already optimized: ${path.relative(root, file)}`);
    continue;
  }

  console.log(`Optimizing ${path.relative(root, file)}...`);

  try {
    if (isSvg) {
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

      // Update cache with post-optimization stats
      const newStats = await fs.stat(file);
      cache[file] = {
        mtimeMs: newStats.mtimeMs,
        size: newStats.size,
      };
      await saveCache();

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
      .webp({ quality: 75, effort: 6 })
      .toFile(path.join(parsed.dir, `${parsed.name}.webp`));

    // 2. Generate AVIF
    await pipeline
      .clone()
      .avif({ quality: 48, effort: 7 })
      .toFile(path.join(parsed.dir, `${parsed.name}.avif`));

    // 3. Compress original in-place
    if (parsed.ext.toLowerCase() === ".png") {
      await pipeline
        .clone()
        .png({ quality: 75, compressionLevel: 9, palette: true })
        .toFile(tempPath);
    } else {
      await pipeline
        .clone()
        .jpeg({ quality: 75 })
        .toFile(tempPath);
    }

    // Overwrite the original file
    await fs.rename(tempPath, file);

    // Update cache with post-optimization stats
    const newStats = await fs.stat(file);
    cache[file] = {
      mtimeMs: newStats.mtimeMs,
      size: newStats.size,
    };
    await saveCache();

    console.log(`Successfully optimized ${parsed.base}`);
  } catch (error) {
    console.error(`Failed to optimize ${parsed.base}:`, error);
  }
}
