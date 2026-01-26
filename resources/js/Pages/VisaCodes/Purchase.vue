<template>
    <DashboardLayout title="Purchase Visa Codes">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <ShoppingCart :size="16" class="text-green-400" />
            <span>Visa Codes</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Purchase</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- User Balance -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Your Balance</h3>
                                <p class="text-2xl font-bold text-green-400">KES {{ userBalance.toLocaleString() }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-green-300 text-sm mb-1">Available for purchase</p>
                                <div v-if="selectedCode" class="mt-2">
                                    <p class="text-sm text-white">Selected: {{ selectedCode.visa_name }} - {{ selectedCode.country_name }}</p>
                                    <p class="text-lg font-bold text-white">KES {{ selectedCode.amount.toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Filter Visa Codes</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Visa Type Filter -->
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    <Tag :size="16" class="inline mr-2" />
                                    Visa Type
                                </label>
                                <select 
                                    v-model="selectedVisaType"
                                    @change="filterCodes"
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Visa Types</option>
                                    <option v-for="(type, key) in visaTypes" :key="key" :value="key">
                                        {{ type.name }} - KES {{ type.price.toLocaleString() }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Country Filter -->
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    <Globe :size="16" class="inline mr-2" />
                                    Country
                                </label>
                                <select 
                                    v-model="selectedCountry"
                                    @change="filterCodes"
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Countries</option>
                                    <option v-for="(name, code) in countries" :key="code" :value="code">
                                        {{ name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Clear Filters -->
                        <div class="mt-4">
                            <button
                                @click="clearFilters"
                                class="text-sm text-blue-400 hover:text-blue-300 flex items-center space-x-1"
                            >
                                <X :size="14" />
                                <span>Clear Filters</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <Loader2 class="animate-spin mx-auto text-green-400" :size="32" />
                    <p class="text-green-300 mt-2">Loading visa codes...</p>
                </div>

                <!-- No Codes Available -->
                <div v-else-if="availableCodes.length === 0" class="text-center py-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl">
                    <Tag :size="48" class="mx-auto text-green-400 mb-3" />
                    <h4 class="text-lg font-bold text-white mb-2">No Visa Codes Found</h4>
                    <p class="text-green-300 mb-4">No visa codes match your filters. Try different criteria.</p>
                    <button
                        @click="clearFilters"
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-6 rounded-xl transition-all duration-200"
                    >
                        Clear Filters
                    </button>
                </div>

                <!-- Available Codes Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="code in availableCodes" 
                        :key="code.id"
                        :class="{
                            'border-green-500 shadow-green-500/20': !isCodeSelected(code.id),
                            'border-blue-500 shadow-blue-500/40': isCodeSelected(code.id)
                        }"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border rounded-2xl p-6 shadow-2xl transition-all duration-200 hover:border-blue-500 cursor-pointer"
                        @click="selectCode(code)"
                    >
                        <!-- Selected Indicator -->
                        <div v-if="isCodeSelected(code.id)" class="absolute top-4 right-4">
                            <div class="bg-blue-500 text-white p-1 rounded-full">
                                <Check :size="16" />
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <Globe :size="20" class="text-green-400" />
                                    <h4 class="text-lg font-bold text-white">{{ code.country_name }}</h4>
                                </div>
                                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    Available
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="text-sm opacity-75">Visa Type</p>
                                <p class="text-xl font-bold text-white">{{ code.visa_name }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <p class="text-sm opacity-75 mb-1">Visa Code</p>
                                <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                    <code class="text-white font-mono text-sm">{{ code.code }}</code>
                                    <button @click="copyToClipboard(code.code)" class="text-blue-400 hover:text-blue-300">
                                        <Copy :size="16" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center space-x-2">
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
                                    <li v-for="(feature, index) in code.features.slice(0, 3)" :key="index" 
                                        class="flex items-start space-x-2 text-sm">
                                        <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                        <span class="text-green-100">{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm opacity-75">Price</p>
                                    <p class="text-2xl font-bold text-green-400">KES {{ code.amount.toLocaleString() }}</p>
                                </div>
                                <div v-if="code.days_remaining" class="text-right">
                                    <p class="text-sm opacity-75">Valid for</p>
                                    <p class="text-sm font-semibold text-white">{{ code.days_remaining }} days</p>
                                </div>
                            </div>
                        </div>
                        
                        <button
                            @click.stop="selectCode(code)"
                            :class="{
                                'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500': !isCodeSelected(code.id),
                                'bg-gradient-to-r from-blue-600 to-cyan-600': isCodeSelected(code.id)
                            }"
                            class="w-full text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center space-x-2"
                        >
                            <ShoppingCart :size="18" />
                            <span>{{ isCodeSelected(code.id) ? 'Selected' : 'Select This Code' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Purchase Section -->
                <div v-if="selectedCode" class="mb-8">
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                            <CheckCircle :size="20" />
                            <span>Complete Your Purchase</span>
                        </h3>
                        
                        <!-- Selected Code Details -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-1">Selected Visa Code</p>
                                    <p class="text-lg font-bold text-white">{{ selectedCode.code }}</p>
                                </div>
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-1">Visa Type</p>
                                    <p class="text-lg font-bold text-white">{{ selectedCode.visa_name }}</p>
                                </div>
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-1">Country</p>
                                    <p class="text-lg font-bold text-white">{{ selectedCode.country_name }}</p>
                                </div>
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-1">Price</p>
                                    <p class="text-lg font-bold text-green-400">KES {{ selectedCode.amount.toLocaleString() }}</p>
                                </div>
                            </div>
                            
                            <div class="bg-black/30 rounded-xl p-4 mb-4">
                                <p class="text-sm opacity-75 mb-2">Features Included:</p>
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <li v-for="(feature, index) in selectedCode.features" :key="index" 
                                        class="flex items-start space-x-2 text-sm">
                                        <CheckCircle :size="14" class="text-green-400 mt-0.5 flex-shrink-0" />
                                        <span class="text-green-100">{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="bg-black/30 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm opacity-75">Your Balance</p>
                                        <p class="text-xl font-bold text-purple-400">KES {{ userBalance.toLocaleString() }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm opacity-75">Remaining After Purchase</p>
                                        <p class="text-xl font-bold" :class="{
                                            'text-green-400': userBalance >= selectedCode.amount,
                                            'text-red-400': userBalance < selectedCode.amount
                                        }">
                                            KES {{ (userBalance - selectedCode.amount).toLocaleString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Purchase Button -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div>
                                <p v-if="userBalance < selectedCode.amount" class="text-red-400 text-sm">
                                    ❌ Insufficient balance. You need KES {{ (selectedCode.amount - userBalance).toLocaleString() }} more.
                                </p>
                                <p v-else class="text-green-400 text-sm">
                                    ✅ Sufficient balance available for purchase.
                                </p>
                            </div>
                            
                            <button
                                @click="processPurchase"
                                :disabled="processing || userBalance < selectedCode.amount"
                                class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 flex items-center justify-center space-x-2"
                            >
                                <Loader2 v-if="processing" :size="20" class="animate-spin" />
                                <Lock v-else :size="18" />
                                <span>Purchase Visa Code</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        href="/visa-codes"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <ArrowLeft :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">Back to Visa Codes</h4>
                                <p class="text-blue-400 text-sm">Browse all available visa codes</p>
                            </div>
                        </div>
                    </Link>
                    
                    <Link 
                        href="/wallet/deposit"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <Wallet :size="24" class="text-green-400 group-hover:text-green-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-green-300">Deposit Funds</h4>
                                <p class="text-green-400 text-sm">Add funds to your wallet</p>
                            </div>
                        </div>
                    </Link>
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
    ShoppingCart, Globe, Tag, Copy,
    CheckCircle, Clock, Calendar, Loader2,
    Check, X, Lock, Wallet, ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    availableCodes: Array,
    visaTypes: Object,
    countries: Object,
    selectedVisaType: String,
    selectedCountry: String,
    userBalance: Number,
});

const selectedCode = ref(null);
const processing = ref(false);
const loading = ref(false);

const selectCode = (code) => {
    selectedCode.value = code;
};

const isCodeSelected = (codeId) => {
    return selectedCode.value?.id === codeId;
};

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Visa code copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard.');
    }
};

const filterCodes = () => {
    loading.value = true;
    
    const params = {};
    if (selectedVisaType.value) params.visa_type = selectedVisaType.value;
    if (selectedCountry.value) params.country = selectedCountry.value;
    
    router.get('/visa-codes/purchase', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            selectedCode.value = null; // Reset selection when filters change
        }
    });
};

const clearFilters = () => {
    selectedVisaType.value = '';
    selectedCountry.value = '';
    filterCodes();
};

const processPurchase = async () => {
    if (!selectedCode.value) {
        alert('Please select a visa code to purchase.');
        return;
    }

    if (props.userBalance < selectedCode.value.amount) {
        alert(`Insufficient balance. You need KES ${(selectedCode.value.amount - props.userBalance).toLocaleString()} more.`);
        return;
    }

    if (!confirm(`Are you sure you want to purchase visa code ${selectedCode.value.code} for KES ${selectedCode.value.amount.toLocaleString()}?`)) {
        return;
    }

    processing.value = true;

    try {
        await router.post('/visa-codes/purchase', {
            visa_code_id: selectedCode.value.id
        }, {
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
                console.error('Error purchasing visa code:', errors);
                alert('Failed to purchase visa code. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error purchasing visa code:', error);
        alert('An unexpected error occurred.');
    } finally {
        processing.value = false;
    }
};

// Initialize filters from props
const selectedVisaType = ref(props.selectedVisaType || '');
const selectedCountry = ref(props.selectedCountry || '');
</script>