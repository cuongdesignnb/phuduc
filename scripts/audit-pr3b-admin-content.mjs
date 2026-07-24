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
  'resources/js/Components/AdvancedTextEditor.vue', 'resources/js/Components/MenuItemTree.vue',
  'resources/js/Components/Admin/AdminMediaPicker.vue', 'resources/js/Components/Admin/AdminEntityPicker.vue',
  'resources/js/Components/Admin/CategoryTree.vue', 'resources/js/Composables/useStableClientKey.js',
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
const mediaReferences = read('app/Services/Admin/Media/MediaReferenceService.php');
const mediaStorage = read('app/Services/Admin/Media/AdminImageStorageService.php');
const postService = read('app/Services/Admin/Content/AdminPostService.php');
const mediaAssetRule = read('app/Rules/MediaAssetRule.php');
const mediaAssetService = read('app/Services/Admin/Media/MediaAssetService.php');
const menuTargetService = read('app/Services/Admin/Content/AdminMenuTargetService.php');
const menuTargetController = read('app/Http/Controllers/Admin/MenuTargetController.php');
const menuUpdateRequest = read('app/Http/Requests/Admin/UpdateMenuRequest.php');
const menuTree = read('resources/js/Components/MenuItemTree.vue');
const postEdit = read('resources/js/Pages/Admin/Post/Edit.vue');
const productEdit = read('resources/js/Pages/Admin/Product/Edit.vue');
const menuEdit = read('resources/js/Pages/Admin/Menu/Edit.vue');
const homeEdit = read('resources/js/Pages/Admin/HomeContent/Index.vue');
const settingEdit = read('resources/js/Pages/Admin/Setting/Index.vue');
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
  HOME_CONTENT_RAW_LOOKUP_COLLECTION_HITS: homeService.includes('productLookup') && homeService.includes('limit(') && homeService.includes('postLookup') ? 0 : 1,
  HOME_CONTENT_VERSION_GUARD_MISSING: homeService.includes('assertFingerprint') ? 0 : 1,
  PER_ITEM_PRODUCT_REFERENCE_QUERY_HITS: productService.includes('->canDelete($product)') || productService.match(/presentation->item\(\$product\)/) ? 1 : 0,
  PER_ITEM_MEDIA_REFERENCE_QUERY_HITS: mediaService.match(/map\([^\n]*presentation->item\(\$media\)/) ? 1 : 0,
  PER_ITEM_POST_MEDIA_LOOKUP_HITS: postService.includes('idForPath($post->featured_image)') ? 1 : 0,
  PICKER_FULL_REFERENCE_DTO_HITS: mediaService.includes('presentation->item($media)') ? 1 : 0,
  MENU_NON_RECURSIVE_UI_HITS: menuEdit.includes('v-for="(child') || menuEdit.includes('item.children"') ? 1 : 0,
  MENU_DATE_NOW_KEY_HITS: (menuEdit.match(/Date\.now\(/g) || []).length,
  UNSAFE_MENU_URL_GUARD_MISSING: read('app/Services/Admin/Content/AdminUrlService.php').includes('javascript') && menuService.includes('urls->normalize') ? 0 : 1,
  MENU_REORDER_CONTROLS_MISSING: menuEdit.includes('MenuItemTree') && frontend.includes('Đưa mục lên') && frontend.includes('draggable') ? 0 : 1,
  SETTING_MEDIA_CONTRACT_MISSING: settingEdit.includes('AdminMediaPicker') && !settingEdit.includes('module.media') ? 0 : 1,
  SETTING_OG_KEY_MISMATCH: settingRegistry.includes('seo.og_image') || mediaReferences.includes('seo.og_image') ? 1 : 0,
  SETTING_FONT_OPTIONS_UNUSED: settingEdit.includes('module.font_options') ? 0 : 1,
  MEDIA_WEBP_PIPELINE_MISSING: mediaService.includes('AdminImageStorageService') && mediaStorage.includes('toWebp') ? 0 : 1,
  PRODUCT_WEBP_PIPELINE_MISSING: imageService.includes('AdminImageStorageService') && mediaStorage.includes('toWebp') ? 0 : 1,
  HOME_ITEM_FINGERPRINT_MISSING: homeService.includes("'item:'") && homeService.includes('item->updated_at') ? 0 : 1,
  HOME_REMOTE_LOOKUP_UNUSED: homeEdit.includes('axios') && homeEdit.includes('admin.home-content.') && homeEdit.includes('loadLookup') ? 0 : 1,
  PRODUCT_360_UI_MISSING: productEdit.includes('Tải ảnh 360') && productEdit.includes('pickerIs360') ? 0 : 1,
  PRODUCT_REORDER_UI_MISSING: productEdit.includes('vuedraggable') && productEdit.includes('products.images.reorder') ? 0 : 1,
  POST_RICH_EDITOR_MISSING: postEdit.includes('AdvancedTextEditor') ? 0 : 1,
  CATEGORY_RECURSIVE_UI_MISSING: read('resources/js/Components/Admin/CategoryTree.vue').includes('<CategoryTree') ? 0 : 1,
  IMAGE_PICKER_MEDIA_TYPE_MISSING: frontend.includes('mediaType') && ['Product/Edit.vue', 'Post/Edit.vue', 'HomeContent/Index.vue', 'Setting/Index.vue'].every((file) => read(`resources/js/Pages/Admin/${file}`).includes('media-type="image"')) ? 0 : 1,
  PRODUCT_MEDIA_IMAGE_GUARD_MISSING: mediaAssetRule.includes("mime_type', 'like', 'image/%'") && read('app/Http/Requests/Admin/AttachProductMediaRequest.php').includes('MediaAssetRule::image') && imageService.includes('requireImage') ? 0 : 1,
  POST_MEDIA_IMAGE_GUARD_MISSING: read('app/Http/Requests/Admin/StorePostRequest.php').includes('MediaAssetRule::image') && postService.includes('requireImage') ? 0 : 1,
  SETTING_MEDIA_IMAGE_GUARD_MISSING: read('app/Http/Requests/Admin/SaveSettingsRequest.php').includes('MediaAssetRule::image') && settingsService.includes('requireImage') ? 0 : 1,
  HOME_MEDIA_IMAGE_GUARD_MISSING: read('app/Http/Requests/Admin/SaveHomeContentRequest.php').includes('MediaAssetRule::image') && homeService.includes('requireImage') ? 0 : 1,
  MENU_TARGET_SELECTOR_MISSING: menuTargetController.includes('products') && menuTargetController.includes('categories') && menuTargetService.includes('limit') ? 0 : 1,
  MENU_MODEL_ID_CONTROL_MISSING: menuTree.includes('AdminEntityPicker') && menuTree.includes('model_id') ? 0 : 1,
  MENU_DETAILS_VERSION_MISSING: menuUpdateRequest.includes("rules['version']") && menuEdit.includes('version: menu?.version') ? 0 : 1,
  MENU_CROSS_FORM_VERSION_SYNC_MISSING: menuEdit.includes('itemForm.version = version') && menuEdit.includes('form.version = version') ? 0 : 1,
  TEL_URL_RUNTIME_GUARD_MISSING: read('app/Services/Admin/Content/AdminUrlService.php').includes("/^tel:") && read('app/Services/Admin/Content/AdminUrlService.php').includes('FILTER_VALIDATE_EMAIL') ? 0 : 1,
  PRODUCT_IMAGE_STATE_SYNC_MISSING: productEdit.includes('syncImages') && productEdit.includes('onSuccess: syncFromPage') ? 0 : 1,
  PRODUCT_KEYBOARD_REORDER_MISSING: productEdit.includes('moveImage') && productEdit.includes('aria-label="Đưa ảnh sang trái"') ? 0 : 1,
  IMAGE_CLEAR_ACTION_MISSING: postEdit.includes('clearMedia') && settingEdit.includes('clearMedia') && homeEdit.includes('clearItemMedia') && homeEdit.includes('clearConfigMedia') ? 0 : 1,
  UNINTENDED_ENGLISH_ADMIN_LABEL_HITS: (frontend.match(/(?:Choose media|Search media|Save settings|Save content|Save product|Save post|Delete product image|Add item|Remove item|Item label|Safe URL|Back|Loading\.\.\.|No media found|Previous|Next)/g) || []).length,
};

for (const [key, value] of Object.entries(counters)) console.log(`${key}=${value}`);
process.exitCode = Object.values(counters).every((value) => value === 0) ? 0 : 1;
