<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with('product:id,name')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Review/Index', [
            'reviews' => $reviews,
            'filters' => $request->only('status'),
        ]);
    }

    public function updateStatus(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review->update(['status' => $request->status]);

        return back()->with('success', 'Trạng thái đánh giá đã được cập nhật.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Đánh giá đã được xóa.');
    }
}
