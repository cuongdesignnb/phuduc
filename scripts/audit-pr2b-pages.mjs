import fs from 'node:fs';
import path from 'node:path';

const roots = [
  'app/Http/Controllers/Guest/ProductController.php',
  'app/Http/Requests/Storefront',
  'app/Services/Storefront',
  'resources/js/Pages/Guest/Product',
  'resources/js/Pages/Guest/News',
  'resources/js/Pages/Guest/About.vue',
  'resources/js/Components/Storefront',
  'resources/js/Components/ProductViewer360.vue',
];

const checks = {
  MOJIBAKE_REPLACEMENT_HITS: [
    '�',
    'Ã',
    'Â',
  ],
  UNACCENTED_VIETNAMESE_LABEL_HITS: [
    'Trang chu',
    'San pham',
    'Giai phap xe dien cong nghiep',
    'Tin tuc',
    'Gioi thieu',
    'Lien he',
    'Tai trong',
    'Quang duong',
    'Tim san pham',
    'Gia tu',
    'Gia den',
    'Sap xep',
    'Ap dung',
    'Xoa loc',
    'Them vao gio hang',
    'Thong so ky thuat',
    'Mo ta san pham',
    'Gui danh gia',
    'Dang them',
    'Chua co mo ta',
    'Chua co danh gia',
    'Ho ten',
    'Dien thoai',
    'Noi dung',
    'Bai viet lien quan',
    'Tim bai viet',
    'Tim kiem',
    'Danh muc tin tuc',
    'Tat ca',
    'Khong tim thay san pham phu hop',
    'Khong tim thay bai viet phu hop',
    'Khong co khung hinh 360',
    'Dung xoay',
    'Tu xoay',
    'Chon hinh',
    'Phan trang',
    'Truoc',
    'danh gia da duyet',
    'Xoa bo loc',
    'Su menh',
    'Tam nhin',
    'Goi dien',
  ],
  LEGACY_FORMATTER_HITS: [
    '$fixText',
    'TextDecoder',
    'IntersectionObserver',
    'formatPrice',
    'new Date(',
    '1900xxxx',
    'href="#"',
  ],
  DIRECT_STORAGE_PATH_HITS: [
    "'/storage/'",
    '"/storage/"',
  ],
  LEGACY_THEME_HITS: [
    'bg-brand-primary',
    'text-ink-',
    'bg-surface-bg',
    'border-surface-border',
    'glass-card',
    'neon-line',
    'glow-border',
  ],
};

const files = [];
const directStorageAllowedFiles = new Set([
  path.normalize('app/Services/Storefront/MediaUrlService.php'),
]);

const collect = (target) => {
  if (!fs.existsSync(target)) return;
  const stat = fs.statSync(target);
  if (stat.isDirectory()) {
    for (const entry of fs.readdirSync(target)) {
      collect(path.join(target, entry));
    }
    return;
  }
  if (/\.(vue|js|php)$/.test(target)) files.push(target);
};

roots.forEach(collect);

const failures = [];
const hitCounts = Object.fromEntries(Object.keys(checks).map((key) => [key, 0]));

for (const file of files) {
  const source = fs.readFileSync(file, 'utf8');
  for (const [group, patterns] of Object.entries(checks)) {
    for (const pattern of patterns) {
      if (group === 'DIRECT_STORAGE_PATH_HITS' && directStorageAllowedFiles.has(path.normalize(file))) {
        continue;
      }
      if (source.includes(pattern)) {
        hitCounts[group] += 1;
        failures.push(`${file}: ${group} contains ${pattern}`);
      }
    }
  }
  if (source.includes('v-html') && path.normalize(file) !== path.normalize('resources/js/Components/Storefront/RichContent.vue')) {
    failures.push(`${file}: v-html is only allowed in RichContent.vue`);
  }
}

const readIfExists = (file) => fs.existsSync(file) ? fs.readFileSync(file, 'utf8') : '';
const controllerSource = readIfExists('app/Http/Controllers/Guest/ProductController.php');
const resolverSource = readIfExists('app/Services/Storefront/ProductCatalogFilterResolver.php');
const requestExists = fs.existsSync('app/Http/Requests/Storefront/ProductCatalogRequest.php');
const architectureCounts = {
  PRODUCT_CONTROLLER_VALIDATOR_MAKE_HITS: controllerSource.includes('Validator::make') ? 1 : 0,
  DUPLICATE_PRODUCT_FILTER_RULESETS: requestExists ? 1 : 0,
  UNUSED_PRODUCT_CATALOG_REQUEST: requestExists ? 1 : 0,
};

if (!resolverSource.includes("'min_price' => ['nullable', 'numeric'")) {
  failures.push('app/Services/Storefront/ProductCatalogFilterResolver.php: missing product min_price validation rules');
}

for (const [group, count] of Object.entries(architectureCounts)) {
  if (count > 0) failures.push(`${group}=${count}`);
}

if (failures.length) {
  console.error('PR2B page audit failed:');
  for (const [group, count] of Object.entries(hitCounts)) console.error(`${group}=${count}`);
  for (const [group, count] of Object.entries(architectureCounts)) console.error(`${group}=${count}`);
  for (const failure of failures) console.error(`- ${failure}`);
  process.exit(1);
}

for (const [group, count] of Object.entries(hitCounts)) console.log(`${group}=${count}`);
for (const [group, count] of Object.entries(architectureCounts)) console.log(`${group}=${count}`);
console.log(`PR2B page audit passed (${files.length} files scanned).`);
