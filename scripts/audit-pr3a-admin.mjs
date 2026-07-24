import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const files = [
  'app/Http/Controllers/Admin/DashboardController.php',
  'app/Services/Admin/AdminDashboardService.php',
  'app/Services/Admin/AdminNavigationService.php',
  'app/Services/Admin/AdminPermissionService.php',
  'app/Services/Admin/AdminPresentationService.php',
  'app/Http/Middleware/HandleInertiaRequests.php',
  'resources/js/Layouts/AuthenticatedLayout.vue',
  'resources/js/Pages/Dashboard.vue',
  'resources/js/Components/Admin/AdminIcon.vue',
  'resources/js/Components/Admin/AdminShell.vue',
  'resources/js/Components/Admin/AdminSidebar.vue',
  'resources/js/Components/Admin/AdminTopbar.vue',
  'resources/js/Components/Admin/AdminMobileNavigation.vue',
  'resources/js/Components/Admin/AdminPageHeader.vue',
  'resources/js/Components/Admin/AdminStatCard.vue',
  'resources/js/Components/Admin/AdminDataCard.vue',
  'resources/js/Components/Admin/AdminEmptyState.vue',
  'resources/js/Components/Admin/AdminStatusBadge.vue',
  'resources/js/Components/Admin/AdminTable.vue',
  'resources/js/Components/Admin/AdminBreadcrumbs.vue',
  'resources/js/Components/Admin/AdminPagination.vue',
  'resources/js/Components/Admin/AdminFilterBar.vue',
  'resources/js/Components/Admin/AdminSkeleton.vue',
  'resources/js/Components/Admin/AdminAlert.vue',
  'resources/js/Components/Admin/AdminConfirmDialog.vue',
];
const source = files.map((file) => fs.readFileSync(path.join(root, file), 'utf8')).join('\n');
const controller = fs.readFileSync(path.join(root, 'app/Http/Controllers/Admin/DashboardController.php'), 'utf8');
const navigation = fs.readFileSync(path.join(root, 'app/Services/Admin/AdminNavigationService.php'), 'utf8');
const sidebar = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminSidebar.vue'), 'utf8');
const mobile = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminMobileNavigation.vue'), 'utf8');
const layout = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminShell.vue'), 'utf8');

const counters = {
  HARDCODED_ADMIN_BRAND_HITS: (source.match(/PHU DUC EV/g) || []).length,
  MYSQL_DATE_FORMAT_HITS: (source.match(/DATE_FORMAT\s*\(/gi) || []).length,
  RAW_ADMIN_MODEL_PROP_HITS: (controller.match(/Inertia::render|->(?:get|paginate)\(|(?:Order|Product|Review)::/g) || []).length,
  CLIENT_MONEY_FORMATTER_HITS: (source.match(/Intl\.NumberFormat|toLocaleString|format(?:Price|Money)/g) || []).length,
  CLIENT_DATE_FORMATTER_HITS: (source.match(/new Date|toLocaleDateString|toLocaleTimeString/g) || []).length,
  DIRECT_STORAGE_PATH_HITS: (source.match(/\/storage\//g) || []).length,
  MISSING_MEDIA_NAV_HITS: navigation.includes("'key' => 'media'") ? 0 : 1,
  MISSING_ARIA_CURRENT_HITS: (sidebar.includes('aria-current') && mobile.includes('aria-current')) ? 0 : 1,
  MISSING_MAIN_LANDMARK_HITS: layout.includes('<main') ? 0 : 1,
  MISSING_MOBILE_DRAWER_HITS: (mobile.includes('role="dialog"') && mobile.includes('aria-modal="true"') && mobile.includes("event.key === 'Escape'")) ? 0 : 1,
};

for (const [key, value] of Object.entries(counters)) console.log(`${key}=${value}`);
process.exitCode = Object.values(counters).every((value) => value === 0) ? 0 : 1;
