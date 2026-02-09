<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Review;

class ReviewController extends Controller
{
    // Display all reviews (public page)
    public function index()
    {
        // Get featured reviews
        $featuredReviews = Review::with('user')
            ->published()
            ->featured()
            ->recent(10)
            ->get()
            ->map(function ($review) {
                return $this->formatReview($review);
            });

        // Get latest reviews
        $latestReviews = Review::with('user')
            ->published()
            ->recent(20)
            ->get()
            ->map(function ($review) {
                return $this->formatReview($review);
            });

        // Get overall stats
        $overallStats = [
            'total_reviews' => Review::published()->count(),
            'average_rating' => round(Review::published()->avg('rating') ?? 0, 1),
            'total_reviewers' => Review::published()->distinct('user_id')->count(),
            'featured_reviews' => Review::featured()->published()->count(),
        ];

        // Get rating distribution
        $ratingDistribution = Review::getRatingDistribution();

        return Inertia::render('Reviews/Index', [
            'featuredReviews' => $featuredReviews,
            'latestReviews' => $latestReviews,
            'overallStats' => $overallStats,
            'ratingDistribution' => $ratingDistribution,
            'user' => Auth::user() ? [
                'id' => Auth::id(),
                'name' => Auth::user()->name,
                'has_reviewed' => Review::where('user_id', Auth::id())->exists(),
            ] : null,
        ]);
    }

    // Store a new review
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user has already reviewed (limit to one per user)
        $existingReview = Review::where('user_id', $user->id)->first();

        if ($existingReview) {
            return back()->with([
                'flash' => [
                    'error' => 'You have already submitted a review. You can update your existing review.'
                ]
            ]);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'comment' => 'required|string',
        ]);

        try {
            $review = Review::create([
                'user_id' => $user->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'],
                'comment' => $validated['comment'],
                'status' => 'published', // Auto-publish
            ]);

            return back()->with([
                'flash' => [
                    'success' => 'Thank you for your review!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Review submission failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to submit review. Please try again.'
                ]
            ]);
        }
    }

    // Show user's own reviews
    public function myReviews()
    {
        $user = Auth::user();

        // User's review
        $userReview = Review::with('user')
            ->where('user_id', $user->id)
            ->first();

        return Inertia::render('Reviews/MyReviews', [
            'userReview' => $userReview ? $this->formatReview($userReview) : null,
        ]);
    }

    // Update a review
    public function update(Request $request, Review $review)
    {
        $user = Auth::user();

        // Check if user owns this review
        if ($review->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'comment' => 'sometimes|string',
        ]);

        try {
            $review->update($validated);

            return back()->with([
                'flash' => [
                    'success' => 'Review updated successfully!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Review update failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to update review. Please try again.'
                ]
            ]);
        }
    }

    // Delete a review
    public function destroy(Review $review)
    {
        $user = Auth::user();

        // Check if user owns this review
        if ($review->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $review->delete();

            return back()->with([
                'flash' => [
                    'success' => 'Review deleted successfully!'
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Review deletion failed: ' . $e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Failed to delete review. Please try again.'
                ]
            ]);
        }
    }

    // Format review for response
    private function formatReview($review)
    {
        return [
            'id' => $review->id,
            'user' => [
                'id' => $review->user->id,
                'name' => $review->display_name,
                'avatar' => $review->user->profile_photo_url,
            ],
            'rating' => $review->rating,
            'stars' => $review->stars,
            'title' => $review->title,
            'comment' => $review->comment,
            'is_featured' => $review->is_featured,
            'created_at' => $review->created_at->format('M d, Y H:i'),
            'time_ago' => $review->time_ago,
        ];
    }

    // Get reviews for dashboard
    public function getDashboardReviews()
    {
        $reviews = Review::with('user')
            ->published()
            ->recent(10)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'user' => $review->display_name,
                    'rating' => $review->rating,
                    'stars' => $review->stars,
                    'comment' => $review->comment,
                    'date' => $review->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $review->time_ago,
                ];
            });

        return response()->json($reviews);
    }
}