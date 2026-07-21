import { readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const targets = [
    'resources/js/Layouts/GuestPageLayout.vue',
    'resources/js/Components/Storefront',
    'resources/js/Components/Home',
    'resources/js/Pages/Guest',
];

const forbidden = [
    '#ffd400',
    '#ffc400',
    '#e5be00',
    '#f3c800',
    '#d49d00',
    '#e4a900',
    '#f1b900',
    'bg-volt-',
    'text-volt-',
    'border-volt-',
    'glass-card',
    'neon-line',
    'glow-border',
];

async function collectFiles(target) {
    const absolute = path.join(root, target);
    const metadata = await stat(absolute);
    if (metadata.isFile()) return [absolute];

    const entries = await readdir(absolute, { withFileTypes: true });
    const nested = await Promise.all(entries.map((entry) => collectFiles(path.join(target, entry.name))));

    return nested.flat().filter((file) => /\.(vue|js)$/.test(file));
}

const files = (await Promise.all(targets.map(collectFiles))).flat();
const violations = [];

for (const file of files) {
    const relative = path.relative(root, file).replaceAll('\\', '/');
    const lines = (await readFile(file, 'utf8')).split(/\r?\n/);

    lines.forEach((line, index) => {
        forbidden.forEach((pattern) => {
            if (line.toLowerCase().includes(pattern.toLowerCase())) {
                violations.push(`${relative}:${index + 1}: forbidden storefront theme pattern "${pattern}"`);
            }
        });
    });
}

if (violations.length) {
    console.error('Storefront theme audit failed:');
    violations.forEach((violation) => console.error(`- ${violation}`));
    process.exitCode = 1;
} else {
    console.log(`Storefront theme audit passed (${files.length} files, ${forbidden.length} forbidden patterns).`);
}
