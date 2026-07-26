<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use App\Services\Admin\Operations\OrderStatusTransitionService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

class OrderCancellationMySqlConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_two_workers_cancel_the_same_committed_order_once(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! function_exists('proc_open')) {
            $this->markTestSkipped('Real concurrent cancellation requires isolated MySQL with a process runner.');
        }

        $token = bin2hex(random_bytes(12));
        $paths = [
            'ready_a' => sys_get_temp_dir()."/pr3c-ready-a-{$token}",
            'ready_b' => sys_get_temp_dir()."/pr3c-ready-b-{$token}",
            'start' => sys_get_temp_dir()."/pr3c-start-{$token}",
            'result_a' => sys_get_temp_dir()."/pr3c-result-a-{$token}",
            'result_b' => sys_get_temp_dir()."/pr3c-result-b-{$token}",
        ];
        foreach ($paths as $path) {
            @unlink($path);
        }

        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Concurrent product', 'slug' => "concurrent-{$token}", 'price' => 100, 'stock' => 0, 'status' => 'active']);
        $order = Order::create(['order_number' => "ORD-CONCURRENT-{$token}", 'customer_name' => 'Khách', 'total_amount' => 100, 'status' => 'pending']);
        $item = OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Concurrent product', 'price' => 100, 'quantity' => 2, 'total' => 200]);
        $version = app(AdminConcurrencyService::class)->version($order);
        $processes = [];

        try {
            foreach ([['a', $paths['ready_a'], $paths['result_a']], ['b', $paths['ready_b'], $paths['result_b']]] as [$worker, $ready, $result]) {
                $command = [PHP_BINARY, base_path('tests/Support/Pr3cConcurrentCancellationWorker.php'), (string) $order->id, (string) $admin->id, $version, $ready, $paths['start'], $result];
                $pipes = [];
                $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                $this->assertIsResource($process, "Worker {$worker} did not start.");
                $processes[$worker] = ['process' => $process, 'pipes' => $pipes];
            }

            $this->waitForFiles([$paths['ready_a'], $paths['ready_b']], $processes, 'ready barrier');
            file_put_contents($paths['start'], 'start', LOCK_EX);
            $this->waitForFiles([$paths['result_a'], $paths['result_b']], $processes, 'result files');
            $results = [json_decode(file_get_contents($paths['result_a']), true, 512, JSON_THROW_ON_ERROR), json_decode(file_get_contents($paths['result_b']), true, 512, JSON_THROW_ON_ERROR)];

            foreach ($results as $index => $result) {
                $this->assertTrue($result['fixture_visible'], 'Worker '.($index + 1).' could not see committed fixtures.');
                $this->assertTrue($result['barrier_passed'], 'Worker '.($index + 1).' did not pass the shared start barrier.');
                $this->assertContains($result['outcome'], ['changed', 'noop', 'stale']);
                $this->assertNull($result['exception']);
            }
            $this->assertSame(1, count(array_filter($results, fn (array $result): bool => $result['outcome'] === 'changed')));
            $this->assertSame('cancelled', $order->refresh()->status);
            $this->assertSame(2, (int) $product->refresh()->stock);
            $this->assertSame(1, $order->statusHistories()->count());

            fwrite(STDOUT, 'Worker A fixture visible='.($results[0]['fixture_visible'] ? 'YES' : 'NO').PHP_EOL);
            fwrite(STDOUT, 'Worker B fixture visible='.($results[1]['fixture_visible'] ? 'YES' : 'NO').PHP_EOL);
            fwrite(STDOUT, 'Worker A barrier passed='.($results[0]['barrier_passed'] ? 'YES' : 'NO').PHP_EOL);
            fwrite(STDOUT, 'Worker B barrier passed='.($results[1]['barrier_passed'] ? 'YES' : 'NO').PHP_EOL);
            fwrite(STDOUT, 'Worker A outcome='.$results[0]['outcome'].PHP_EOL);
            fwrite(STDOUT, 'Worker B outcome='.$results[1]['outcome'].PHP_EOL);
            fwrite(STDOUT, 'Final stock='.$product->stock.PHP_EOL);
            fwrite(STDOUT, 'Final history count='.$order->statusHistories()->count().PHP_EOL);
        } finally {
            $this->closeWorkers($processes);
            foreach ($paths as $path) {
                @unlink($path);
            }
        }
    }

    public function test_cancellation_rolls_back_stock_when_history_insert_fails(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::create(['name' => 'Rollback product', 'slug' => 'rollback-product-'.uniqid(), 'price' => 100, 'stock' => 4, 'status' => 'active']);
        $order = Order::create(['order_number' => 'ORD-ROLLBACK-'.uniqid(), 'customer_name' => 'Khách', 'total_amount' => 100, 'status' => 'pending']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Rollback product', 'price' => 100, 'quantity' => 2, 'total' => 200]);
        $invalidActor = new User;
        $invalidActor->id = 999999999;

        $exception = null;
        try {
            app(OrderStatusTransitionService::class)->transition($order, $invalidActor, [
                'status' => 'cancelled',
                'version' => app(AdminConcurrencyService::class)->version($order),
                'reason' => 'Atomic rollback test',
            ]);
        } catch (Throwable $caught) {
            $exception = $caught;
        }

        $this->assertNotNull($exception, 'The invalid actor must fail the history insert.');
        $this->assertSame('pending', $order->refresh()->status);
        $this->assertSame(4, (int) $product->refresh()->stock);
        $this->assertSame(0, $order->statusHistories()->count());
    }

    private function waitForFiles(array $files, array $processes, string $label): void
    {
        $deadline = microtime(true) + 20;
        while (count(array_filter($files, 'file_exists')) < count($files) && microtime(true) < $deadline) {
            usleep(10000);
        }

        if (count(array_filter($files, 'file_exists')) !== count($files)) {
            $this->fail("Timed out waiting for {$label}. ".$this->workerDiagnostics($processes));
        }
    }

    private function closeWorkers(array $processes): void
    {
        foreach ($processes as $worker) {
            foreach ($worker['pipes'] as $pipe) {
                stream_get_contents($pipe);
                fclose($pipe);
            }
            proc_close($worker['process']);
        }
    }

    private function workerDiagnostics(array $processes): string
    {
        $diagnostics = [];
        foreach ($processes as $name => $worker) {
            $diagnostics[] = "worker {$name}: ".stream_get_contents($worker['pipes'][1]).' '.stream_get_contents($worker['pipes'][2]);
        }

        return implode(' | ', $diagnostics);
    }
}
