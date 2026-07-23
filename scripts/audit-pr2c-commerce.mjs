import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const backendFiles = [
    'routes/web.php',
    'app/Providers/AppServiceProvider.php',
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
    'app/Services/Storefront/PhoneNormalizer.php',
    'app/Http/Requests/Storefront/AddToCartRequest.php',
    'app/Http/Requests/Storefront/UpdateCartItemRequest.php',
    'app/Http/Requests/Storefront/RemoveCartItemRequest.php',
    'app/Http/Requests/Storefront/CheckoutRequest.php',
    'app/Http/Requests/Storefront/OrderLookupRequest.php',
    'app/Http/Requests/Storefront/WarrantyLookupRequest.php',
];
const pageFiles = [
    'resources/js/Pages/Guest/Cart.vue',
    'resources/js/Pages/Guest/Checkout.vue',
    'resources/js/Pages/Guest/CheckoutSuccess.vue',
    'resources/js/Pages/Guest/OrderLookup.vue',
    'resources/js/Pages/Guest/WarrantyLookup.vue',
];
const source = Object.fromEntries([...backendFiles, ...pageFiles, 'resources/js/Components/SeoHead.vue'].map((file) => [file, fs.readFileSync(path.join(root, file), 'utf8')]));
const all = Object.values(source).join('\n');
const pageSource = pageFiles.map((file) => source[file]).join('\n');
const count = (pattern, value = all) => [...value.matchAll(pattern)].length;
const requestSource = ['CheckoutRequest.php', 'OrderLookupRequest.php', 'WarrantyLookupRequest.php']
    .map((name) => backendFiles.find((file) => file.endsWith(`/Requests/Storefront/${name}`)))
    .map((file) => source[file]);
const lookupSource = ['app/Http/Controllers/Guest/OrderLookupController.php', 'app/Http/Controllers/Guest/WarrantyLookupController.php'];

const lookupSingleFactorHits = lookupSource.reduce((hits, file) => {
    const method = source[file].match(/public function lookup[\s\S]*?\n    \}/u)?.[0] || '';

    return hits + (method.includes("where('order_number'") || method.includes("where('serial_number'") ? (method.includes('customer_phone') ? 0 : 1) : 0);
}, 0);

const checks = {
    RAW_ELOQUENT_PUBLIC_PROPS: count(/['"](?:order|warranty)['"]\s*=>\s*\$(?:order|warranty)\s*[,}\n]/g),
    SESSION_PRICE_TRUST_HITS: count(/item\[['"](?:price|name|image|stock)['"]\]|session\(\)->(?:get|put)\([^\n]*(?:price|name|image|stock)/gi),
    CLIENT_TOTAL_TRUST_HITS: count(/(?:total|subtotal)\s*=\s*computed\s*\(|reduce\([^)]*(?:price|subtotal|total)/gi, pageSource),
    CLIENT_PRICE_FORMATTER_HITS: count(/formatPrice|Intl\.NumberFormat|toLocaleString\s*\(/g, pageSource),
    CLIENT_DATE_FORMATTER_HITS: count(/new Date\s*\(|toLocale(?:String|DateString)\s*\(/g, pageSource),
    DIRECT_STORAGE_PATH_HITS: count(/['"`].*\/storage\//g),
    NUMERIC_ORDER_SUCCESS_ROUTE_HITS: count(/thanh-cong\/\{order\}/g),
    LOOKUP_PII_URL_HITS: count(/(?:GET|Route::get)[^\n]*(?:phone|email|address)|\?(?:[^\n]*)(?:phone|email|address)/gi, source['routes/web.php']),
    CONTROLLER_TRANSACTION_HITS: count(/DB::transaction|\btransaction\s*\(/g, source['app/Http/Controllers/Guest/CheckoutController.php']),
    INLINE_CHECKOUT_VALIDATION_HITS: count(/\$request->validate\s*\(/g, source['app/Http/Controllers/Guest/CheckoutController.php']),
    VHTML_HITS: count(/v-html\b/g, pageSource),
    FORBIDDEN_LITERAL_HITS: count(/\/storage\/|\$fixText|TextDecoder|IntersectionObserver|formatPrice|new Date\s*\(|toLocaleString\s*\(|Intl\.NumberFormat|1900xxxx|href="#"|v-html\b/g, all),
    LOOKUP_SINGLE_FACTOR_HITS: lookupSingleFactorHits,
    PR2C_HEAD_VBIND_SEO_HITS: count(/<Head\s+v-bind=["']seo["']/g, pageSource),
    PR2C_SEO_HEAD_IMPORT_MISSING: pageFiles.reduce((hits, file) => hits + (!source[file].includes("import SeoHead from '@/Components/SeoHead.vue';") || !source[file].includes('<SeoHead v-bind="page.seo" />') ? 1 : 0), 0),
    SUCCESS_FULL_PHONE_HITS: count(/page\.order\.(?:customer_phone|customer\.phone(?!_masked))/g, source['resources/js/Pages/Guest/CheckoutSuccess.vue']),
    SUCCESS_ADDRESS_HITS: count(/page\.order\.(?:shipping_address|customer_email|email|public_token|checkout_intent)/g, source['resources/js/Pages/Guest/CheckoutSuccess.vue']),
    UPDATE_ZERO_REMOVE_HITS: count(/quantity\s*={2,3}\s*0/g, source['app/Http/Controllers/Guest/CartController.php'] + source['app/Http/Requests/Storefront/UpdateCartItemRequest.php']),
    CART_DISPLAY_PARSE_HITS: count(/integerMoney|preg_replace\([^\n]*\$display/g, source['app/Services/Storefront/CartPresentationService.php']),
    CART_MUTATION_THROTTLE_MISSING: Math.max(4 - count(/throttle:commerce-cart/g, source['routes/web.php']), 0),
    CHECKOUT_THROTTLE_MISSING: Math.max(1 - count(/throttle:commerce-checkout/g, source['routes/web.php']), 0),
    CHECKOUT_PHONE_NORMALIZATION_MISSING: requestSource[0].includes('PhoneNormalizer') ? 0 : 1,
    LOOKUP_PHONE_NORMALIZATION_MISSING: requestSource.slice(1).some((request) => !request.includes('PhoneNormalizer')) ? 1 : 0,
};

for (const [key, value] of Object.entries(checks)) console.log(`${key}=${value}`);

const failures = Object.entries(checks).filter(([, value]) => value !== 0);
if (failures.length) {
    console.error('PR2C commerce audit failed.');
    process.exit(1);
}
console.log('PR2C commerce audit passed.');
