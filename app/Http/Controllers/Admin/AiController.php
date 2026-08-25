<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiCreatePostRequest;
use App\Http\Requests\Admin\AiGenerateContentRequest;
use App\Http\Requests\Admin\AiScheduleRequest;
use App\Http\Requests\Admin\AiSettingsRequest;
use App\Models\AiGeneration;
use App\Models\PostCategory;
use App\Models\Product;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\Ai\AiConfigurationService;
use App\Services\Admin\Ai\AiContentGenerationService;
use App\Services\Admin\Ai\AiContentScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiController extends Controller
{
    public function index(AiConfigurationService $configuration, AiContentScheduleService $schedules, AdminPageService $pages): Response
    {
        return Inertia::render('Admin/Ai/Index', $pages->envelope(
            request()->user(),
            'admin_ai_index',
            'AI nội dung & SEO',
            [['label' => 'AI nội dung & SEO', 'url' => route('admin.ai.index')]],
            [
                'settings' => $configuration->publicSnapshot(),
                'categories' => PostCategory::query()->orderBy('name')->get(['id', 'name']),
                'schedules' => $schedules->all(),
                'types' => [
                    ['key' => 'article', 'label' => 'Bài viết'],
                    ['key' => 'seo', 'label' => 'Bài SEO'],
                    ['key' => 'product_description', 'label' => 'Mô tả sản phẩm'],
                    ['key' => 'category_description', 'label' => 'Mô tả danh mục'],
                ],
            ],
        ));
    }

    public function saveSettings(AiSettingsRequest $request, AiConfigurationService $configuration): RedirectResponse
    {
        $configuration->save($request->validated());

        return back()->with('success', 'Cài đặt AI đã được lưu an toàn.');
    }

    public function generate(AiGenerateContentRequest $request, AiContentGenerationService $generator): JsonResponse
    {
        return response()->json($generator->generate($request->validated(), $request->user()));
    }

    public function generateProductSeo(Product $product, Request $request, AiContentGenerationService $generator): JsonResponse
    {
        $data = [
            'type' => 'product_description',
            'topic' => $product->name,
            'product_id' => $product->id,
            'tone' => $request->string('tone')->toString() ?: 'professional',
            'length' => $request->string('length')->toString() ?: 'medium',
            'full_article' => false,
            'with_images' => false,
            'keywords' => array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', (string) $request->input('keywords', ''), -1, PREG_SPLIT_NO_EMPTY)))),
        ];

        return response()->json($generator->generate($data, $request->user()));
    }

    public function createPost(string $generationId, AiCreatePostRequest $request, AiContentGenerationService $generator): RedirectResponse
    {
        $generation = AiGeneration::query()->where('generation_id', $generationId)->firstOrFail();
        $post = $generator->saveArticle($generation, $request->user(), $request->boolean('auto_publish'));

        return redirect()->route('admin.posts.edit', $post)->with('success', 'Bài viết AI đã được tạo ở trạng thái '.($post->status === 'published' ? 'đã đăng' : 'bản nháp').'.');
    }

    public function storeSchedule(AiScheduleRequest $request, AiContentScheduleService $schedules): RedirectResponse
    {
        $schedules->create($request->validated(), $request->user());

        return back()->with('success', 'Đã thêm lịch sinh bài AI.');
    }

    public function destroySchedule(int $schedule, Request $request): RedirectResponse
    {
        \App\Models\AiContentSchedule::query()->whereKey($schedule)->where('status', 'pending')->delete();

        return back()->with('success', 'Đã xóa lịch AI đang chờ.');
    }
}
