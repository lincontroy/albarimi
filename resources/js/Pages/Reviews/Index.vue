<template>
    <DashboardLayout title="Customer Reviews">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Star :size="16" class="text-purple-400" />
            <span>Reviews</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">All Reviews</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="text-center">
                            <p class="text-purple-300 text-sm mb-1">Total Reviews</p>
                            <p class="text-3xl font-bold text-white">{{ overallStats.total_reviews }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-600/40 to-amber-600/40 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="text-center">
                            <p class="text-yellow-300 text-sm mb-1">Average Rating</p>
                            <p class="text-3xl font-bold text-white">{{ overallStats.average_rating }}</p>
                            <div class="flex justify-center mt-1">
                                <span v-for="n in 5" :key="n" class="text-yellow-400">
                                    {{ n <= Math.round(overallStats.average_rating) ? '★' : '☆' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="text-center">
                            <p class="text-blue-300 text-sm mb-1">Users Reviewed</p>
                            <p class="text-3xl font-bold text-white">{{ overallStats.total_reviewers }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <p class="text-green-300 text-sm mb-1">Featured Reviews</p>
                            <p class="text-3xl font-bold text-white">{{ overallStats.featured_reviews }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Reviews -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Submit Review (if logged in and hasn't reviewed) -->
                        <div v-if="user && !user.has_reviewed" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Write a Review</h3>
                            <form @submit.prevent="submitReview" class="space-y-4">
                                <div>
                                    <label class="block text-sm text-purple-300 mb-2">Your Rating</label>
                                    <div class="flex space-x-1">
                                        <button 
                                            v-for="n in 5" 
                                            :key="n"
                                            type="button"
                                            @click="newReview.rating = n"
                                            class="text-2xl transition-transform hover:scale-110"
                                            :class="n <= newReview.rating ? 'text-yellow-400' : 'text-gray-400'"
                                        >
                                            {{ n <= newReview.rating ? '★' : '☆' }}
                                        </button>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-purple-300 mb-2">Review Title (Optional)</label>
                                    <input 
                                        v-model="newReview.title"
                                        type="text" 
                                        placeholder="Brief title for your review"
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-purple-300 mb-2">Your Review</label>
                                    <textarea 
                                        v-model="newReview.comment"
                                        required
                                        rows="4"
                                        placeholder="Share your experience with our platform..."
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400"
                                    ></textarea>
                                </div>
                                
                                <button
                                    type="submit"
                                    :disabled="submitting"
                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                                >
                                    <Loader2 v-if="submitting" :size="20" class="animate-spin" />
                                    <span v-else>Submit Review</span>
                                </button>
                            </form>
                        </div>

                        <!-- Already reviewed message -->
                        <div v-else-if="user && user.has_reviewed" class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <div class="flex items-center space-x-3">
                                <CheckCircle :size="24" class="text-green-400" />
                                <div>
                                    <h3 class="text-lg font-bold text-white">Thank you for your review!</h3>
                                    <p class="text-green-300 text-sm">You can update your review at any time.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link 
                                    href="/reviews/my-reviews"
                                    class="inline-flex items-center space-x-2 bg-black/30 hover:bg-black/50 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    <Edit :size="16" />
                                    <span>View/Edit My Review</span>
                                </Link>
                            </div>
                        </div>

                        <!-- Not logged in message -->
                        <div v-else class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <div class="flex items-center space-x-3">
                                <LogIn :size="24" class="text-blue-400" />
                                <div>
                                    <h3 class="text-lg font-bold text-white">Sign in to write a review</h3>
                                    <p class="text-blue-300 text-sm">Log in to share your experience</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link 
                                    href="/login"
                                    class="inline-flex items-center space-x-2 bg-black/30 hover:bg-black/50 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    <LogIn :size="16" />
                                    <span>Sign In</span>
                                </Link>
                            </div>
                        </div>

                        <!-- Featured Reviews -->
                        <div v-if="featuredReviews.length > 0" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-white">Featured Reviews</h3>
                                <span class="text-sm text-yellow-400 bg-yellow-500/20 px-3 py-1 rounded-full">
                                    {{ featuredReviews.length }} Featured
                                </span>
                            </div>
                            
                            <div class="space-y-4">
                                <div 
                                    v-for="review in featuredReviews" 
                                    :key="review.id"
                                    class="bg-black/30 border border-yellow-500/20 rounded-xl p-4 hover:border-yellow-500/40 transition-colors"
                                >
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-600 to-amber-600 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">{{ review.user.name.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold">{{ review.user.name }}</p>
                                                <div class="flex text-yellow-400 mt-1">
                                                    <span v-for="n in 5" :key="n">{{ n <= review.rating ? '★' : '☆' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="review.is_featured" class="text-xs text-yellow-400 bg-yellow-500/20 px-2 py-1 rounded-full">
                                            Featured
                                        </span>
                                    </div>
                                    
                                    <h4 v-if="review.title" class="text-white font-semibold mb-2">{{ review.title }}</h4>
                                    <p class="text-gray-300 text-sm leading-relaxed">{{ review.comment }}</p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-yellow-500/20">
                                        <div class="flex items-center space-x-2 text-xs">
                                            <Calendar :size="12" class="text-yellow-400" />
                                            <span class="text-yellow-300">{{ review.created_at }}</span>
                                        </div>
                                        <span class="text-xs text-yellow-400">{{ review.time_ago }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Latest Reviews -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-bold text-white">Latest Reviews</h3>
                                <Link 
                                    v-if="user"
                                    href="/reviews/my-reviews"
                                    class="text-sm text-blue-400 hover:text-blue-300 transition-colors"
                                >
                                    View My Review →
                                </Link>
                            </div>
                            
                            <div class="space-y-4">
                                <div 
                                    v-for="review in latestReviews" 
                                    :key="review.id"
                                    class="bg-black/30 border border-blue-500/20 rounded-xl p-4 hover:border-blue-500/40 transition-colors"
                                >
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">{{ review.user.name.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold">{{ review.user.name }}</p>
                                                <div class="flex text-yellow-400 mt-1">
                                                    <span v-for="n in 5" :key="n">{{ n <= review.rating ? '★' : '☆' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h4 v-if="review.title" class="text-white font-semibold mb-2">{{ review.title }}</h4>
                                    <p class="text-gray-300 text-sm leading-relaxed">{{ review.comment }}</p>
                                    
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-blue-500/20">
                                        <div class="flex items-center space-x-2 text-xs">
                                            <Calendar :size="12" class="text-blue-400" />
                                            <span class="text-blue-300">{{ review.created_at }}</span>
                                        </div>
                                        <span class="text-xs text-blue-400">{{ review.time_ago }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Stats -->
                    <div class="space-y-6">
                        <!-- Rating Distribution -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Rating Distribution</h3>
                            <div class="space-y-3">
                                <div 
                                    v-for="n in 5" 
                                    :key="n"
                                    class="flex items-center justify-between"
                                >
                                    <div class="flex items-center space-x-2">
                                        <div class="flex text-yellow-400">
                                            <span v-for="i in 5" :key="i">{{ i <= (6-n) ? '★' : '☆' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-white font-semibold">{{ ratingDistribution[6-n] || 0 }}</p>
                                        <p class="text-green-400 text-xs">
                                            {{ overallStats.total_reviews > 0 ? 
                                               Math.round(((ratingDistribution[6-n] || 0) / overallStats.total_reviews) * 100) : 0 }}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Review Guidelines -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                                <Info :size="20" class="text-yellow-400" />
                                <span>Review Guidelines</span>
                            </h3>
                            <ul class="space-y-2 text-sm">
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-300">Share your honest experience</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-300">Be specific and constructive</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-300">Respect others in your comments</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-300">One review per user</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Quick Stats -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-cyan-500/30 rounded-2xl p-6 shadow-2xl shadow-cyan-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Quick Stats</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-cyan-300">5-Star Reviews</span>
                                    <span class="text-white font-semibold">{{ ratingDistribution[5] || 0 }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-cyan-300">Latest Review</span>
                                    <span class="text-white font-semibold text-xs">{{ latestReviewTime }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-cyan-300">Your Review</span>
                                    <span v-if="user && user.has_reviewed" class="text-green-400 font-semibold text-xs">Submitted ✓</span>
                                    <span v-else-if="user" class="text-yellow-400 font-semibold text-xs">Not yet</span>
                                    <span v-else class="text-gray-400 font-semibold text-xs">Sign in</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Star, Calendar, CheckCircle, Info, 
    Loader2, Edit, LogIn
} from 'lucide-vue-next';

const props = defineProps({
    featuredReviews: Array,
    latestReviews: Array,
    overallStats: Object,
    ratingDistribution: Object,
    user: Object,
});

const submitting = ref(false);
const newReview = ref({
    rating: 5,
    title: '',
    comment: '',
});

const latestReviewTime = computed(() => {
    if (props.latestReviews.length === 0) return 'None';
    const latest = props.latestReviews[0];
    return latest.time_ago;
});

const submitReview = async () => {
    if (!newReview.value.comment) {
        alert('Please write your review');
        return;
    }

    submitting.value = true;

    try {
        await router.post('/reviews', newReview.value, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    newReview.value = {
                        rating: 5,
                        title: '',
                        comment: '',
                    };
                    router.reload();
                } else if (flash.error) {
                    alert(flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error submitting review:', errors);
                alert('Failed to submit review. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error submitting review:', error);
        alert('An unexpected error occurred.');
    } finally {
        submitting.value = false;
    }
};
</script>