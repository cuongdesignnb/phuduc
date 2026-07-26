<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\Operations\OrderStatusTransitionService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$orderId, $adminId, $version, $readyFile, $startFile, $resultFile] = array_pad(array_slice($argv, 1), 6, null);
$result = [
    'fixture_visible' => false,
    'barrier_passed' => false,
    'outcome' => 'error',
    'exception' => null,
];

try {
    $order = Order::query()->with('items')->find((int) $orderId);
    $admin = User::query()->find((int) $adminId);
    $item = $order?->items?->first();
    $product = $item?->product_id ? Product::query()->find((int) $item->product_id) : null;
    $orderItem = $item?->id ? OrderItem::query()->find((int) $item->id) : null;
    $result['fixture_visible'] = $order !== null && $admin !== null && $product !== null && $orderItem !== null;
    file_put_contents($readyFile, 'ready', LOCK_EX);

    $deadline = microtime(true) + 15;
    while (! file_exists($startFile) && microtime(true) < $deadline) {
        usleep(10000);
    }
    $result['barrier_passed'] = file_exists($startFile);
    if (! $result['fixture_visible'] || ! $result['barrier_passed']) {
        throw new RuntimeException('Worker barrier or fixture visibility failed.');
    }

    $transition = app(OrderStatusTransitionService::class)->transition(Order::findOrFail((int) $orderId), User::findOrFail((int) $adminId), [
        'status' => 'cancelled',
        'version' => $version,
        'reason' => 'Concurrent worker',
    ]);
    $result['outcome'] = ($transition['changed'] ?? false) ? 'changed' : 'noop';
} catch (Throwable $exception) {
    if ($exception instanceof Illuminate\Validation\ValidationException) {
        $result['outcome'] = 'stale';
    } else {
        $result['exception'] = get_class($exception);
    }
}

file_put_contents($resultFile, json_encode($result, JSON_THROW_ON_ERROR), LOCK_EX);
