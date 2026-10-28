// Copies the dependency-free script to dist/ with the package version stamped in.
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const version = readFileSync(join(root, 'bootstrap.php'), 'utf8').match(/register\(\s*'([^']+)'/)[1];
const source = readFileSync(join(root, 'resources', 'js', 'boat-ui.js'), 'utf8').replace(/version: '[^']*'/, `version: '${version}'`);
writeFileSync(join(root, 'dist', 'boat-ui.js'), source);
console.log(`dist/boat-ui.js written (v${version})`);
