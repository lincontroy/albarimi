<template>
    <DashboardLayout title="My Review">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Star :size="16" class="text-purple-400" />
            <span>Reviews</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">My Review</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-white">My Review</h1>
                    <Link 
                        href="/reviews"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50"
                    >
                        Back to All Reviews
                    </Link>
                </div>

                <!-- Edit Review Form -->
                <div v-if="userReview" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white">Edit Your Review</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-blue-400 bg-blue-500/20 px-3 py-1 rounded-full">
                                Created: {{ userReview.created_at }}
                            </span>
                        </div>
                    </div>
                    
                    <form @submit.prevent="updateReview" class="space-y-4">
                        <div>
                            <label class="block text-sm text-blue-300 mb-2">Your Rating</label>
                            <div class="flex space-x-1">
                                <button 
                                    v-for="n in 5" 
                                    :key="n"
                                    type="button"
                                    @click="editingReview.rating = n"
                                    class="text-2xl transition-transform hover:scale-110"
                                    :class="n <= editingReview.rating ? 'text-yellow-400' : 'text-gray-400'"
                                >
                                    {{ n <= editingReview.rating ? '★' : '☆' }}
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm text-blue-300 mb-2">Review Title (Optional)</label>
                            <input 
                                v-model="editingReview.title"
                                type="text" 
                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm text-blue-300 mb-2">Your Review</label>
                            <textarea 
                                v-model="editingReview.comment"
                                required
                                rows="6"
                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                            ></textarea>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="deleteReview"
                                class="bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-red-500/50"
                            >
                                Delete Review
                            </button>
                            <button
                                type="submit"
                                :disabled="updating"
                                class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50"
                            >
                                <Loader2 v-if="updating" :size="20" class="animate-spin" />
                                <span v-else>Update Review</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- No Review Yet -->
                <div v-else class="text-center py-12">
                    <MessageSquare :size="64" class="mx-auto text-blue-400 mb-4 opacity-50" />
                    <p class="text-blue-300 text-lg mb-2">You haven't written a review yet</p>
                    <p class="text-blue-400 text-sm mb-6">Share your experience with our platform</p>
                    <Link 
                        href="/reviews"
                        class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 inline-flex items-center space-x-2"
                    >
                        <Star :size="18" />
                        <span>Write Your Review</span>
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Star, MessageSquare, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    userReview: Object,
});

const updating = ref(false);
const editingReview = ref(props.userReview ? { ...props.userReview } : null);

const updateReview = async () => {
    if (!editingReview.value) return;

    updating.value = true;

    try {
        await router.put(`/reviews/${editingReview.value.id}`, editingReview.value, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                } else if (flash.error) {
                    alert(flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error updating review:', errors);
                alert('Failed to update review. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error updating review:', error);
        alert('An unexpected error occurred.');
    } finally {
        updating.value = false;
    }
};

const deleteReview = async () => {
    if (!confirm('Are you sure you want to delete your review? This cannot be undone.')) return;

    try {
        await router.delete(`/reviews/${props.userReview.id}`, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.visit('/reviews');
                }
            },
            onError: (errors) => {
                console.error('Error deleting review:', errors);
                alert('Failed to delete review.');
            }
        });
    } catch (error) {
        console.error('Error deleting review:', error);
        alert('An unexpected error occurred.');
    }
};
</script>