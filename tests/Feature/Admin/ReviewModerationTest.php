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
        $this->assertDatabaseHas('review_moderation_histories', ['review_id' => $review->id, 'actor_id' => $admin->id, 'action' => 'approved', 'from_status' => 'pending', 'to_status' => 'approved']);
    }

    public function test_review_transitions_and_delete_reason_are_audited_without_snapshots(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Sản phẩm', 'slug' => 'moderation-audit-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);
        $review = Review::create(['product_id' => $product->id, 'customer_name' => 'Khách', 'customer_phone' => '0900000000', 'customer_email' => 'customer@example.test', 'content' => 'Tốt', 'rating' => 5, 'status' => 'pending']);

        $this->actingAs($admin)->patch(route('admin.reviews.updateStatus', $review), ['status' => 'approved', 'version' => app(AdminConcurrencyService::class)->version($review)])->assertRedirect();
        $this->actingAs($admin)->patch(route('admin.reviews.updateStatus', $review), ['status' => 'rejected', 'version' => app(AdminConcurrencyService::class)->version($review->refresh())])->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review), ['version' => app(AdminConcurrencyService::class)->version($review->refresh()), 'reason' => 'Không phù hợp'])->assertRedirect();

        $this->assertDatabaseHas('review_moderation_histories', ['review_id' => null, 'review_reference' => (string) $review->id, 'actor_id' => $admin->id, 'action' => 'deleted', 'from_status' => 'rejected', 'reason' => 'Không phù hợp']);
        $this->assertDatabaseCount('review_moderation_histories', 3);
        $this->assertDatabaseMissing('review_moderation_histories', ['reason' => 'customer@example.test']);
    }
}
