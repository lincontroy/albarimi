<template>
    <DashboardLayout title="Barimax AI Discounts">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Sparkles :size="16" class="text-purple-400" />
            <span>Barimax</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">AI Discounts</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="text-center">
                            <p class="text-purple-300 text-sm mb-1">Active Offers</p>
                            <p class="text-3xl font-bold text-white">{{ adStats.active_ads }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-600/40 to-amber-600/40 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="text-center">
                            <p class="text-yellow-300 text-sm mb-1">Total Views</p>
                            <p class="text-3xl font-bold text-white">{{ adStats.total_views.toLocaleString() }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="text-center">
                            <p class="text-blue-300 text-sm mb-1">Total Clicks</p>
                            <p class="text-3xl font-bold text-white">{{ adStats.total_clicks.toLocaleString() }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <p class="text-green-300 text-sm mb-1">Next Update</p>
                            <p class="text-3xl font-bold text-white">Midnight</p>
                        </div>
                    </div>
                </div>

                <!-- Featured Ad -->
                <div v-if="featuredAd" class="mb-8">
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <!-- Background Image -->
                        <div class="absolute inset-0">
                            <img 
                                :src="featuredAd.hd_image_url" 
                                :alt="featuredAd.caption"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                            <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-black/40"></div>
                        </div>
                        
                        <!-- Content -->
                        <div class="relative p-8 lg:p-12">
                            <div class="max-w-2xl">
                                <!-- Badge -->
                                <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-yellow-600 to-amber-600 text-white px-4 py-2 rounded-full mb-4">
                                    <Sparkles :size="16" />
                                    <span class="font-bold">AI-GENERATED • LIMITED TIME</span>
                                </div>
                                
                                <!-- Discount -->
                                <div class="mb-4">
                                    <div class="text-6xl lg:text-7xl font-bold text-white mb-2">
                                        {{ featuredAd.discount_percentage }}% OFF
                                    </div>
                                    <div class="text-xl text-yellow-300 font-semibold">
                                        {{ featuredAd.caption }}
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                <p class="text-gray-200 text-lg mb-6">
                                    Exclusive AI-generated discount offer. Valid for 3 days only.
                                </p>
                                
                                <!-- Code & Timer -->
                                <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4 mb-6">
                                    <div class="bg-black/50 border-2 border-dashed border-yellow-500/50 rounded-xl p-4">
                                        <p class="text-gray-300 text-sm mb-1">Your Discount Code</p>
                                        <div class="flex items-center space-x-3">
                                            <code class="text-2xl font-bold text-yellow-400 tracking-wider">
                                                {{ featuredAd.discount_code }}
                                            </code>
                                            <button 
                                                @click="copyToClipboard(featuredAd.discount_code)"
                                                class="text-yellow-400 hover:text-yellow-300"
                                                title="Copy code"
                                            >
                                                <Copy :size="20" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-black/50 border border-red-500/30 rounded-xl p-4">
                                        <p class="text-gray-300 text-sm mb-1">Expires in</p>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-white">{{ featuredAd.days_remaining }}</div>
                                                <div class="text-xs text-gray-300">Days</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-2xl font-bold text-white">{{ featuredAd.hours_remaining % 24 }}</div>
                                                <div class="text-xs text-gray-300">Hours</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- CTA Button -->
                                <button
                                    @click="claimDiscount(featuredAd.id)"
                                    :disabled="claiming"
                                    class="bg-gradient-to-r from-yellow-600 to-amber-600 hover:from-yellow-500 hover:to-amber-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold text-lg py-4 px-8 rounded-xl transition-all duration-200 shadow-2xl shadow-yellow-500/50 flex items-center justify-center space-x-3 group"
                                >
                                    <Loader2 v-if="claiming" :size="24" class="animate-spin" />
                                    <template v-else>
                                        <Gift :size="24" class="group-hover:scale-110 transition-transform" />
                                        <span class="text-xl">CLAIM {{ featuredAd.discount_percentage }}% DISCOUNT</span>
                                    </template>
                                </button>
                                
                                <!-- Note -->
                                <p class="text-gray-400 text-sm mt-4">
                                    ⚡ New AI-generated offer every midnight
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Ads Grid -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-white">Active AI Offers</h3>
                        <div class="text-sm text-purple-400 bg-purple-500/20 px-3 py-1 rounded-full">
                            {{ activeAds.length }} Offers
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="ad in activeAds" 
                            :key="ad.id"
                            class="group relative overflow-hidden rounded-2xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1"
                        >
                            <!-- Background Image -->
                            <div class="absolute inset-0">
                                <img 
                                    :src="ad.image_url" 
                                    :alt="ad.caption"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                />
                                <div :class="ad.gradient_colors" class="absolute inset-0 opacity-90"></div>
                            </div>
                            
                            <!-- Content -->
                            <div class="relative p-6 h-64 flex flex-col justify-between">
                                <!-- Top Section -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-xs font-bold text-white bg-black/30 px-3 py-1 rounded-full">
                                            {{ ad.category_name }}
                                        </span>
                                        <span class="text-xs text-white bg-red-500/30 px-3 py-1 rounded-full">
                                            {{ ad.days_remaining }}d left
                                        </span>
                                    </div>
                                    
                                    <h4 class="text-xl font-bold text-white mb-2 line-clamp-2">
                                        {{ ad.caption }}
                                    </h4>
                                    
                                    <p class="text-gray-200 text-sm line-clamp-2">
                                        {{ ad.description }}
                                    </p>
                                </div>
                                
                                <!-- Bottom Section -->
                                <div>
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <div class="text-3xl font-bold text-white">
                                                {{ ad.discount_percentage }}% OFF
                                            </div>
                                            <div class="text-xs text-gray-300">Use code: {{ ad.discount_code }}</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-white">AI-Generated</div>
                                            <div class="text-xs text-gray-300">Daily Offer</div>
                                        </div>
                                    </div>
                                    
                                    <button
                                        @click="claimDiscount(ad.id)"
                                        class="w-full bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white font-bold py-3 rounded-xl transition-all duration-200 border border-white/30 hover:border-white/50 flex items-center justify-center space-x-2 group"
                                    >
                                        <Gift :size="16" class="group-hover:scale-110 transition-transform" />
                                        <span>{{ ad.call_to_action }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- How It Works -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-8 shadow-2xl shadow-blue-500/20">
                    <h3 class="text-2xl font-bold text-white mb-6 text-center">How Barimax AI Discounts Work</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Sparkles :size="28" class="text-white" />
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2">AI Generated</h4>
                            <p class="text-gray-300 text-sm">
                                Our AI creates unique discounts with HD images every midnight
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Clock :size="28" class="text-white" />
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2">Limited Time</h4>
                            <p class="text-gray-300 text-sm">
                                Each offer is valid for 3 days only. New offers daily
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Gift :size="28" class="text-white" />
                            </div>
                            <h4 class="text-lg font-bold text-white mb-2">Claim & Save</h4>
                            <p class="text-gray-300 text-sm">
                                Claim your discount code and use it on any purchase
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Next Update Timer -->
                <div class="mt-8 text-center">
                    <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-purple-600/30 to-pink-600/30 border border-purple-500/30 rounded-xl px-6 py-3">
                        <Clock :size="20" class="text-purple-400" />
                        <span class="text-purple-300">Next AI discount update:</span>
                        <span class="text-white font-bold">Midnight (00:00)</span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Sparkles, Gift, Clock, Copy,
    Loader2
} from 'lucide-vue-next';

const props = defineProps({
    featuredAd: Object,
    activeAds: Array,
    adStats: Object,
    categories: Object,
    user: Object,
});

const claiming = ref(false);

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Discount code copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy code to clipboard.');
    }
};

const claimDiscount = async (adId) => {
    claiming.value = true;

    try {
        await router.post(`/barimax-ads/${adId}/claim`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                } else if (flash.error) {
                    alert(flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error claiming discount:', errors);
                alert('Failed to claim discount. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error claiming discount:', error);
        alert('An unexpected error occurred.');
    } finally {
        claiming.value = false;
    }
};
</script>