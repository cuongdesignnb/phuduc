<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdvancedTextEditor from '@/Components/AdvancedTextEditor.vue';
import MediaBox from '@/Components/MediaBox.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({ settings: Object });

const fixText = (value) => {
    if (typeof value !== 'string') return value;
    const codes = Array.from(value).map((char) => char.charCodeAt(0));
    const isBroken = codes.some((code) => [0xc2, 0xc3, 0xc4, 0xc6, 0xca, 0xfffd].includes(code))
        || codes.some((code) => code >= 0x80 && code <= 0x9f)
        || codes.some((code, index) => code === 0xe1 && [0xba, 0xbb].includes(codes[index + 1]));
    if (!isBroken) return value;
    try {
        const bytes = Uint8Array.from(Array.from(value), (char) => char.charCodeAt(0) & 255);
        return new TextDecoder('utf-8', { fatal: false }).decode(bytes);
    } catch {
        return value;
    }
};

const allSettings = ref(Object.entries(props.settings || {}).flatMap(([group, items]) =>
    items.map(s => ({ ...s, value: s.value ? fixText(s.value) : '' }))
));

// Default settings structure if empty
if (!allSettings.value.length) {
    allSettings.value = [
        { key: 'site.name', value: '', type: 'text' },
        { key: 'site.logo', value: '', type: 'image' },
        { key: 'site.favicon', value: '', type: 'image' },
        { key: 'site.phone', value: '', type: 'text' },
        { key: 'site.hotline', value: '', type: 'text' },
        { key: 'site.email', value: '', type: 'text' },
        { key: 'site.address', value: '', type: 'text' },
        { key: 'site.primary_color', value: '#ffd400', type: 'color' },
        { key: 'font.heading', value: 'Rajdhani', type: 'font' },
        { key: 'font.body', value: 'Inter', type: 'font' },
        { key: 'home.hero_title', value: '', type: 'text' },
        { key: 'home.hero_subtitle', value: '', type: 'text' },
        { key: 'home.hero_image', value: '', type: 'image' },
        { key: 'home.about_text', value: '', type: 'textarea' },
        { key: 'about.title', value: '', type: 'text' },
        { key: 'about.content', value: '', type: 'textarea' },
        { key: 'about.mission', value: '', type: 'textarea' },
        { key: 'about.vision', value: '', type: 'textarea' },
    ];
}

const requiredSettings = [
    { key: 'home.hero_primary_label', value: 'Xem s\u1ea3n ph\u1ea9m', type: 'text' },
    { key: 'home.hero_primary_url', value: '/san-pham', type: 'text' },
    { key: 'home.hero_secondary_label', value: 'T\u01b0 v\u1ea5n ngay', type: 'text' },
    { key: 'home.featured_products_title', value: 'S\u1ea3n ph\u1ea9m n\u1ed5i b\u1eadt', type: 'text' },
    { key: 'home.featured_products_limit', value: '4', type: 'text' },
    { key: 'home.latest_posts_title', value: 'Tin t\u1ee9c n\u1ed5i b\u1eadt', type: 'text' },
    { key: 'home.latest_posts_limit', value: '3', type: 'text' },
    { key: 'home.energy_eyebrow', value: 'N\u0103ng l\u01b0\u1ee3ng xanh', type: 'text' },
    { key: 'home.energy_title', value: 'Cho t\u01b0\u01a1ng lai b\u1ec1n v\u1eefng', type: 'text' },
    { key: 'home.energy_description', value: 'S\u1ea3n ph\u1ea9m xe \u0111i\u1ec7n & thi\u1ebft b\u1ecb \u0111i\u1ec7n c\u00f4ng nghi\u1ec7p gi\u00fap doanh nghi\u1ec7p t\u1ed1i \u01b0u chi ph\u00ed v\u1eadn h\u00e0nh.', type: 'textarea' },
    { key: 'home.energy_stat_1_label', value: 'Ti\u1ebft ki\u1ec7m n\u0103ng l\u01b0\u1ee3ng', type: 'text' },
    { key: 'home.energy_stat_1_value', value: '30-50%', type: 'text' },
    { key: 'home.energy_stat_2_label', value: 'Gi\u1ea3m ph\u00e1t th\u1ea3i CO\u2082', type: 'text' },
    { key: 'home.energy_stat_2_value', value: '> 60%', type: 'text' },
];

requiredSettings.forEach((setting) => {
    if (!allSettings.value.some((item) => item.key === setting.key)) {
        allSettings.value.push(setting);
    }
});

// Ensure font settings exist
const hasFontHeading = allSettings.value.some(s => s.key === 'font.heading');
const hasFontBody = allSettings.value.some(s => s.key === 'font.body');
if (!hasFontHeading) allSettings.value.push({ key: 'font.heading', value: 'Rajdhani', type: 'font' });
if (!hasFontBody) allSettings.value.push({ key: 'font.body', value: 'Inter', type: 'font' });

// Key → Vietnamese label mapping
const keyLabels = {
    'home.hero_primary_label': 'N\u00fat ch\u00ednh Hero',
    'home.hero_primary_url': 'Li\u00ean k\u1ebft n\u00fat ch\u00ednh',
    'home.hero_secondary_label': 'N\u00fat t\u01b0 v\u1ea5n Hero',
    'home.featured_products_title': 'Ti\u00eau \u0111\u1ec1 s\u1ea3n ph\u1ea9m n\u1ed5i b\u1eadt',
    'home.featured_products_limit': 'S\u1ed1 s\u1ea3n ph\u1ea9m hi\u1ec3n th\u1ecb',
    'home.latest_posts_title': 'Ti\u00eau \u0111\u1ec1 tin t\u1ee9c n\u1ed5i b\u1eadt',
    'home.latest_posts_limit': 'S\u1ed1 tin t\u1ee9c hi\u1ec3n th\u1ecb',
    'home.energy_eyebrow': 'Nhãn banner n\u0103ng l\u01b0\u1ee3ng',
    'home.energy_title': 'Ti\u00eau \u0111\u1ec1 banner n\u0103ng l\u01b0\u1ee3ng',
    'home.energy_description': 'M\u00f4 t\u1ea3 banner n\u0103ng l\u01b0\u1ee3ng',
    'home.energy_stat_1_label': 'Nhãn th\u1ed1ng k\u00ea 1',
    'home.energy_stat_1_value': 'Gi\u00e1 tr\u1ecb th\u1ed1ng k\u00ea 1',
    'home.energy_stat_2_label': 'Nhãn th\u1ed1ng k\u00ea 2',
    'home.energy_stat_2_value': 'Gi\u00e1 tr\u1ecb th\u1ed1ng k\u00ea 2',
    'site.name': 'Tên website',
    'site.tagline': 'Slogan',
    'site.description': 'Mô tả website',
    'site.logo': 'Logo',
    'site.favicon': 'Favicon',
    'site.email': 'Email',
    'site.phone': 'Số điện thoại',
    'site.hotline': 'Hotline',
    'site.address': 'Địa chỉ',
    'site.working_hours': 'Giờ làm việc',
    'site.facebook': 'Facebook',
    'site.zalo': 'Zalo',
    'site.youtube': 'Youtube',
    'site.map_embed': 'Google Map (Embed URL)',
    'site.copyright': 'Bản quyền',
    'site.primary_color': 'Màu chủ đạo',
    'font.heading': 'Font tiêu đề (Heading)',
    'font.body': 'Font nội dung (Body)',
    'home.hero_title': 'Tiêu đề Hero',
    'home.hero_subtitle': 'Phụ đề Hero',
    'home.hero_image': 'Ảnh Hero',
    'home.about_text': 'Giới thiệu ngắn',
    'home.about_image': 'Ảnh giới thiệu',
    'about.title': 'Tiêu đề trang',
    'about.content': 'Nội dung',
    'about.image': 'Ảnh giới thiệu',
    'about.mission': 'Sứ mệnh',
    'about.vision': 'Tầm nhìn',
    'seo.default_title': 'Tiêu đề SEO mặc định',
    'seo.default_description': 'Mô tả SEO mặc định',
    'seo.default_keywords': 'Từ khóa SEO mặc định',
};

const getLabel = (key) => keyLabels[key] || key.split('.').pop().replace(/_/g, ' ');

// Vietnamese-friendly Google Fonts
const vietnameseFonts = [
    { name: 'Inter', category: 'sans-serif', label: 'Inter — Hiện đại, dễ đọc' },
    { name: 'Be Vietnam Pro', category: 'sans-serif', label: 'Be Vietnam Pro — Thiết kế cho tiếng Việt' },
    { name: 'Nunito', category: 'sans-serif', label: 'Nunito — Tròn, thân thiện' },
    { name: 'Nunito Sans', category: 'sans-serif', label: 'Nunito Sans — Sạch, chuyên nghiệp' },
    { name: 'Montserrat', category: 'sans-serif', label: 'Montserrat — Mạnh mẽ, nổi bật' },
    { name: 'Open Sans', category: 'sans-serif', label: 'Open Sans — Phổ biến, dễ đọc' },
    { name: 'Roboto', category: 'sans-serif', label: 'Roboto — Chuẩn Material Design' },
    { name: 'Source Sans 3', category: 'sans-serif', label: 'Source Sans 3 — Adobe, rõ ràng' },
    { name: 'Mulish', category: 'sans-serif', label: 'Mulish — Thanh lịch, đa năng' },
    { name: 'Quicksand', category: 'sans-serif', label: 'Quicksand — Tròn, sáng tạo' },
    { name: 'Lexend', category: 'sans-serif', label: 'Lexend — Tối ưu đọc nhanh' },
    { name: 'Rajdhani', category: 'sans-serif', label: 'Rajdhani — Công nghệ, công nghiệp' },
    { name: 'Barlow', category: 'sans-serif', label: 'Barlow — Kỹ thuật, hiện đại' },
    { name: 'Barlow Condensed', category: 'sans-serif', label: 'Barlow Condensed — Gọn, tiêu đề' },
    { name: 'Josefin Sans', category: 'sans-serif', label: 'Josefin Sans — Sang trọng, vintage' },
    { name: 'Space Grotesk', category: 'sans-serif', label: 'Space Grotesk — Futuristic' },
    { name: 'Exo 2', category: 'sans-serif', label: 'Exo 2 — Sci-fi, năng động' },
    { name: 'Sarabun', category: 'sans-serif', label: 'Sarabun — Nhẹ, thanh thoát' },
    { name: 'Noto Sans', category: 'sans-serif', label: 'Noto Sans — Google, đa ngôn ngữ' },
    { name: 'Lora', category: 'serif', label: 'Lora — Serif hiện đại, sang trọng' },
    { name: 'Merriweather', category: 'serif', label: 'Merriweather — Serif, đọc tốt' },
    { name: 'Playfair Display', category: 'serif', label: 'Playfair Display — Serif, cao cấp' },
    { name: 'EB Garamond', category: 'serif', label: 'EB Garamond — Cổ điển, tinh tế' },
];

// Preview font by loading it dynamically
const previewFont = (fontName) => {
    const link = document.getElementById('font-preview-link');
    if (link) link.remove();
    const el = document.createElement('link');
    el.id = 'font-preview-link';
    el.rel = 'stylesheet';
    el.href = `https://fonts.googleapis.com/css2?family=${fontName.replace(/ /g, '+')}:wght@300;400;500;600;700;800;900&display=swap`;
    document.head.appendChild(el);
};

const isSaving = ref(false);
const toast = useToast();

const save = () => {
    if (isSaving.value) return;
    isSaving.value = true;
    router.post(route('admin.settings.save'), {
        settings: allSettings.value.filter(s => s.key),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Cài đặt đã được lưu thành công!');
        },
        onError: (errors) => {
            toast.error('Lưu cài đặt thất bại. Vui lòng thử lại.');
        },
        onFinish: () => {
            isSaving.value = false;
        },
    });
};

// Tab system
const tabs = [
    { id: 'site', label: 'Thông tin chung', icon: 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418' },
    { id: 'appearance', label: 'Giao diện', icon: 'M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42' },
    { id: 'home', label: 'Trang chủ', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' },
    { id: 'about', label: 'Giới thiệu', icon: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z' },
    { id: 'seo', label: 'SEO', icon: 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z' },
];

const activeTab = ref('site');

const groupedSettings = computed(() => {
    const groups = {};
    allSettings.value.forEach((s, i) => {
        let group = s.key.split('.')[0] || 'other';
        if (s.type === 'color' || s.type === 'font') group = 'appearance';
        if (!groups[group]) groups[group] = [];
        groups[group].push({ ...s, index: i });
    });
    return groups;
});

const currentItems = computed(() => groupedSettings.value[activeTab.value] || []);

// Preset colors for quick selection
const presetColors = [
    { hex: '#ffd400', label: 'Vàng công nghiệp (Mặc định)' },
    { hex: '#09DE52', label: 'Xanh lá neon' },
    { hex: '#3B82F6', label: 'Xanh dương' },
    { hex: '#EF4444', label: 'Đỏ' },
    { hex: '#F59E0B', label: 'Vàng cam' },
    { hex: '#8B5CF6', label: 'Tím' },
    { hex: '#EC4899', label: 'Hồng' },
    { hex: '#14B8A6', label: 'Xanh ngọc' },
    { hex: '#F97316', label: 'Cam' },
    { hex: '#06B6D4', label: 'Cyan' },
];

const showMediaBox = ref(false);
const mediaTargetIndex = ref(null);

const openMediaBox = (index) => {
    mediaTargetIndex.value = index;
    showMediaBox.value = true;
};

const onMediaSelected = (media) => {
    if (mediaTargetIndex.value !== null) {
        allSettings.value[mediaTargetIndex.value].value = media.file_path || '';
    }
    showMediaBox.value = false;
};
</script>

<template>
    <Head title="Cài đặt" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-display font-bold text-white">Cài đặt Website</h2>
                <button @click="save" :disabled="isSaving" class="inline-flex items-center gap-1.5 rounded-xl bg-volt-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-volt-600 transition-all shadow-lg shadow-volt-500/20 disabled:opacity-50">
                    <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ isSaving ? 'Đang lưu...' : 'Lưu cài đặt' }}
                </button>
            </div>
        </template>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-6">
                <!-- Top Tabs -->
                <div class="flex items-center gap-1 mb-6 rounded-xl bg-carbon-900/60 border border-white/5 p-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200"
                        :class="activeTab === tab.id
                            ? 'bg-volt-500/15 text-volt-400 shadow-sm'
                            : 'text-carbon-400 hover:text-white hover:bg-carbon-800/50'"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/></svg>
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Content Area -->
                <div class="rounded-2xl border border-white/5 bg-carbon-900/50 backdrop-blur-sm p-6">

                    <!-- ══════ Appearance Tab (Color + Font) ══════ -->
                    <div v-if="activeTab === 'appearance'" class="space-y-8">
                        <!-- Primary Color -->
                        <div v-for="item in currentItems.filter(i => i.type === 'color')" :key="item.index">
                            <h4 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-volt-500"></span>
                                Màu chủ đạo
                            </h4>
                            <div class="rounded-xl border border-white/5 bg-carbon-800/30 p-5 space-y-4">
                                <div class="flex items-center gap-4">
                                    <input type="color" v-model="allSettings[item.index].value" class="w-14 h-14 rounded-xl border-2 border-white/10 cursor-pointer bg-transparent" style="padding: 2px;" />
                                    <div class="flex-1">
                                        <input v-model="allSettings[item.index].value" type="text" placeholder="#09DE52" maxlength="7" class="w-full text-sm rounded-xl border border-white/10 bg-carbon-800 text-white font-mono py-2.5 px-4 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition uppercase" />
                                        <p class="text-xs text-carbon-500 mt-1">Nhập mã màu HEX hoặc chọn từ bảng gợi ý bên dưới</p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-carbon-500 mb-2">Gợi ý nhanh:</p>
                                    <div class="flex flex-wrap gap-2">
                                        <button v-for="preset in presetColors" :key="preset.hex" @click="allSettings[item.index].value = preset.hex" :title="preset.label" class="w-9 h-9 rounded-lg border-2 transition-all duration-200 hover:scale-110" :class="allSettings[item.index].value?.toLowerCase() === preset.hex.toLowerCase() ? 'border-white shadow-lg scale-110' : 'border-white/10 hover:border-white/30'" :style="{ backgroundColor: preset.hex }"></button>
                                    </div>
                                </div>
                                <div class="p-4 rounded-lg bg-carbon-950/50 border border-white/5">
                                    <p class="text-xs text-carbon-500 mb-3">Xem trước:</p>
                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white" :style="{ backgroundColor: allSettings[item.index].value }">Nút chính</span>
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold border-2" :style="{ borderColor: allSettings[item.index].value, color: allSettings[item.index].value }">Nút viền</span>
                                        <span class="text-sm font-semibold" :style="{ color: allSettings[item.index].value }">Văn bản chủ đạo</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fonts -->
                        <div>
                            <h4 class="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-volt-500"></span>
                                Phông chữ
                            </h4>
                            <div class="space-y-4">
                                <div v-for="item in currentItems.filter(i => i.type === 'font')" :key="item.index" class="rounded-xl border border-white/5 bg-carbon-800/30 p-5">
                                    <label class="block text-sm font-medium text-carbon-300 mb-2">{{ getLabel(item.key) }}</label>
                                    <select v-model="allSettings[item.index].value" @change="previewFont(allSettings[item.index].value)" class="w-full rounded-xl border border-white/10 bg-carbon-800 text-white py-2.5 px-4 text-sm focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                        <option v-for="f in vietnameseFonts" :key="f.name" :value="f.name">{{ f.label }}</option>
                                    </select>
                                    <div class="mt-3 p-4 rounded-lg bg-carbon-950/50 border border-white/5">
                                        <p class="text-xs text-carbon-500 mb-2">Xem trước:</p>
                                        <p v-if="item.key === 'font.heading'" :style="{ fontFamily: allSettings[item.index].value + ', sans-serif' }" class="text-2xl font-bold text-white">Phú Đức Electric Vehicle — Xe Điện Công Nghiệp</p>
                                        <p v-else :style="{ fontFamily: allSettings[item.index].value + ', sans-serif' }" class="text-base text-carbon-200 leading-relaxed">Phú Đức cung cấp các dòng xe điện công nghiệp chất lượng cao, phục vụ vận chuyển trong khu du lịch, sân golf, nhà máy và khu đô thị.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ══════ Regular Tabs (site, home, about, seo) ══════ -->
                    <div v-else class="space-y-4">
                        <div v-for="item in currentItems" :key="item.index" class="rounded-xl border border-white/5 bg-carbon-800/30 p-4">
                            <label class="block text-sm font-medium text-carbon-300 mb-2">{{ getLabel(item.key) }}</label>

                            <!-- Textarea / Rich text -->
                            <AdvancedTextEditor v-if="item.type === 'textarea'" v-model="allSettings[item.index].value" :height="150" />

                            <!-- JSON -->
                            <textarea v-else-if="item.type === 'json'" v-model="allSettings[item.index].value" rows="3" class="w-full text-sm rounded-xl border border-white/10 bg-carbon-800 text-white font-mono py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition"></textarea>

                            <!-- Image -->
                            <div v-else-if="item.type === 'image'" class="space-y-2">
                                <div class="flex gap-2">
                                    <input v-model="allSettings[item.index].value" type="text" placeholder="media/file.webp" class="flex-1 text-sm rounded-xl border border-white/10 bg-carbon-800 text-white placeholder-carbon-500 py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                                    <button @click="openMediaBox(item.index)" type="button" class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-carbon-800 px-3 py-2 text-sm font-medium text-carbon-300 hover:bg-carbon-700 hover:text-white transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Media
                                    </button>
                                </div>
                                <img v-if="allSettings[item.index].value" :src="'/storage/' + allSettings[item.index].value.replace(/^\/?storage\//, '')" class="h-20 w-auto rounded-lg border border-white/10 object-cover" />
                            </div>

                            <!-- Boolean -->
                            <select v-else-if="item.type === 'boolean'" v-model="allSettings[item.index].value" class="w-full text-sm rounded-xl border border-white/10 bg-carbon-800 text-white py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option value="1">Bật</option>
                                <option value="0">Tắt</option>
                            </select>

                            <!-- Color (fallback) -->
                            <div v-else-if="item.type === 'color'" class="flex items-center gap-2">
                                <input type="color" v-model="allSettings[item.index].value" class="w-10 h-10 rounded-lg border border-white/10 cursor-pointer bg-transparent" style="padding: 1px;" />
                                <input v-model="allSettings[item.index].value" type="text" maxlength="7" class="flex-1 text-sm rounded-xl border border-white/10 bg-carbon-800 text-white font-mono py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition uppercase" />
                            </div>

                            <!-- Font (fallback) -->
                            <select v-else-if="item.type === 'font'" v-model="allSettings[item.index].value" class="w-full text-sm rounded-xl border border-white/10 bg-carbon-800 text-white py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition">
                                <option v-for="f in vietnameseFonts" :key="f.name" :value="f.name">{{ f.label }}</option>
                            </select>

                            <!-- Text (default) -->
                            <input v-else v-model="allSettings[item.index].value" type="text" class="w-full text-sm rounded-xl border border-white/10 bg-carbon-800 text-white py-2 px-3 focus:border-volt-500/50 focus:ring-1 focus:ring-volt-500/20 transition" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MediaBox Modal -->
        <Teleport to="body">
            <div v-if="showMediaBox" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="showMediaBox = false">
                <div class="bg-carbon-900 border border-white/10 p-6 rounded-2xl w-[80vw] max-w-5xl h-[75vh] overflow-y-auto shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-display font-semibold text-white">Chọn ảnh từ Media Library</h3>
                        <button @click="showMediaBox = false" class="rounded-lg p-1.5 text-carbon-400 hover:bg-red-500/10 hover:text-red-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <MediaBox @select="onMediaSelected" />
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
