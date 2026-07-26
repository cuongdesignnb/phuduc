<?php

namespace App\Services\Admin\Operations;

use App\Models\Review;
use App\Models\User;
use App\Services\Admin\AdminPageService;
use App\Services\Admin\AdminPresentationService;

class AdminReviewService
{
    public function __construct(
        private readonly AdminPageService $pages,
        private readonly AdminPresentationService $presentation,
        private readonly AdminReviewPresentationService $reviews,
        private readonly ReviewModerationService $moderation,
    ) {}

    public function index(User $user, array $filters): array
    {
        $query = Review::query()->select(['id', 'product_id', 'customer_name', 'customer_phone', 'customer_email', 'content', 'rating', 'status', 'created_at', 'updated_at'])->with('product:id,name,slug');
        $search = $filters['search'] ?? null;
        if ($search) {
            $escaped = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($query) use ($escaped): void {
                $query->where('customer_name', 'like', $escaped)
                    ->orWhere('content', 'like', $escaped)
                    ->orWhereHas('product', fn ($product) => $product->where('name', 'like', $escaped));
            });
        }
        $query->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status));
        $query->when($filters['rating'] ?? null, fn ($query, $rating) => $query->where('rating', $rating));
        $query->when($filters['product_id'] ?? null, fn ($query, $id) => $query->where('product_id', $id));
        $paginator = $query->latest()->paginate(15)->withQueryString();

        return $this->pages->envelope($user, 'admin_reviews_index', 'Đánh giá', [['label' => 'Đánh giá', 'url' => route('admin.reviews.index')]], [
            'items' => $paginator->getCollection()->map(fn (Review $review): array => $this->reviews->item($review))->values()->all(),
            'pagination' => $this->presentation->pagination($paginator),
            'filters' => ['search' => $filters['search'] ?? '', 'status' => $filters['status'] ?? '', 'rating' => $filters['rating'] ?? '', 'product_id' => $filters['product_id'] ?? ''],
            'statuses' => collect(['pending', 'approved', 'rejected'])->map(fn (string $key): array => ['key' => $key, 'label' => $this->presentation->statusLabel($key)])->all(),
            'ratings' => range(1, 5),
        ]);
    }

    public function updateStatus(Review $review, User $user, array $data): Review
    {
        return $this->moderation->updateStatus($review, $user, $data);
    }

    public function delete(Review $review, User $user, array $data): void
    {
        $this->moderation->delete($review, $user, $data);
    }
}
