import fs from "node:fs/promises";
import path from "node:path";
import sharp from "sharp";

const root = process.cwd();
const publicImagesDir = path.join(root, "public/images");
const sourcePattern = /\.(?:png|jpe?g)$/i;
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

const entries = await fs.readdir(publicImagesDir, { withFileTypes: true });

await Promise.all(
  entries
    .filter((entry) => entry.isFile() && sourcePattern.test(entry.name))
    .map(async (entry) => {
      const inputPath = path.join(publicImagesDir, entry.name);
      const parsed = path.parse(entry.name);
      const pipeline = sharp(inputPath, { animated: false }).rotate().resize({
        width: maxDimension,
        height: maxDimension,
        fit: "inside",
        withoutEnlargement: true,
      });

      await Promise.all([
        pipeline
          .clone()
          .webp({ quality: 82, effort: 6 })
          .toFile(path.join(publicImagesDir, `${parsed.name}.webp`)),
        pipeline
          .clone()
          .avif({ quality: 58, effort: 7 })
          .toFile(path.join(publicImagesDir, `${parsed.name}.avif`)),
      ]);
    }),
);
