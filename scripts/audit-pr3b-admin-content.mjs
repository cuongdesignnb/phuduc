import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const pageFiles = [
  'resources/js/Pages/Admin/Product/Index.vue', 'resources/js/Pages/Admin/Product/Edit.vue',
  'resources/js/Pages/Admin/Media/Index.vue', 'resources/js/Pages/Admin/Post/Index.vue',
  'resources/js/Pages/Admin/Post/Edit.vue', 'resources/js/Pages/Admin/PostCategory/Index.vue',
  'resources/js/Pages/Admin/PostCategory/Edit.vue', 'resources/js/Pages/Admin/Menu/Index.vue',
  'resources/js/Pages/Admin/Menu/Edit.vue', 'resources/js/Pages/Admin/HomeContent/Index.vue',
  'resources/js/Pages/Admin/Setting/Index.vue',
];
const sharedFiles = fs.readdirSync(path.join(root, 'resources/js/Components/Admin')).map((file) => `resources/js/Components/Admin/${file}`);
const frontend = [...pageFiles, ...sharedFiles].map(read).join('\n');
const controllers = [
  'app/Http/Controllers/Admin/MediaLibraryController.php', 'app/Http/Controllers/Admin/ProductController.php',
  'app/Http/Controllers/Admin/PostController.php', 'app/Http/Controllers/Admin/PostCategoryController.php',
  'app/Http/Controllers/Admin/MenuController.php', 'app/Http/Controllers/Admin/HomeContentController.php',
  'app/Http/Controllers/Admin/SettingController.php',
].map(read).join('\n');
const mediaController = read('app/Http/Controllers/Admin/MediaLibraryController.php');
const mediaService = read('app/Services/Admin/Media/AdminMediaService.php');
const productService = read('app/Services/Admin/Catalog/AdminProductService.php');
const imageService = read('app/Services/Admin/Catalog/ProductImageService.php');
const categoryService = read('app/Services/Admin/Content/AdminPostCategoryService.php');
const menuService = read('app/Services/Admin/Content/MenuItemSyncService.php');
const menuRegistry = read('app/Services/Admin/Content/MenuTargetRegistry.php');
const settingsService = read('app/Services/Admin/Content/AdminSettingService.php');
const settingRegistry = read('app/Services/Admin/Content/AdminSettingRegistry.php');
const homeService = read('app/Services/Admin/Content/AdminHomeContentService.php');
const mediaJob = read('app/Jobs/ProcessMediaUpload.php');
const indexBlock = mediaController.match(/public function index[\s\S]*?public function data/gi)?.[0] || '';
const rawModelPropHits = controllers.split('Inertia::render').slice(1).filter((block) => {
  const firstArgument = block.slice(0, block.indexOf(','));
  const secondArgument = block.slice(block.indexOf(',') + 1).trim();
  return /^(?:\$(?:product|post|menu|media|category))\s*[,)]/i.test(secondArgument) && !firstArgument.includes('edit');
}).length;

const counters = {
  RAW_PR3B_MODEL_PROP_HITS: rawModelPropHits,
  INLINE_PR3B_VALIDATION_HITS: (controllers.match(/->validate\s*\(|\$request->validate\s*\(/g) || []).length,
  CLIENT_MONEY_FORMATTER_HITS: (frontend.match(/Intl\.NumberFormat|toLocaleString|format(?:Price|Money)/g) || []).length,
  CLIENT_DATE_FORMATTER_HITS: (frontend.match(/new Date\s*\(|toLocaleDateString|toLocaleTimeString/g) || []).length,
  DIRECT_STORAGE_PATH_HITS: (frontend.match(/\/storage\//g) || []).length,
  FIX_TEXT_HITS: (frontend.match(/\$fixText|\bfixText\b/g) || []).length,
  TEXT_DECODER_HITS: (frontend.match(/TextDecoder/g) || []).length,
  NATIVE_CONFIRM_HITS: (frontend.match(/(?:window\.)?confirm\s*\(/g) || []).length,
  VHTML_HITS: (frontend.match(/v-html/g) || []).length,
  HASH_LINK_HITS: (frontend.match(/href="#"/g) || []).length,
  LEGACY_ADMIN_TOKEN_HITS: (frontend.match(/carbon-|volt-|industrial-/g) || []).length,
  HARDCODED_ADMIN_ENDPOINT_HITS: (frontend.match(/['"`]\/admin\/(?:media|products|posts|menus|settings)/g) || []).length,
  MEDIA_INDEX_JSON_RESPONSE_HITS: (indexBlock.match(/response\(\)->json/g) || []).length,
  MEDIA_PATH_MUTATION_HITS: (mediaJob.match(/file_path\s*=/g) || []).length,
  UNGUARDED_MEDIA_DELETE_HITS: mediaService.includes('references(') && mediaService.includes('ValidationException') ? 0 : 1,
  SHARED_MEDIA_PRODUCT_PATH_HITS: (imageService.match(/image_path['"]?\s*=>\s*\$media->file_path/g) || []).length,
  PRODUCT_ALL_IMAGES_INDEX_HITS: (productService.match(/index[\s\S]{0,1800}with\(['"]images['"]\)/g) || []).length,
  UNSCOPED_PRODUCT_IMAGE_HITS: (productService.match(/index[\s\S]{0,1800}images->/g) || []).length,
  UNGUARDED_PRODUCT_DELETE_HITS: productService.includes('$this->references->references') ? 0 : 1,
  CATEGORY_CYCLE_GUARD_MISSING: categoryService.includes('descendantIds') && categoryService.includes('assertParent') ? 0 : 1,
  CATEGORY_DELETE_GUARD_MISSING: categoryService.includes('children()->exists()') && categoryService.includes('posts()->exists()') ? 0 : 1,
  MENU_DELETE_ALL_SYNC_HITS: (menuService.match(/allItems\(\)->delete/gi) || []).length,
  MENU_TRANSACTION_MISSING: menuService.includes('DB::transaction') ? 0 : 1,
  MENU_OWNERSHIP_GUARD_MISSING: menuService.includes('! $existing->has') ? 0 : 1,
  MENU_VERSION_GUARD_MISSING: menuService.includes('assertVersion') ? 0 : 1,
  ARBITRARY_MENU_MODEL_TYPE_HITS: menuRegistry.includes("'url'") && menuRegistry.includes("'product'") && menuRegistry.includes("'post'") ? 0 : 1,
  ARBITRARY_SETTING_KEY_HITS: settingsService.includes('AdminSettingRegistry::all') && settingsService.includes('$registry[$item[\'key\']]') ? 0 : 1,
  CLIENT_SETTING_TYPE_TRUST_HITS: (frontend.match(/settings:\s*.*type|setting\.type\s*\)/g) || []).length,
  SETTING_TRANSACTION_MISSING: settingsService.includes('DB::transaction') ? 0 : 1,
  HOME_SETTING_KEY_HITS: (settingRegistry.match(/home\./g) || []).length,
  HOME_CONTENT_RAW_LOOKUP_COLLECTION_HITS: homeService.includes('productLookup') && homeService.includes('limit(20)') && homeService.includes('postLookup') ? 0 : 1,
  HOME_CONTENT_VERSION_GUARD_MISSING: homeService.includes('assertFingerprint') ? 0 : 1,
};

for (const [key, value] of Object.entries(counters)) console.log(`${key}=${value}`);
process.exitCode = Object.values(counters).every((value) => value === 0) ? 0 : 1;
