<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestReviewRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_five_submissions_are_allowed_and_sixth_submission_is_throttled(): void
    {
        $product = Product::create(['name' => 'Rate product', 'slug' => 'rate-review-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post(route('reviews.store'), [
                'product_id' => $product->id,
                'customer_name' => "Khách {$attempt}",
                'content' => 'Đánh giá tốt',
                'rating' => 5,
            ])->assertRedirect();
        }

        $this->post(route('reviews.store'), [
            'product_id' => $product->id,
            'customer_name' => 'Khách sáu',
            'content' => 'Đánh giá tốt',
            'rating' => 5,
        ])->assertStatus(429);
    }
}
