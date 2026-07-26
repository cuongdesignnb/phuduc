<?php

namespace App\Services\Admin\Operations;

use App\Models\Warranty;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Support\Facades\DB;

class WarrantyStatusMutationService
{
    public function __construct(private readonly AdminConcurrencyService $concurrency) {}

    public function void(Warranty $warranty, array $data): Warranty
    {
        return DB::transaction(function () use ($warranty, $data): Warranty {
            $locked = Warranty::query()->lockForUpdate()->findOrFail($warranty->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Bảo hành đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            if ($locked->status !== 'voided') {
                $locked->update(['status' => 'voided', 'void_reason' => $data['reason']]);
            }

            return $locked->refresh();
        });
    }
}
