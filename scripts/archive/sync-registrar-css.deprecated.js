const fs = require('fs');
const path = require('path');

const root = process.cwd();
const mainPath = path.join(root, 'public', 'css', 'style.css');
const registrarPath = path.join(root, 'public', 'css', 'styles.css');
const marker = '/* ===== Merged from css/styles.css on 2026-03-21 ===== */';

if (!fs.existsSync(mainPath)) {
  throw new Error('Missing file: public/css/style.css');
}
if (!fs.existsSync(registrarPath)) {
  throw new Error('Missing file: public/css/styles.css');
}

const main = fs.readFileSync(mainPath, 'utf8');
const registrar = fs.readFileSync(registrarPath, 'utf8');

if (!registrar.includes(marker)) {
  throw new Error('styles.css is missing the merge marker block header.');
}

const markerIndex = main.indexOf(marker);
const normalizedRegistrar = registrar.replace(/^\uFEFF/, '').trimEnd();
let updated;

if (markerIndex >= 0) {
  const prefix = main.slice(0, markerIndex).trimEnd();
  updated = `${prefix}\n\n${normalizedRegistrar}\n`;
} else {
  updated = `${main.trimEnd()}\n\n${normalizedRegistrar}\n`;
}

fs.writeFileSync(mainPath, updated, 'utf8');
console.log('Synced registrar CSS block from public/css/styles.css into public/css/style.css');
