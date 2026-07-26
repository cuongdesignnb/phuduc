<?php

namespace App\Services\Admin\Operations;

use App\Models\Review;
use App\Models\ReviewModerationHistory;
use App\Models\User;
use App\Services\Admin\AdminConcurrencyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewModerationService
{
    private const TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'approved' => ['rejected'],
        'rejected' => ['approved'],
    ];

    public function __construct(private readonly AdminConcurrencyService $concurrency) {}

    public function updateStatus(Review $review, User $actor, array $data): Review
    {
        return DB::transaction(function () use ($review, $actor, $data): Review {
            $locked = Review::query()->lockForUpdate()->findOrFail($review->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Đánh giá đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            $from = (string) $locked->status;
            $to = (string) $data['status'];
            if ($from === $to) {
                return $locked->refresh();
            }
            if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Không thể chuyển trạng thái đánh giá theo quy trình hiện tại.']);
            }
            $locked->update(['status' => $to]);
            ReviewModerationHistory::create([
                'review_id' => $locked->id,
                'review_reference' => (string) $locked->id,
                'actor_id' => $actor->id,
                'action' => $to,
                'from_status' => $from,
                'to_status' => $to,
            ]);

            return $locked->refresh();
        });
    }

    public function delete(Review $review, User $actor, array $data): void
    {
        DB::transaction(function () use ($review, $actor, $data): void {
            $locked = Review::query()->lockForUpdate()->findOrFail($review->id);
            $this->concurrency->assertVersion($data['version'] ?? null, $locked, 'Đánh giá đã được cập nhật ở phiên khác. Vui lòng tải lại.');
            if ($locked->status === 'approved') {
                throw ValidationException::withMessages(['review' => 'Cần từ chối đánh giá trước khi xóa.']);
            }
            if (! in_array($locked->status, ['pending', 'rejected'], true)) {
                throw ValidationException::withMessages(['review' => 'Đánh giá hiện tại không thể xóa.']);
            }
            ReviewModerationHistory::create([
                'review_id' => $locked->id,
                'review_reference' => (string) $locked->id,
                'actor_id' => $actor->id,
                'action' => 'deleted',
                'from_status' => $locked->status,
                'to_status' => null,
                'reason' => $data['reason'] ?? null,
            ]);
            $locked->delete();
        });
    }
}
