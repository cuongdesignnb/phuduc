<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Warranty;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) return;

        $customers = [
            ['name' => 'Nguyễn Văn Minh', 'phone' => '0909111222', 'email' => 'minh.nv@samtech.vn', 'address' => 'KCN Amata, Biên Hòa, Đồng Nai'],
            ['name' => 'Trần Thị Hương', 'phone' => '0912333444', 'email' => 'huong.tt@vinhomes.vn', 'address' => 'Vinhomes Grand Park, TP. Thủ Đức, TP.HCM'],
            ['name' => 'Lê Hoàng Nam', 'phone' => '0938555666', 'email' => 'nam.lh@vinpearl.com', 'address' => 'Vinpearl Resort, Phú Quốc, Kiên Giang'],
            ['name' => 'Phạm Minh Tuấn', 'phone' => '0977888999', 'email' => 'tuan.pm@samsung.com', 'address' => 'KCN Yên Phong, Bắc Ninh'],
            ['name' => 'Đỗ Thị Mai Anh', 'phone' => '0901222333', 'email' => 'maianh.dt@fpt.com.vn', 'address' => 'FPT City, Đà Nẵng'],
            ['name' => 'Hoàng Đức Thắng', 'phone' => '0945666777', 'email' => 'thang.hd@geleximco.vn', 'address' => 'KCN Geleximco, Hải Phòng'],
            ['name' => 'Vũ Ngọc Lan', 'phone' => '0888444555', 'email' => 'lan.vn@bvresthospital.vn', 'address' => 'Bệnh viện Quân Y 175, TP.HCM'],
        ];

        $statuses = ['completed', 'completed', 'completed', 'shipping', 'processing', 'pending', 'completed'];

        foreach ($customers as $i => $customer) {
            $orderDate = Carbon::now()->subDays(rand(5, 120));
            $orderProducts = $products->random(rand(1, 3));
            $totalAmount = 0;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'customer_name' => $customer['name'],
                'customer_phone' => $customer['phone'],
                'customer_email' => $customer['email'],
                'shipping_address' => $customer['address'],
                'total_amount' => 0,
                'status' => $statuses[$i],
                'notes' => $i === 0 ? 'Giao hàng giờ hành chính, liên hệ trước 30 phút' : ($i === 2 ? 'Cần hóa đơn VAT' : null),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            foreach ($orderProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->price;
                $total = $price * $qty;
                $totalAmount += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $total,
                ]);

                // Create warranties for completed orders
                if ($order->status === 'completed') {
                    for ($w = 0; $w < $qty; $w++) {
                        $activationDate = $orderDate->copy()->addDays(rand(3, 10));
                        Warranty::create([
                            'order_id' => $order->id,
                            'serial_number' => $product->sku . '-' . strtoupper(Warranty::count() + 1 < 10 ? '000' . (Warranty::count() + 1) : '00' . (Warranty::count() + 1)),
                            'product_name' => $product->name,
                            'activation_date' => $activationDate,
                            'expiration_date' => $activationDate->copy()->addYears(3),
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
