<?php

namespace Tests\Feature\Storefront;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_review_is_pending_plain_text_and_active_product_only(): void
    {
        $product = Product::create(['name' => 'Sản phẩm', 'slug' => 'review-product', 'price' => 100, 'stock' => 1, 'status' => 'active']);

        $this->post(route('reviews.store'), ['product_id' => $product->id, 'customer_name' => ' Khách ', 'customer_phone' => '+84900000000', 'customer_email' => ' TEST@EXAMPLE.COM ', 'content' => " <b>Tốt</b>\n\u{0007} ", 'rating' => 5, 'status' => 'approved'])->assertRedirect();
        $this->assertDatabaseHas('reviews', ['product_id' => $product->id, 'customer_name' => 'Khách', 'customer_phone' => '0900000000', 'customer_email' => 'test@example.com', 'content' => 'Tốt', 'status' => 'pending']);

        $inactive = Product::create(['name' => 'Ngừng bán', 'slug' => 'inactive-review-product', 'price' => 100, 'stock' => 1, 'status' => 'inactive']);
        $this->post(route('reviews.store'), ['product_id' => $inactive->id, 'customer_name' => 'Khách', 'content' => 'Không nhận', 'rating' => 4])->assertSessionHasErrors('product_id');
        $this->assertSame(1, Review::count());
    }
}
