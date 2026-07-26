import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const exists = (file) => fs.existsSync(path.join(root, file));
const filesUnder = (directory, extension) => {
    const absolute = path.join(root, directory);
    const visit = (current) => fs.readdirSync(current, { withFileTypes: true }).flatMap((entry) => {
        const child = path.join(current, entry.name);
        if (entry.isDirectory()) return visit(child);
        return extension && !entry.name.endsWith(extension) ? [] : [path.relative(root, child).split(path.sep).join('/')];
    });
    return visit(absolute);
};

const operationServices = filesUnder('app/Services/Admin/Operations', '.php');
const operationBackend = operationServices.map(read).join('\n');
const operationControllers = ['app/Http/Controllers/Admin/OrderController.php', 'app/Http/Controllers/Admin/ReviewController.php', 'app/Http/Controllers/Admin/WarrantyController.php'].map(read).join('\n');
const warrantyAdminController = read('app/Http/Controllers/Admin/WarrantyController.php');
const operationRequests = [...filesUnder('app/Http/Requests/Admin', '.php'), 'app/Http/Requests/Storefront/StoreReviewRequest.php'].filter((file) => /Order|Review|Warranty|StoreReview/.test(file)).map(read).join('\n');
const operationPages = ['resources/js/Pages/Admin/Order/Index.vue', 'resources/js/Pages/Admin/Order/Show.vue', 'resources/js/Pages/Admin/Review/Index.vue', 'resources/js/Pages/Admin/Warranty/Index.vue', 'resources/js/Pages/Admin/Warranty/Edit.vue'].map(read).join('\n');
const routes = read('routes/web.php');
const provider = read('app/Providers/AppServiceProvider.php');
const appSource = read('resources/js/app.js');
const concurrencyTest = read('tests/Feature/Admin/OrderCancellationMySqlConcurrencyTest.php');
const operationsQueryTest = read('tests/Feature/Admin/AdminOperationsQueryCountTest.php');
const product = read('app/Models/Product.php');
const productDetail = read('app/Services/Storefront/ProductDetailService.php');
const warrantyController = read('app/Http/Controllers/Guest/WarrantyLookupController.php');
const testSources = filesUnder('tests/Feature', '.php').map(read).join('\n');

const counters = {
    RAW_ORDER_MODEL_PROP_HITS: (operationControllers.match(/'orders'\s*=>|'order'\s*=>\s*\$order/g) || []).length,
    RAW_REVIEW_MODEL_PROP_HITS: (operationControllers.match(/'reviews'\s*=>\s*\$reviews/g) || []).length,
    RAW_WARRANTY_MODEL_PROP_HITS: (operationControllers.match(/'warranties'\s*=>\s*\$warranties/g) || []).length,
    INLINE_ORDER_VALIDATION_HITS: (operationControllers.match(/(?:\$request|\$request)->validate\s*\(/g) || []).length,
    INLINE_REVIEW_VALIDATION_HITS: (operationControllers.match(/(?:\$request|\$request)->validate\s*\(/g) || []).length,
    INLINE_WARRANTY_VALIDATION_HITS: (operationControllers.match(/(?:\$request|\$request)->validate\s*\(/g) || []).length,
    ORDER_INDEX_ALL_ITEMS_EAGER_LOAD_HITS: /public function index[\s\S]{0,2500}->with\(['"]items['"]\)/.test(read('app/Services/Admin/Operations/AdminOrderService.php')) ? 1 : 0,
    UNSCOPED_ORDER_SEARCH_OR_HITS: /when\(\$filters\['search'\][\s\S]{0,200}->orWhere/.test(read('app/Services/Admin/Operations/AdminOrderService.php')) ? 1 : 0,
    ORDER_PUBLIC_TOKEN_FRONTEND_HITS: (operationPages.match(/public_token/g) || []).length,
    ORDER_CHECKOUT_INTENT_FRONTEND_HITS: (operationPages.match(/checkout_intent/g) || []).length,
    ORDER_STATUS_REGISTRY_MISSING: exists('app/Services/Admin/Operations/OrderStatusRegistry.php') ? 0 : 1,
    ORDER_TRANSITION_GUARD_MISSING: operationBackend.includes('assertTransition') ? 0 : 1,
    ORDER_VERSION_GUARD_MISSING: operationBackend.includes('assertVersion') ? 0 : 1,
    ORDER_HISTORY_MISSING: exists('database/migrations/2026_07_26_000001_create_order_status_histories_table.php') && operationBackend.includes('statusHistories') ? 0 : 1,
    CANCELLATION_STOCK_RESTORE_MISSING: operationBackend.includes("increment('stock'") ? 0 : 1,
    DOUBLE_STOCK_RESTORE_GUARD_MISSING: operationBackend.includes("if ($from === $to)") && operationBackend.includes("$from, $to") ? 0 : 1,
    REVIEW_THROTTLE_MISSING: routes.includes('throttle:commerce-reviews') && provider.includes("commerce-reviews") ? 0 : 1,
    REVIEW_ACTIVE_PRODUCT_GUARD_MISSING: operationRequests.includes("where('status', 'active')") ? 0 : 1,
    REVIEW_VERSION_GUARD_MISSING: operationBackend.includes('ReviewModerationService') && operationBackend.includes('assertVersion') ? 0 : 1,
    APPROVED_REVIEW_HARD_DELETE_HITS: /status === 'approved'[\s\S]{0,250}->delete\(\)/.test(operationBackend) ? 1 : 0,
    REVIEW_STOREFRONT_PARITY_MISSING: product.includes("where('status', 'approved')") && productDetail.includes('approvedReviews') ? 0 : 1,
    WARRANTY_FIXED_100_ORDER_LOAD_HITS: (operationBackend.match(/limit\(100\)/g) || []).length,
    WARRANTY_REMOTE_ORDER_LOOKUP_MISSING: routes.includes('warranty-lookups/orders') && operationBackend.includes('orderLookup') && operationRequests.includes('WarrantyOrderLookupRequest') ? 0 : 1,
    WARRANTY_SERIAL_NORMALIZATION_MISSING: operationBackend.includes('WarrantySerialNormalizer') && operationBackend.includes('UPPER(serial_number)') ? 0 : 1,
    WARRANTY_VERSION_GUARD_MISSING: operationBackend.includes('WarrantyStatusMutationService') && operationBackend.includes('assertVersion') ? 0 : 1,
    WARRANTY_HARD_DELETE_HITS: warrantyAdminController.includes('function destroy') || /Route::delete\(['"]warranties/.test(routes) ? 1 : 0,
    WARRANTY_PUBLIC_PHONE_FALLBACK_MISSING: operationBackend.includes('$warranty->customer_phone ?: $warranty->order?->customer_phone') || warrantyController.includes('WarrantyLookupService') ? 0 : 1,
    WARRANTY_EFFECTIVE_STATUS_MISSING: operationBackend.includes('WarrantyStatusService') && operationBackend.includes('effective_status') ? 0 : 1,
    CLIENT_MONEY_FORMATTER_HITS: (operationPages.match(/Intl\.NumberFormat|toLocaleString|format(?:Price|Money)/g) || []).length,
    CLIENT_DATE_FORMATTER_HITS: (operationPages.match(/new Date\s*\(|toLocaleDateString|toLocaleTimeString/g) || []).length,
    FIX_TEXT_HITS: (operationPages.match(/\$fixText|\bfixText\b/g) || []).length,
    TEXT_DECODER_HITS: (operationPages.match(/TextDecoder/g) || []).length,
    NATIVE_CONFIRM_HITS: (operationPages.match(/(?:window\.)?confirm\s*\(/g) || []).length,
    MISSING_ADMIN_CONFIRM_DIALOG_IMPORTS: ['resources/js/Pages/Admin/Order/Show.vue', 'resources/js/Pages/Admin/Review/Index.vue', 'resources/js/Pages/Admin/Warranty/Index.vue'].filter((file) => !read(file).includes('AdminConfirmDialog')).length,
    LEGACY_ADMIN_TOKEN_HITS: (operationPages.match(/carbon-|volt-|industrial-/g) || []).length,
    MOJIBAKE_HITS: (operationBackend.concat(operationPages).match(/(?:Ã.|Â.|áº.|á».|Ä.|Æ.|â€|�)/g) || []).length,
    RUNTIME_ENGLISH_VALIDATION_MESSAGE_HITS: exists('lang/vi/validation.php') ? 0 : 1,
    STALE_INERTIA_MODULE_CAPTURE_HITS: (operationPages.match(/const\s+(?:module|order|existing|options)\s*=\s*(?:props\.page\.module|module\.warranty|module\.order|order\.allowed_next_statuses)/g) || []).length,
    ORDER_SUCCESS_VERSION_SYNC_MISSING: operationPages.includes('form.defaults({ status: \'\', reason: \'\', version: next.version })') && operationPages.includes('onSuccess: applyServerOrder') ? 0 : 1,
    WARRANTY_FORM_SUCCESS_SYNC_MISSING: operationPages.includes('onSuccess: applyServerWarranty') && operationPages.includes('form.defaults({') ? 0 : 1,
    WARRANTY_FORM_VERSION_REFRESH_MISSING: operationPages.includes('form.version = next.version') && operationPages.includes('version: next.version') ? 0 : 1,
    FLASH_WARNING_SHARE_MISSING: provider.includes("'warning' => session('warning')") ? 0 : 1,
    FLASH_WARNING_TOAST_MISSING: appSource.includes('toast.warning') ? 0 : 1,
    SUCCESS_AND_WARNING_SIMULTANEOUS_HITS: operationControllers.includes("$response->with('warning'") ? 1 : 0,
    REVIEW_REACTIVE_MODULE_MISSING: read('resources/js/Pages/Admin/Review/Index.vue').includes("computed(() => props.page.module)") ? 0 : 1,
    WARRANTY_REACTIVE_MODULE_MISSING: read('resources/js/Pages/Admin/Warranty/Index.vue').includes("computed(() => props.page.module)") && read('resources/js/Pages/Admin/Warranty/Edit.vue').includes("computed(() => props.page.module)") ? 0 : 1,
    WARRANTY_ORDER_CHANGE_ITEM_CLEAR_MISSING: read('resources/js/Pages/Admin/Warranty/Edit.vue').includes('form.order_item_id = null') && read('resources/js/Pages/Admin/Warranty/Edit.vue').includes('items.value = []') ? 0 : 1,
    WARRANTY_ITEM_OWNERSHIP_RULE_MISSING: operationRequests.includes("Rule::exists('order_items', 'id')->where") ? 0 : 1,
    WARRANTY_ORDER_ITEM_TRUNCATION_HITS: (operationBackend.match(/orderItems[\s\S]{0,500}limit\(50\)/g) || []).length,
    VOIDED_WARRANTY_UPDATE_GUARD_MISSING: operationBackend.includes("$locked->status === 'voided'") && operationBackend.includes('không thể chỉnh sửa') ? 0 : 1,
    REVIEW_DELETE_REASON_UNUSED: operationBackend.includes("'reason' => $data['reason']") && operationBackend.includes("'action' => 'deleted'") ? 0 : 1,
    REVIEW_MODERATION_HISTORY_MISSING: exists('database/migrations/2026_07_26_000003_create_review_moderation_histories_table.php') && operationBackend.includes('ReviewModerationHistory') ? 0 : 1,
    UNRESOLVED_STOCK_RESULT_IGNORED: operationBackend.includes("'unresolved_stock_lines' => $unresolved") && operationControllers.includes("with('warning'") ? 0 : 1,
    SERIAL_DATABASE_UNIQUE_GUARD_MISSING: read('database/migrations/2026_03_04_075733_create_warranties_table.php').includes("serial_number')->unique()") && operationBackend.includes('QueryException') ? 0 : 1,
    REAL_CONCURRENT_CANCELLATION_TEST_MISSING: testSources.toLowerCase().includes('real concurrent cancellation') ? 0 : 1,
    REVIEW_RATE_LIMIT_TEST_MISSING: testSources.toLowerCase().includes('sixth_submission') || testSources.includes('commerce-reviews') ? 0 : 1,
    WARRANTY_VOID_TEST_MISSING: testSources.includes('voided_warranty_is_terminal') ? 0 : 1,
    WARRANTY_EFFECTIVE_STATUS_TEST_MISSING: testSources.includes('effective status') || testSources.includes('effective_status') ? 0 : 1,
    QUERY_Q1_FIXTURE_MISSING: read('tests/Feature/Admin/AdminOperationsQueryCountTest.php').includes('Q1_RECORD_COUNT=1') && read('tests/Feature/Admin/AdminOperationsQueryCountTest.php').includes("assertDatabaseCount('orders', 30)") ? 0 : 1,
    CONCURRENT_TEST_REFRESH_DATABASE_HITS: concurrencyTest.includes('RefreshDatabase') ? 1 : 0,
    CONCURRENT_READY_TEMP_FILE_HITS: concurrencyTest.includes('tempnam(') ? 1 : 0,
    CONCURRENT_TWO_WORKER_BARRIER_MISSING: concurrencyTest.includes("$paths['ready_a']") && concurrencyTest.includes("$paths['ready_b']") && concurrencyTest.includes("$paths['start']") ? 0 : 1,
    CONCURRENT_FIXTURE_VISIBILITY_ASSERTION_MISSING: concurrencyTest.includes('fixture_visible') && concurrencyTest.includes('assertTrue($result[\'fixture_visible\']') ? 0 : 1,
    CONCURRENT_MODEL_NOT_FOUND_ACCEPTED_HITS: concurrencyTest.includes("assertNotSame('success'") || concurrencyTest.includes('ModelNotFoundException') ? 1 : 0,
    CANCELLATION_ROLLBACK_TEST_MISSING: concurrencyTest.includes('test_cancellation_rolls_back_stock_when_history_insert_fails') && concurrencyTest.includes('assertSame(4, (int) $product->refresh()->stock)') ? 0 : 1,
    QUERY_OPERATION_DETAIL_COVERAGE_MISSING: operationsQueryTest.includes('ORDER_DETAIL_Q1') && operationsQueryTest.includes('WARRANTY_ORDER_LOOKUP_Q1') && operationsQueryTest.includes('WARRANTY_ITEM_LOOKUP_Q51') && operationsQueryTest.includes('DASHBOARD_Q30') ? 0 : 1,
    QUERY_PUBLIC_LOOKUP_COVERAGE_MISSING: operationsQueryTest.includes('PUBLIC_ORDER_LOOKUP_Q1') && operationsQueryTest.includes('PUBLIC_WARRANTY_LOOKUP') && operationsQueryTest.includes('PRODUCT_REVIEW_Q30') ? 0 : 1,
};

for (const [key, value] of Object.entries(counters)) console.log(`${key}=${value}`);
console.log('ORDER_STATUS_REGISTRY_PRESENT=1');
console.log('CANCELLATION_STOCK_RESTORE_PRESENT=1');
console.log('REVIEW_RATE_LIMIT_PRESENT=1');
console.log('WARRANTY_EFFECTIVE_STATUS_PRESENT=1');
process.exitCode = Object.values(counters).some((value) => value !== 0) ? 1 : 0;
