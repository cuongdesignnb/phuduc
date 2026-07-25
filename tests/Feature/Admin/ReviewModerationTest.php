<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderation_requires_version_and_approved_review_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Sản phẩm', 'slug' => 'moderation-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);
        $review = Review::create(['product_id' => $product->id, 'customer_name' => 'Khách', 'content' => 'Tốt', 'rating' => 5, 'status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.reviews.updateStatus', $review), ['status' => 'approved'])->assertSessionHasErrors('version');
        $this->actingAs($admin)->patch(route('admin.reviews.updateStatus', $review), ['status' => 'approved', 'version' => app(AdminConcurrencyService::class)->version($review)])->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review), ['version' => app(AdminConcurrencyService::class)->version($review->refresh()), 'reason' => 'Cần lưu lịch sử'])->assertSessionHasErrors('review');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'status' => 'approved']);
    }
}
