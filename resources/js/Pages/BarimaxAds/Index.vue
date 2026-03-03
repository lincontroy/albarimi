<!-- resources/js/Pages/BarimaxAds/Index.vue -->
<template>
    <DashboardLayout title="Product Of The Day">
        <!-- Simple Header -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <Sparkles :size="20" />
                    <span class="font-semibold">Product Of The Day</span>
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