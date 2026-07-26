<?php

use App\Models\Order;
use App\Models\User;
use App\Services\Admin\Operations\OrderStatusTransitionService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$orderId, $adminId, $version, $readyFile, $resultFile] = array_pad(array_slice($argv, 1), 5, null);
file_put_contents($readyFile, 'ready');

try {
    app(OrderStatusTransitionService::class)->transition(Order::findOrFail((int) $orderId), User::findOrFail((int) $adminId), [
        'status' => 'cancelled',
        'version' => $version,
        'reason' => 'Concurrent worker',
    ]);
    file_put_contents($resultFile, 'success');
} catch (Throwable $exception) {
    file_put_contents($resultFile, get_class($exception));
}
