import fs from 'node:fs';
import path from 'node:path';

const roots = [
  'docs/refactor/evidence/pr2b',
  'docs/refactor/pr2b',
];

const corruptPatterns = [
  'X?a b? l?c',
  'Ch?n h?nh',
  'Ph?n trang',
  'Tr??c',
  '??nh gi? ?? duy?t',
  'S? m?nh',
  'T?m nh?n',
  'G?i ?i?n',
  '?i?u h??ng ph?n c?p',
];

const mandatoryLabels = [
  'Xóa bộ lọc',
  'Chọn hình',
  'Phân trang',
  'Trước',
  'Đánh giá đã duyệt',
  'Sứ mệnh',
  'Tầm nhìn',
  'Gọi điện',
  'Điều hướng phân cấp',
];

const files = [];

const collect = (target) => {
  if (!fs.existsSync(target)) return;

  const stat = fs.statSync(target);
  if (stat.isDirectory()) {
    for (const entry of fs.readdirSync(target)) collect(path.join(target, entry));
    return;
  }

  if (/\.(md|txt|json)$/.test(target)) files.push(target);
};

roots.forEach(collect);

const failures = [];
let mojibakeHits = 0;
const joined = files.map((file) => fs.readFileSync(file, 'utf8')).join('\n');

for (const file of files) {
  const bytes = fs.readFileSync(file);
  if (bytes[0] === 0xEF && bytes[1] === 0xBB && bytes[2] === 0xBF) {
    failures.push(`${file}: UTF-8 BOM is not allowed`);
  }

  const source = bytes.toString('utf8');
  for (const pattern of corruptPatterns) {
    if (source.includes(pattern)) {
      mojibakeHits += 1;
      failures.push(`${file}: EVIDENCE_MOJIBAKE contains ${pattern}`);
    }
  }
}

for (const label of mandatoryLabels) {
  if (!joined.includes(label)) failures.push(`EVIDENCE_MANDATORY_LABEL missing ${label}`);
}

if (failures.length) {
  console.error('PR2B evidence audit failed:');
  console.error(`EVIDENCE_MOJIBAKE_HITS=${mojibakeHits}`);
  console.error('EVIDENCE_MANDATORY_LABELS=FAIL');
  for (const failure of failures) console.error(`- ${failure}`);
  process.exit(1);
}

console.log(`EVIDENCE_MOJIBAKE_HITS=${mojibakeHits}`);
console.log('EVIDENCE_MANDATORY_LABELS=PASS');
console.log(`PR2B evidence audit passed (${files.length} files scanned).`);
