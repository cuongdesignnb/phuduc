<?php

namespace Tests\Feature\Storefront;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutUniqueConflictRecoveryTest extends TestCase
{
    use DatabaseMigrations;

    public function test_checkout_recovers_the_existing_order_after_a_checkout_intent_unique_conflict(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('This recovery harness requires MySQL.');
        }

        $product = Product::create(['name' => 'Race item', 'slug' => 'race-item', 'price' => 100000, 'stock' => 2, 'status' => 'active']);
        $this->withSession(['cart' => [$product->id => ['quantity' => 1]]])->get('/thanh-toan')->assertOk();
        $intent = session('checkout_intent');
        $pdo = $this->rawMysqlConnection();
        $conflictToken = 'race-public-token';

        Order::creating(function (Order $order) use ($pdo, $intent, $conflictToken): void {
            if ($order->checkout_intent !== $intent) {
                return;
            }

            $pdo->prepare('INSERT INTO orders (order_number, checkout_intent, public_token, customer_name, customer_phone, shipping_address, total_amount, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())')
                ->execute(['ORD-RACE-COMPETITOR', $intent, $conflictToken, 'Competitor', '0900000000', 'Address', 100000, 'pending']);
        });

        $first = $this->post('/thanh-toan', $this->checkoutData($intent))->assertRedirect();
        Order::first()->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 100000,
            'quantity' => 1,
            'total' => 100000,
        ]);
        $second = $this->post('/thanh-toan', $this->checkoutData($intent))->assertRedirect();

        $this->assertSame(route('checkout.success', ['token' => $conflictToken]), $first->headers->get('Location'));
        $this->assertSame($first->headers->get('Location'), $second->headers->get('Location'));
        $this->assertSame(1, Order::count());
        $this->assertSame(1, Order::first()->items()->count());
        $this->assertSame(['quantity' => 1], session()->get('cart.'.$product->id));
    }

    private function rawMysqlConnection(): \PDO
    {
        $config = config('database.connections.mysql');
        $pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
            $config['username'],
            $config['password'],
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );

        return $pdo;
    }

    private function checkoutData(?string $intent): array
    {
        return ['checkout_intent' => $intent, 'customer_name' => 'Synthetic', 'customer_phone' => '0900000000', 'shipping_address' => 'Address'];
    }
}
