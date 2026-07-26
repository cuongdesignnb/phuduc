<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteReviewRequest;
use App\Http\Requests\Admin\ReviewIndexRequest;
use App\Http\Requests\Admin\UpdateReviewStatusRequest;
use App\Models\Review;
use App\Services\Admin\Operations\AdminReviewService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(ReviewIndexRequest $request, AdminReviewService $reviews): Response
    {
        return Inertia::render('Admin/Review/Index', $reviews->index($request->user(), $request->validated()));
    }

    public function updateStatus(UpdateReviewStatusRequest $request, Review $review, AdminReviewService $reviews): RedirectResponse
    {
        $reviews->updateStatus($review, $request->user(), $request->validated());

        return back()->with('success', 'Trạng thái đánh giá đã được cập nhật.');
    }

    public function destroy(DeleteReviewRequest $request, Review $review, AdminReviewService $reviews): RedirectResponse
    {
        $reviews->delete($review, $request->user(), $request->validated());

        return back()->with('success', 'Đánh giá đã được xóa.');
    }
}
