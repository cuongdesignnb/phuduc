<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\StoreReviewRequest;
use App\Services\Storefront\ReviewSubmissionService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, ReviewSubmissionService $reviews): RedirectResponse
    {
        $reviews->store($request->validated());

        return back()->with('success', 'Đánh giá của bạn đã được gửi và đang chờ kiểm duyệt.');
    }
}
