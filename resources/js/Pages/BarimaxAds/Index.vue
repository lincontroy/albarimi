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
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-wrap gap-3 mt-4">
                                <!-- Download Button -->
                                <button
                                    @click="downloadImage(latestProduct)"
                                    :disabled="downloading"
                                    class="inline-flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:bg-purple-300 disabled:cursor-not-allowed"
                                >
                                    <Loader2 v-if="downloading" :size="16" class="animate-spin" />
                                    <Download v-else :size="16" />
                                    <span>{{ downloading ? 'Downloading...' : 'Download Image' }}</span>
                                </button>
                                
                                <!-- Copy URL Button -->
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
    Sparkles, Copy, Download, Package,
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

const downloading = ref(false);
const copying = ref(false);

// Helper Functions
const getFilenameFromUrl = (url) => {
    if (!url) return 'image.jpg';
    return url.split('/').pop() || 'image.jpg';
};

const getDownloadFilename = (product) => {
    if (!product) return 'image.jpg';
    
    const cleanName = product.name
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    
    const extension = product.image_url?.split('.').pop() || 'jpg';
    return `${cleanName}-${product.id}.${extension}`;
};

// Toast notification helper
const showToast = (message, type = 'info') => {
    // You can replace this with your preferred toast library
    alert(message);
};

// FIXED: Download Image Function
const downloadImage = async (product) => {
    if (!product?.image_url) {
        showToast('❌ Image URL not available', 'error');
        return;
    }

    downloading.value = true;

    try {
        const filename = getDownloadFilename(product);
        
        // Method 1: Using fetch and blob (most reliable)
        const response = await fetch(product.image_url, {
            method: 'GET',
            mode: 'cors',
            cache: 'no-cache',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const blob = await response.blob();
        
        // Create download link
        const blobUrl = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = filename;
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        
        // Cleanup
        setTimeout(() => {
            document.body.removeChild(link);
            window.URL.revokeObjectURL(blobUrl);
            downloading.value = false;
        }, 100);
        
        showToast('✅ Download started!', 'success');
        
    } catch (error) {
        console.error('Download error:', error);
        
        // Method 2: Fallback - try with download attribute
        try {
            const link = document.createElement('a');
            link.href = product.image_url;
            link.download = getDownloadFilename(product);
            link.target = '_blank';
            
            document.body.appendChild(link);
            link.click();
            
            setTimeout(() => {
                document.body.removeChild(link);
                downloading.value = false;
            }, 1000);
            
            showToast('✅ Download started!', 'success');
            
        } catch (fallbackError) {
            // Method 3: Last resort - open in new tab
            window.open(product.image_url, '_blank');
            downloading.value = false;
            showToast('ℹ️ Image opened in new tab. Right-click to save.', 'info');
        }
    }
};

// FIXED: Copy Image URL Function
const copyImageUrl = async (url) => {
    if (!url) {
        showToast('❌ No URL to copy', 'error');
        return;
    }
    
    copying.value = true;
    
    try {
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(url);
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = url;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
        
        showToast('✅ Image URL copied to clipboard!', 'success');
        
    } catch (err) {
        console.error('Failed to copy URL:', err);
        showToast('❌ Failed to copy URL. Please try again.', 'error');
    } finally {
        setTimeout(() => {
            copying.value = false;
        }, 500);
    }
};

// Handle image load error
const handleImageError = (e) => {
    e.target.src = 'https://via.placeholder.com/300x200?text=Image+Not+Found';
};

// Claim discount (if needed)
const claimDiscount = async (adId) => {
    if (!props.user) {
        showToast('Please login to claim this discount.', 'info');
        router.visit('/login');
        return;
    }

    downloading.value = true;

    try {
        await router.post(`/barimax-ads/${adId}/claim`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    showToast(flash.success, 'success');
                } else if (flash.error) {
                    showToast(flash.error, 'error');
                }
            },
            onError: () => {
                showToast('Failed to claim discount. Please try again.', 'error');
            }
        });
    } catch (error) {
        console.error('Error claiming discount:', error);
        showToast('An unexpected error occurred.', 'error');
    } finally {
        downloading.value = false;
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

/* Button hover effects */
button {
    transition: all 0.2s ease;
}

button:active {
    transform: scale(0.98);
}
</style>