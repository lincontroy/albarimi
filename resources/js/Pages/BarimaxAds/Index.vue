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
                <span class="text-sm opacity-90">{{ adStats?.active_ads || 0 }} active offers</span>
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
                                @error="handleImageError"
                            />
                        </div>
                        
                        <!-- Product Info -->
                        <div class="md:w-2/3 p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
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
                            
                            <div class="flex flex-wrap items-center gap-4 mt-4">
                                <span :class="latestProduct.in_stock ? 'text-green-600' : 'text-red-600'" 
                                      class="text-sm font-medium">
                                    {{ latestProduct.in_stock ? '✓ In Stock' : '✗ Out of Stock' }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ latestProduct.category }}
                                </span>
                                <span v-if="latestProduct.stock" class="text-sm text-gray-500">
                                    Stock: {{ latestProduct.stock }}
                                </span>
                            </div>
                            
                            <!-- Download Button - FIXED -->
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button
                                    @click="downloadImage(latestProduct)"
                                    :disabled="downloading"
                                    class="inline-flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:bg-purple-300 disabled:cursor-not-allowed"
                                >
                                    <Loader2 v-if="downloading" :size="16" class="animate-spin" />
                                    <Download v-else :size="16" />
                                    <span>{{ downloading ? 'Downloading...' : 'Download Image' }}</span>
                                </button>
                                
                                <button
                                    @click="copyImageUrl(latestProduct.image_url)"
                                    class="inline-flex items-center space-x-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
                                >
                                    <Copy :size="16" />
                                    <span>Copy URL</span>
                                </button>
                            </div>
                            
                            <!-- Image filename display -->
                            <div class="mt-2 text-xs text-gray-400 truncate">
                                File: {{ getFilenameFromUrl(latestProduct.image_url) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <Package :size="48" class="mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Products Available</h3>
                <p class="text-gray-600">Check back later for new products!</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Sparkles, Gift, Copy, Download, Package,
    Loader2
} from 'lucide-vue-next';

const props = defineProps({
    featuredAd: {
        type: Object,
        default: null
    },
    activeAds: {
        type: Array,
        default: () => []
    },
    latestProduct: {
        type: Object,
        default: null
    },
    adStats: {
        type: Object,
        default: () => ({})
    },
    user: {
        type: Object,
        default: null
    },
});

const claiming = ref(false);
const downloading = ref(false);

// Helper function to get filename from URL
const getFilenameFromUrl = (url) => {
    if (!url) return 'image.jpg';
    return url.split('/').pop() || 'image.jpg';
};

// Generate download filename based on product
const getDownloadFilename = (product) => {
    if (!product) return 'image.jpg';
    
    // Create a clean filename from product name
    const cleanName = product.name
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    
    // Get extension from URL or default to jpg
    const extension = product.image_url?.split('.').pop() || 'jpg';
    
    return `${cleanName}-${product.id}.${extension}`;
};

// Download image function
const downloadImage = async (product) => {
    if (!product?.download_url) {
        alert('Download URL not available');
        return;
    }

    downloading.value = true;

    try {
        // Create a hidden anchor element
        const link = document.createElement('a');
        link.href = product.download_url;
        link.download = getDownloadFilename(product);
        link.target = '_blank'; // Open in new tab to avoid navigation
        link.rel = 'noopener noreferrer';
        
        // Append to body, click, and remove
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Small delay to show downloading state
        setTimeout(() => {
            downloading.value = false;
        }, 1000);
        
    } catch (error) {
        console.error('Download error:', error);
        alert('Failed to download image. Please try again.');
        downloading.value = false;
    }
};

// Alternative download method using fetch (more reliable but requires CORS)
const downloadImageFetch = async (product) => {
    if (!product?.download_url) {
        alert('Download URL not available');
        return;
    }

    downloading.value = true;

    try {
        const response = await fetch(product.download_url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Download failed');
        }

        // Get the image as blob
        const blob = await response.blob();
        
        // Create download link
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = getDownloadFilename(product);
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        setTimeout(() => {
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            downloading.value = false;
        }, 100);
        
    } catch (error) {
        console.error('Download error:', error);
        alert('Failed to download image. Please try again.');
        downloading.value = false;
        
        // Fallback: direct navigation to download URL
        window.open(product.download_url, '_blank');
    }
};

// Handle image load error
const handleImageError = (e) => {
    e.target.src = 'https://via.placeholder.com/300x200?text=Image+Not+Found';
};

// Copy image URL to clipboard
const copyImageUrl = async (url) => {
    try {
        await navigator.clipboard.writeText(url);
        alert('Image URL copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy URL:', err);
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Image URL copied to clipboard!');
    }
};

// Copy discount code to clipboard
const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Code copied!');
    } catch (err) {
        console.error('Failed to copy:', err);
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Code copied!');
    }
};

// Claim discount
const claimDiscount = async (adId) => {
    if (!props.user) {
        alert('Please login to claim this discount.');
        router.visit('/login');
        return;
    }

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

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .md\:w-1\3, .md\:w-2\3 {
        width: 100%;
    }
    
    .bg-gray-100.p-6 {
        padding: 1rem;
    }
}

/* Loading animation */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>