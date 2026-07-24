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
  'resources/js/Composables/useModalFocus.js',
  'resources/js/Composables/useAdminBrand.js',
  'resources/css/admin-tokens.css',
];
const source = files.map((file) => fs.readFileSync(path.join(root, file), 'utf8')).join('\n');
const controller = fs.readFileSync(path.join(root, 'app/Http/Controllers/Admin/DashboardController.php'), 'utf8');
const navigation = fs.readFileSync(path.join(root, 'app/Services/Admin/AdminNavigationService.php'), 'utf8');
const sidebar = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminSidebar.vue'), 'utf8');
const mobile = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminMobileNavigation.vue'), 'utf8');
const layout = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminShell.vue'), 'utf8');
const authenticatedLayout = fs.readFileSync(path.join(root, 'resources/js/Layouts/AuthenticatedLayout.vue'), 'utf8');
const modalFocus = fs.readFileSync(path.join(root, 'resources/js/Composables/useModalFocus.js'), 'utf8');
const confirm = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminConfirmDialog.vue'), 'utf8');
const pagination = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminPagination.vue'), 'utf8');
const alert = fs.readFileSync(path.join(root, 'resources/js/Components/Admin/AdminAlert.vue'), 'utf8');

const counters = {
  HARDCODED_ADMIN_BRAND_HITS: (source.match(/PHU DUC EV/g) || []).length,
  MYSQL_DATE_FORMAT_HITS: (source.match(/DATE_FORMAT\s*\(/gi) || []).length,
  RAW_ADMIN_MODEL_PROP_HITS: (controller.match(/Inertia::render|->(?:get|paginate)\(|(?:Order|Product|Review)::/g) || []).length,
  CLIENT_MONEY_FORMATTER_HITS: (source.match(/Intl\.NumberFormat|toLocaleString|format(?:Price|Money)/g) || []).length,
  CLIENT_DATE_FORMATTER_HITS: (source.match(/new Date|toLocaleDateString|toLocaleTimeString/g) || []).length,
  DIRECT_STORAGE_PATH_HITS: (source.match(/\/storage\//g) || []).length,
  LEGACY_ADMIN_CARBON_HITS: (source.match(/carbon-/g) || []).length,
  DEPRECATED_ADMIN_VOLT_HITS: (source.match(/volt-/g) || []).length,
  ADMIN_VHTML_HITS: (source.match(/v-html/g) || []).length,
  ADMIN_HASH_LINK_HITS: (source.match(/href="#"/g) || []).length,
  PAGINATION_DISABLED_LINK_HITS: (pagination.match(/:href="[^"]*\|\|/g) || []).length,
  UNWIRED_ADMIN_BREADCRUMB_HITS: (layout.includes('import AdminBreadcrumbs') && layout.includes(':items="breadcrumbs"')) ? 0 : 1,
  DEAD_BREADCRUMB_SLOT_HITS: (authenticatedLayout.includes('#breadcrumb') && layout.includes('<slot name="breadcrumb">')) ? 0 : 1,
  CONFIRM_DIALOG_FOCUS_MANAGEMENT_MISSING: (confirm.includes('useModalFocus') && modalFocus.includes('document.activeElement')) ? 0 : 1,
  CONFIRM_DIALOG_ESCAPE_MISSING: modalFocus.includes("event.key === 'Escape'") ? 0 : 1,
  CONFIRM_DIALOG_TAB_TRAP_MISSING: modalFocus.includes("event.key !== 'Tab'") ? 0 : 1,
  MOBILE_DRAWER_TAB_TRAP_MISSING: mobile.includes('useModalFocus') ? 0 : 1,
  MODAL_BODY_SCROLL_LOCK_MISSING: modalFocus.includes('document.body.style.overflow') ? 0 : 1,
  ADMIN_ERROR_ALERT_ROLE_MISSING: alert.includes("props.tone === 'error' ? 'alert'") ? 0 : 1,
  PAGINATION_ARIA_CURRENT_MISSING: pagination.includes('aria-current') ? 0 : 1,
  MISSING_MEDIA_NAV_HITS: navigation.includes("'key' => 'media'") ? 0 : 1,
  MISSING_ARIA_CURRENT_HITS: (sidebar.includes('aria-current') && mobile.includes('aria-current')) ? 0 : 1,
  MISSING_MAIN_LANDMARK_HITS: layout.includes('<main') ? 0 : 1,
  MISSING_MOBILE_DRAWER_HITS: (mobile.includes('role="dialog"') && mobile.includes('aria-modal="true"') && mobile.includes('useModalFocus') && modalFocus.includes("event.key === 'Escape'")) ? 0 : 1,
};

for (const [key, value] of Object.entries(counters)) console.log(`${key}=${value}`);
process.exitCode = Object.values(counters).every((value) => value === 0) ? 0 : 1;
