<!-- resources/js/Pages/BarimaxAds/Index.vue -->
<template>
    <DashboardLayout title="AI Discounts">
        <!-- Simple Header -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <Sparkles :size="20" />
                    <span class="font-semibold">AI Discounts</span>
                </div>
                <span class="text-sm opacity-90">{{ adStats.active_ads }} active offers</span>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 py-8">
            <!-- Latest Product -->
            <div v-if="latestProduct" class="mb-8">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="md:flex">
                        <!-- Product Image -->
                        <div class="md:w-1/3 bg-gray-100 p-6 flex items-center justify-center">
                            <img 
                                :src="latestProduct.image_url" 
                                :alt="latestProduct.name"
                                class="max-h-48 object-contain"
                            />
                        </div>
                        
                        <!-- Product Info -->
                        <div class="md:w-2/3 p-6">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="text-xs font-semibold text-purple-600 uppercase tracking-wide">
                                        Latest Product
                                    </span>
                                    <h2 class="text-2xl font-bold text-gray-900 mt-1">
                                        {{ latestProduct.name }}
                                    </h2>
                                </div>
                                <span class="text-2xl font-bold text-green-600">
                                    {{ latestProduct.formatted_price }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mt-3 line-clamp-2">
                                {{ latestProduct.description }}
                            </p>
                            
                            <div class="flex items-center space-x-4 mt-4">
                                <span :class="latestProduct.in_stock ? 'text-green-600' : 'text-red-600'" 
                                      class="text-sm font-medium">
                                    {{ latestProduct.in_stock ? '✓ In Stock' : '✗ Out of Stock' }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ latestProduct.category }}
                                </span>
                            </div>
                            
                            <!-- Download Button -->
                            <a 
                                :href="latestProduct.download_url"
                                class="inline-flex items-center space-x-2 mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                            >
                                <Download :size="16" />
                                <span>Download Image</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Ad -->
            <div v-if="featuredAd" class="mb-8">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-semibold uppercase tracking-wide bg-white/20 px-3 py-1 rounded-full">
                                Featured Offer
                            </span>
                            <span class="text-sm">{{ featuredAd.days_remaining }} days left</span>
                        </div>
                        
                        <div class="text-4xl font-bold mb-2">
                            {{ featuredAd.discount_percentage }}% OFF
                        </div>
                        
                        <p class="text-lg mb-4">{{ featuredAd.caption }}</p>
                        
                        <div class="bg-black/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm opacity-90">Discount Code</span>
                                    <div class="text-xl font-mono font-bold">{{ featuredAd.discount_code }}</div>
                                </div>
                                <button 
                                    @click="copyToClipboard(featuredAd.discount_code)"
                                    class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
                                >
                                    <Copy :size="16" />
                                    <span>Copy</span>
                                </button>
                            </div>
                        </div>
                        
                        <button
                            @click="claimDiscount(featuredAd.id)"
                            :disabled="claiming"
                            class="w-full bg-white text-purple-600 hover:bg-gray-100 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold py-3 rounded-lg transition-colors flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="claiming" :size="18" class="animate-spin" />
                            <template v-else>
                                <Gift :size="18" />
                                <span>Claim Discount</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Offers Grid -->
            <div v-if="activeAds.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="ad in activeAds" 
                    :key="ad.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold text-purple-600 uppercase tracking-wide">
                                {{ ad.discount_percentage }}% OFF
                            </span>
                            <span class="text-xs text-gray-500">{{ ad.days_remaining }}d left</span>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-2">{{ ad.caption }}</h3>
                        
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ ad.description }}</p>
                        
                        <div class="flex items-center justify-between">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ ad.discount_code }}</code>
                            <button
                                @click="claimDiscount(ad.id)"
                                class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center space-x-1"
                            >
                                <Gift :size="14" />
                                <span>Claim</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <Gift :size="48" class="mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Active Offers</h3>
                <p class="text-gray-600">Check back at midnight for new AI-generated discounts!</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Sparkles, Gift, Copy, Download,
    Loader2
} from 'lucide-vue-next';

const props = defineProps({
    featuredAd: Object,
    activeAds: Array,
    latestProduct: Object,
    adStats: Object,
    user: Object,
});

const claiming = ref(false);

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Code copied!');
    } catch (err) {
        console.error('Failed to copy:', err);
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
            onError: () => {
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