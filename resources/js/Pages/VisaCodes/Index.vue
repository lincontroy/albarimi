<template>
    <DashboardLayout title="Visa Codes">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Globe :size="16" class="text-blue-400" />
            <span>Visa Codes</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Available Codes</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Loading State -->
                <div v-if="isLoading" class="text-center py-12">
                    <Loader2 class="animate-spin mx-auto text-blue-400" :size="48" />
                    <p class="text-blue-300 mt-4">Loading visa codes...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="hasError" class="text-center py-12">
                    <div class="bg-gradient-to-br from-red-600/20 to-orange-600/20 backdrop-blur-xl border border-red-500/30 rounded-2xl p-8">
                        <AlertCircle :size="48" class="mx-auto text-red-400 mb-4" />
                        <h4 class="text-xl font-bold text-white mb-2">Unable to Load Visa Codes</h4>
                        <p class="text-red-300 mb-4">There was an error loading the visa codes. Please try again.</p>
                        <button @click="loadData" class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-6 rounded-xl transition-all duration-200">
                            Retry
                        </button>
                    </div>
                </div>

                <!-- Success State -->
                <div v-else>
                    <!-- User Balance & Stats -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Balance Card -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <Wallet :size="24" class="text-purple-400" />
                                <h3 class="text-lg font-bold text-white">Your Balance</h3>
                            </div>
                            <p class="text-3xl font-bold text-purple-400 mb-2">KES {{ formatCurrency(userBalance) }}</p>
                            <p class="text-sm text-purple-300">Available for visa code purchases</p>
                        </div>
                        
                        <!-- My Codes Card -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <CreditCard :size="24" class="text-blue-400" />
                                <h3 class="text-lg font-bold text-white">My Visa Codes</h3>
                            </div>
                            <p class="text-3xl font-bold text-white mb-2">{{ myCodes.length }}</p>
                            <Link 
                                href="/visa-codes/my-codes"
                                class="text-sm text-blue-400 hover:text-blue-300 flex items-center space-x-1"
                            >
                                <span>View all</span>
                                <ArrowRight :size="14" />
                            </Link>
                        </div>
                        
                        <!-- Quick Purchase Card -->
                        <Link 
                            href="/visa-codes/purchase"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200 group"
                        >
                            <div class="flex items-center space-x-3 mb-3">
                                <ShoppingCart :size="24" class="text-green-400 group-hover:text-green-300" />
                                <h3 class="text-lg font-bold text-white group-hover:text-green-300">Purchase Visa Codes</h3>
                            </div>
                            <p class="text-sm text-green-400 group-hover:text-green-300">Browse and purchase visa codes</p>
                        </Link>
                    </div>

                    <!-- My Recent Visa Codes -->
                    <div v-if="myCodes.length > 0" class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                                <CreditCard :size="20" />
                                <span>My Recent Visa Codes</span>
                            </h3>
                            <Link 
                                href="/visa-codes/my-codes"
                                class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                            >
                                <span>View All</span>
                                <ArrowRight :size="14" />
                            </Link>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="code in myCodes" 
                                :key="code.id"
                                :class="getStatusColor(code.status_badge?.color)"
                                class="backdrop-blur-xl border rounded-xl p-4"
                            >
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <Globe :size="16" />
                                        <span class="font-bold text-white">{{ code.country_name || 'N/A' }}</span>
                                    </div>
                                    <span 
                                        v-if="code.status_badge"
                                        class="px-2 py-1 rounded-full text-xs font-semibold"
                                        :style="`background-color: ${getStatusBgColor(code.status_badge.color)}; color: ${getStatusTextColor(code.status_badge.color)}`"
                                    >
                                        {{ code.status_badge.text || 'Unknown' }}
                                    </span>
                                </div>
                                
                                <div class="mb-3">
                                    <p class="text-sm opacity-75 mb-1">Visa Code</p>
                                    <div class="flex items-center justify-between bg-black/30 rounded-lg p-2">
                                        <code class="text-white font-mono text-sm">{{ code.code || 'N/A' }}</code>
                                        <button @click="copyToClipboard(code.code)" class="text-blue-400 hover:text-blue-300">
                                            <Copy :size="14" />
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Type</span>
                                        <span class="text-sm font-semibold">{{ code.visa_name || 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Purchased</span>
                                        <span class="text-sm font-semibold">{{ code.purchased_at || 'N/A' }}</span>
                                    </div>
                                    <div v-if="code.expires_at" class="flex justify-between">
                                        <span class="text-sm opacity-75">Expires</span>
                                        <span class="text-sm font-semibold">{{ code.expires_at }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Visa Codes -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                                <Tag :size="20" />
                                <span>Available Visa Codes</span>
                            </h3>
                            <div class="flex space-x-2">
                                <select 
                                    v-model="selectedVisaType"
                                    @change="filterCodes"
                                    class="bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-1 text-sm focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Visa Types</option>
                                    <option v-for="(type, key) in visaTypes" :key="key" :value="key">
                                        {{ type.name || key }}
                                    </option>
                                </select>
                                
                                <select 
                                    v-model="selectedCountry"
                                    @change="filterCodes"
                                    class="bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-1 text-sm focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Countries</option>
                                    <option v-for="(name, key) in countries" :key="key" :value="key">
                                        {{ name || key }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Filter Loading -->
                        <div v-if="isFiltering" class="text-center py-4">
                            <Loader2 class="animate-spin mx-auto text-blue-400" :size="24" />
                        </div>
                        
                        <!-- No Codes Available -->
                        <div v-else-if="availableCodes.data.length === 0" class="text-center py-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl">
                            <Tag :size="48" class="mx-auto text-blue-400 mb-3" />
                            <h4 class="text-lg font-bold text-white mb-2">No Visa Codes Available</h4>
                            <p class="text-blue-300">Check back later for new visa code releases.</p>
                            <div class="mt-4">
                                <button @click="clearFilters" class="text-blue-400 hover:text-blue-300 text-sm">
                                    Clear filters
                                </button>
                            </div>
                        </div>
                        
                        <!-- Available Codes Grid -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="code in availableCodes.data" 
                                :key="code.id"
                                class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <Globe :size="20" class="text-green-400" />
                                        <h4 class="text-lg font-bold text-white">{{ code.country_name || 'N/A' }}</h4>
                                    </div>
                                    <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">
                                        Available
                                    </span>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="text-sm opacity-75">Visa Type</p>
                                            <p class="text-lg font-bold text-white">{{ code.visa_name || 'N/A' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm opacity-75">Price</p>
                                            <p class="text-xl font-bold text-green-400">KES {{ formatCurrency(code.amount || 0) }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <p class="text-sm opacity-75 mb-1">Visa Code</p>
                                        <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                            <code class="text-white font-mono text-sm">{{ code.code || 'N/A' }}</code>
                                            <button @click="copyToClipboard(code.code)" class="text-blue-400 hover:text-blue-300">
                                                <Copy :size="16" />
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <div v-if="code.duration" class="flex items-center space-x-2">
                                            <Clock :size="14" class="text-blue-400" />
                                            <span class="text-sm text-blue-300">{{ code.duration }}</span>
                                        </div>
                                        <div v-if="code.days_remaining" class="flex items-center space-x-2">
                                            <Calendar :size="14" class="text-orange-400" />
                                            <span class="text-sm text-orange-300">Expires in {{ code.days_remaining }} days</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <p class="text-sm opacity-75 mb-2">Features:</p>
                                        <ul class="space-y-1">
                                            <li v-for="(feature, index) in (code.features || []).slice(0, 3)" :key="index" 
                                                class="flex items-start space-x-2 text-sm">
                                                <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                                <span class="text-green-100">{{ feature }}</span>
                                            </li>
                                            <li v-if="code.features && code.features.length > 3" class="text-xs text-green-300 pl-6">
                                                +{{ code.features.length - 3 }} more features
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                
                                <Link 
                                    :href="`/visa-codes/purchase?visa_type=${code.visa_type || ''}&country=${code.country || ''}`"
                                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center justify-center space-x-2"
                                >
                                    <ShoppingCart :size="18" />
                                    <span>Purchase Now</span>
                                </Link>
                            </div>
                        </div>
                        
                        <!-- Fixed Pagination Section -->
                        <div v-if="availableLinks && availableLinks.length > 0" class="mt-6">
                            <div class="flex items-center justify-center space-x-2">
                                <template v-for="link in availableLinks" :key="link.label">
                                    <!-- For active/clickable links -->
                                    <Link 
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="{
                                            'bg-blue-600 text-white': link.active,
                                            'bg-slate-800/50 text-slate-300 hover:bg-slate-700/50': !link.active
                                        }"
                                        class="px-4 py-2 rounded-lg transition-all duration-200"
                                        preserve-scroll
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    
                                    <!-- For disabled links -->
                                    <span 
                                        v-else
                                        class="bg-slate-800/30 text-slate-500 cursor-not-allowed px-4 py-2 rounded-lg"
                                    >
                                        <span v-html="link.label"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link 
                            href="/visa-codes/purchase"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200 group"
                        >
                            <div class="flex items-center space-x-3">
                                <ShoppingCart :size="24" class="text-green-400 group-hover:text-green-300" />
                                <div>
                                    <h4 class="text-white font-bold group-hover:text-green-300">Purchase Visa Codes</h4>
                                    <p class="text-green-400 text-sm">Browse and buy visa codes</p>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            href="/visa-codes/my-codes"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                        >
                            <div class="flex items-center space-x-3">
                                <CreditCard :size="24" class="text-blue-400 group-hover:text-blue-300" />
                                <div>
                                    <h4 class="text-white font-bold group-hover:text-blue-300">My Visa Codes</h4>
                                    <p class="text-blue-400 text-sm">View your purchased codes</p>
                                </div>
                            </div>
                        </Link>
                        
                        <Link 
                            href="/visa-codes/history"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                        >
                            <div class="flex items-center space-x-3">
                                <History :size="24" class="text-purple-400 group-hover:text-purple-300" />
                                <div>
                                    <h4 class="text-white font-bold group-hover:text-purple-300">Purchase History</h4>
                                    <p class="text-purple-400 text-sm">View all your transactions</p>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Globe, Wallet, CreditCard, ShoppingCart,
    ArrowRight, Tag, Copy, CheckCircle,
    Clock, Calendar, History, Loader2,
    AlertCircle
} from 'lucide-vue-next';

// Props with defaults
const props = defineProps({
    availableCodes: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    myCodes: {
        type: Array,
        default: () => []
    },
    visaTypes: {
        type: Object,
        default: () => ({})
    },
    countries: {
        type: Object,
        default: () => ({})
    },
    userBalance: {
        type: [Number, String],
        default: 0
    },
    selectedVisaType: {
        type: String,
        default: ''
    },
    selectedCountry: {
        type: String,
        default: ''
    }
});

// State
const selectedVisaType = ref(props.selectedVisaType || '');
const selectedCountry = ref(props.selectedCountry || '');
const isLoading = ref(false);
const hasError = ref(false);
const isFiltering = ref(false);

// Computed properties
const formattedUserBalance = computed(() => {
    const balance = parseFloat(props.userBalance) || 0;
    return formatCurrency(balance);
});

// Safe pagination links
const availableLinks = computed(() => {
    if (!props.availableCodes?.links) {
        return [];
    }
    // Filter out any links with null URLs
    return props.availableCodes.links.filter(link => link !== null);
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getStatusColor = (color = 'gray') => {
    const colors = {
        green: 'bg-gradient-to-br from-green-600/20 to-emerald-600/20 border-green-500/30',
        blue: 'bg-gradient-to-br from-blue-600/20 to-cyan-600/20 border-blue-500/30',
        purple: 'bg-gradient-to-br from-purple-600/20 to-pink-600/20 border-purple-500/30',
        red: 'bg-gradient-to-br from-red-600/20 to-orange-600/20 border-red-500/30',
        gray: 'bg-gradient-to-br from-slate-600/20 to-slate-700/20 border-slate-500/30',
    };
    return colors[color] || colors.gray;
};

const getStatusBgColor = (color = 'gray') => {
    const colors = {
        green: 'rgba(34, 197, 94, 0.2)',
        blue: 'rgba(59, 130, 246, 0.2)',
        purple: 'rgba(168, 85, 247, 0.2)',
        red: 'rgba(239, 68, 68, 0.2)',
        gray: 'rgba(100, 116, 139, 0.2)',
    };
    return colors[color] || colors.gray;
};

const getStatusTextColor = (color = 'gray') => {
    const colors = {
        green: '#4ade80',
        blue: '#60a5fa',
        purple: '#c084fc',
        red: '#f87171',
        gray: '#94a3b8',
    };
    return colors[color] || colors.gray;
};

const copyToClipboard = async (text) => {
    if (!text) return;
    
    try {
        await navigator.clipboard.writeText(text);
        alert('Visa code copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard.');
    }
};

const filterCodes = () => {
    isFiltering.value = true;
    
    const params = {};
    if (selectedVisaType.value) params.visa_type = selectedVisaType.value;
    if (selectedCountry.value) params.country = selectedCountry.value;
    
    router.get('/visa-codes', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isFiltering.value = false;
        },
        onError: () => {
            isFiltering.value = false;
            hasError.value = true;
        }
    });
};

const clearFilters = () => {
    selectedVisaType.value = '';
    selectedCountry.value = '';
    filterCodes();
};

const loadData = () => {
    hasError.value = false;
    router.reload();
};

// Initialize filters from URL on mount
onMounted(() => {
    console.log('VisaCodes Index mounted');
    console.log('Available codes:', props.availableCodes);
    console.log('Available links:', props.availableCodes?.links);
    
    // If no data is passed, try to reload
    if (!props.availableCodes || !props.visaTypes) {
        console.warn('Missing props, attempting to reload');
        loadData();
    }
});

// Watch for route changes
watch(() => router.url, () => {
    // Extract filters from URL when route changes
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        selectedVisaType.value = urlParams.get('visa_type') || '';
        selectedCountry.value = urlParams.get('country') || '';
    }
}, { immediate: true });
</script>