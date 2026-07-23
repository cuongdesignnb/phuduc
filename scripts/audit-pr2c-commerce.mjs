import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const files = [
    'routes/web.php',
    'app/Http/Controllers/Guest/CartController.php',
    'app/Http/Controllers/Guest/CheckoutController.php',
    'app/Http/Controllers/Guest/OrderLookupController.php',
    'app/Http/Controllers/Guest/WarrantyLookupController.php',
    'app/Services/Storefront/CartSessionService.php',
    'app/Services/Storefront/CartResolver.php',
    'app/Services/Storefront/CartPresentationService.php',
    'app/Services/Storefront/CheckoutIntentService.php',
    'app/Services/Storefront/CheckoutService.php',
    'app/Services/Storefront/OrderPresentationService.php',
    'app/Services/Storefront/WarrantyPresentationService.php',
    'app/Http/Requests/Storefront/AddToCartRequest.php',
    'app/Http/Requests/Storefront/UpdateCartItemRequest.php',
    'app/Http/Requests/Storefront/RemoveCartItemRequest.php',
    'app/Http/Requests/Storefront/CheckoutRequest.php',
    'app/Http/Requests/Storefront/OrderLookupRequest.php',
    'app/Http/Requests/Storefront/WarrantyLookupRequest.php',
    'resources/js/Pages/Guest/Cart.vue',
    'resources/js/Pages/Guest/Checkout.vue',
    'resources/js/Pages/Guest/CheckoutSuccess.vue',
    'resources/js/Pages/Guest/OrderLookup.vue',
    'resources/js/Pages/Guest/WarrantyLookup.vue',
];

const source = Object.fromEntries(files.map((file) => [file, fs.readFileSync(path.join(root, file), 'utf8')]));
const all = Object.values(source).join('\n');
const count = (pattern, value = all) => [...value.matchAll(pattern)].length;
const controller = source['app/Http/Controllers/Guest/CheckoutController.php'];
const cartBackend = [source['app/Http/Controllers/Guest/CartController.php'], source['app/Services/Storefront/CartSessionService.php']].join('\n');
const lookupBackend = [source['app/Http/Controllers/Guest/OrderLookupController.php'], source['app/Http/Controllers/Guest/WarrantyLookupController.php']].join('\n');
const utilityVue = ['resources/js/Pages/Guest/Cart.vue', 'resources/js/Pages/Guest/Checkout.vue', 'resources/js/Pages/Guest/CheckoutSuccess.vue', 'resources/js/Pages/Guest/OrderLookup.vue', 'resources/js/Pages/Guest/WarrantyLookup.vue'].map((file) => source[file]).join('\n');

const checks = {
    RAW_ELOQUENT_PUBLIC_PROPS: count(/['"]order['"]\s*=>\s*\$order\s*[,}\n]|['"]warranty['"]\s*=>\s*\$warranty\s*[,}\n]/g),
    SESSION_PRICE_TRUST_HITS: count(/item\[['"](?:price|name|image|stock)['"]\]|session\(\)->(?:get|put)\([^\n]*(?:price|name|image|stock)/gi, cartBackend + '\n' + source['app/Services/Storefront/CheckoutService.php']),
    CLIENT_TOTAL_TRUST_HITS: count(/(?:item|order)\.(?:price|total_amount)\s*[*+]|total\s*=s*computed\(|reduce\([^)]*price/gi, utilityVue),
    CLIENT_PRICE_FORMATTER_HITS: count(/formatPrice|Intl\.NumberFormat|toLocaleString\s*\(/g, utilityVue),
    CLIENT_DATE_FORMATTER_HITS: count(/new Date\s*\(|toLocale(?:String|DateString)\s*\(/g, utilityVue),
    DIRECT_STORAGE_PATH_HITS: count(/['"`].*\/storage\//g, all),
    NUMERIC_ORDER_SUCCESS_ROUTE_HITS: count(/thanh-cong\/\{order\}/g, all),
    LOOKUP_PII_URL_HITS: count(/(?:GET|Route::get)[^\n]*(?:phone|email|address)|\?(?:[^\n]*)(?:phone|email|address)/gi, all),
    CONTROLLER_TRANSACTION_HITS: count(/DB::transaction|\btransaction\s*\(/g, controller),
    INLINE_CHECKOUT_VALIDATION_HITS: count(/\$request->validate\s*\(/g, controller),
    VHTML_HITS: count(/v-html\b/g, utilityVue),
    FORBIDDEN_LITERAL_HITS: count(/\/storage\/|\$fixText|TextDecoder|IntersectionObserver|formatPrice|new Date\s*\(|toLocaleString\s*\(|Intl\.NumberFormat|1900xxxx|href="#"|v-html\b/g, all),
    LOOKUP_SINGLE_FACTOR_HITS: count(/where\(['"](?:order_number|serial_number)['"][^\n]*\)->first/g, lookupBackend),
};

for (const [key, value] of Object.entries(checks)) console.log(`${key}=${value}`);

const failures = Object.entries(checks).filter(([key, value]) => value !== 0 && !['LOOKUP_SINGLE_FACTOR_HITS'].includes(key));
if (failures.length) {
    console.error('PR2C commerce audit failed.');
    process.exit(1);
}
console.log('PR2C commerce audit passed.');
