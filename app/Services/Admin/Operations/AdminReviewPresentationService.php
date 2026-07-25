<?php

namespace App\Services\Admin\Operations;

use App\Models\Review;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\AdminPresentationService;

class AdminReviewPresentationService
{
    public function __construct(
        private readonly AdminPresentationService $presentation,
        private readonly AdminConcurrencyService $concurrency,
    ) {}

    public function item(Review $review): array
    {
        return [
            'id' => (int) $review->id,
            'customer' => [
                'name' => $review->customer_name,
                'phone_masked' => $this->maskPhone($review->customer_phone),
                'email_masked' => $this->maskEmail($review->customer_email),
            ],
            'product' => [
                'id' => $review->product?->id,
                'name' => $review->product?->name ?? 'Sản phẩm không còn tồn tại',
                'admin_url' => $review->product ? route('admin.products.edit', $review->product) : null,
                'storefront_url' => $review->product?->slug ? route('products.show', $review->product->slug) : null,
            ],
            'rating' => (int) $review->rating,
            'rating_label' => (int) $review->rating.' trên 5 sao',
            'content' => $review->content,
            'status' => $this->presentation->status($review->status),
            'created_at' => $review->created_at?->toIso8601String(),
            'created_at_display' => $this->presentation->date($review->created_at),
            'version' => $this->concurrency->version($review),
            'allowed_actions' => [
                'approve' => $review->status !== 'approved',
                'reject' => $review->status !== 'rejected',
                'delete' => $review->status !== 'approved',
            ],
        ];
    }

    private function maskPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        return strlen($phone) < 4 ? str_repeat('*', strlen($phone)) : substr($phone, 0, 2).str_repeat('*', max(0, strlen($phone) - 4)).substr($phone, -2);
    }

    private function maskEmail(?string $email): ?string
    {
        if (! $email || ! str_contains($email, '@')) {
            return null;
        }
        [$local, $domain] = explode('@', $email, 2);

        return substr($local, 0, 1).str_repeat('*', max(1, strlen($local) - 1)).'@'.$domain;
    }
}
