import fs from 'node:fs';
import path from 'node:path';

const roots = [
  'resources/js/Pages/Guest/Product',
  'resources/js/Pages/Guest/News',
  'resources/js/Pages/Guest/About.vue',
  'resources/js/Components/Storefront',
  'resources/js/Components/ProductViewer360.vue',
];

const banned = [
  "'/storage/'",
  '"/storage/"',
  '$fixText',
  'TextDecoder',
  'IntersectionObserver',
  'formatPrice',
  'new Date(',
  '1900xxxx',
  'href="#"',
  'bg-brand-primary',
  'text-ink-',
  'bg-surface-bg',
  'border-surface-border',
  'glass-card',
  'neon-line',
  'glow-border',
];

const files = [];

const collect = (target) => {
  if (!fs.existsSync(target)) return;
  const stat = fs.statSync(target);
  if (stat.isDirectory()) {
    for (const entry of fs.readdirSync(target)) {
      collect(path.join(target, entry));
    }
    return;
  }
  if (/\.(vue|js)$/.test(target)) files.push(target);
};

roots.forEach(collect);

const failures = [];

for (const file of files) {
  const source = fs.readFileSync(file, 'utf8');
  for (const pattern of banned) {
    if (source.includes(pattern)) {
      failures.push(`${file}: contains ${pattern}`);
    }
  }
  if (source.includes('v-html') && path.normalize(file) !== path.normalize('resources/js/Components/Storefront/RichContent.vue')) {
    failures.push(`${file}: v-html is only allowed in RichContent.vue`);
  }
}

if (failures.length) {
  console.error('PR2B page audit failed:');
  for (const failure of failures) console.error(`- ${failure}`);
  process.exit(1);
}

console.log(`PR2B page audit passed (${files.length} files scanned).`);
