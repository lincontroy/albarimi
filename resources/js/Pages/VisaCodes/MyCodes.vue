<template>
    <DashboardLayout title="My Visa Codes">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <CreditCard :size="16" class="text-blue-400" />
            <span>Visa Codes</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">My Visa Codes</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">My Visa Codes</h1>
                    <p class="text-blue-300">View and manage your purchased visa codes</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <CreditCard :size="20" class="text-blue-400" />
                            <p class="text-sm text-blue-300">Total Codes</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-4 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <CheckCircle :size="20" class="text-green-400" />
                            <p class="text-sm text-green-300">Active</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.active }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <Clock :size="20" class="text-purple-400" />
                            <p class="text-sm text-purple-300">Pending</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.purchased }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-orange-600/40 to-red-600/40 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-4 shadow-2xl shadow-orange-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <Calendar :size="20" class="text-orange-400" />
                            <p class="text-sm text-orange-300">Expiring Soon</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.expiring }}</p>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="mb-6">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="flex-1 w-full">
                                <div class="relative">
                                    <Search :size="18" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-blue-400" />
                                    <input
                                        v-model="search"
                                        placeholder="Search visa codes..."
                                        class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl pl-10 pr-4 py-2 focus:outline-none focus:border-blue-400"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <select 
                                    v-model="statusFilter"
                                    class="bg-black/30 border border-blue-500/30 text-white rounded-xl px-3 py-2 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Status</option>
                                    <option value="purchased">Purchased</option>
                                    <option value="activated">Activated</option>
                                    <option value="used">Used</option>
                                    <option value="expired">Expired</option>
                                </select>
                                
                                <select 
                                    v-model="visaTypeFilter"
                                    class="bg-black/30 border border-blue-500/30 text-white rounded-xl px-3 py-2 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="(type, key) in visaTypes" :key="key" :value="key">
                                        {{ type.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <Loader2 class="animate-spin mx-auto text-blue-400" :size="32" />
                    <p class="text-blue-300 mt-2">Loading your visa codes...</p>
                </div>

                <!-- No Codes -->
                <div v-else-if="filteredCodes.length === 0" class="text-center py-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl">
                    <CreditCard :size="48" class="mx-auto text-blue-400 mb-3" />
                    <h4 class="text-lg font-bold text-white mb-2">No Visa Codes Found</h4>
                    <p class="text-blue-300 mb-4">You haven't purchased any visa codes yet.</p>
                    <Link 
                        href="/visa-codes/purchase"
                        class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-6 rounded-xl transition-all duration-200 inline-flex items-center space-x-2"
                    >
                        <ShoppingCart :size="16" />
                        <span>Purchase Visa Codes</span>
                    </Link>
                </div>

                <!-- Visa Codes Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="code in filteredCodes" 
                        :key="code.id"
                        :class="getStatusColor(code.status_badge.color)"
                        class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl transition-all duration-200 hover:border-opacity-100"
                    >
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            <span 
                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                :style="`background-color: ${getStatusBgColor(code.status_badge.color)}; color: ${getStatusTextColor(code.status_badge.color)}`"
                            >
                                {{ code.status_badge.text }}
                            </span>
                        </div>
                        
                        <!-- Country and Type -->
                        <div class="mb-4">
                            <div class="flex items-center space-x-2 mb-2">
                                <Globe :size="20" />
                                <h4 class="text-lg font-bold text-white">{{ code.country_name }}</h4>
                            </div>
                            <p class="text-sm text-blue-300">{{ code.visa_name }}</p>
                        </div>
                        
                        <!-- Visa Code -->
                        <div class="mb-4">
                            <p class="text-sm opacity-75 mb-1">Visa Code</p>
                            <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                <code class="text-white font-mono text-lg">{{ code.code }}</code>
                                <div class="flex items-center space-x-2">
                                    <button @click="copyToClipboard(code.code)" class="text-blue-400 hover:text-blue-300">
                                        <Copy :size="16" />
                                    </button>
                                    <button @click="showCodeDetails(code)" class="text-purple-400 hover:text-purple-300">
                                        <Eye :size="16" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Details -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between">
                                <span class="text-sm opacity-75">Amount</span>
                                <span class="text-sm font-semibold text-green-400">KES {{ code.amount.toLocaleString() }}</span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-sm opacity-75">Purchased</span>
                                <span class="text-sm font-semibold">{{ code.purchased_at }}</span>
                            </div>
                            
                            <div v-if="code.activated_at" class="flex justify-between">
                                <span class="text-sm opacity-75">Activated</span>
                                <span class="text-sm font-semibold">{{ code.activated_at }}</span>
                            </div>
                            
                            <div v-if="code.expires_at" class="flex justify-between">
                                <span class="text-sm opacity-75">Expires</span>
                                <span class="text-sm font-semibold">{{ code.expires_at }}</span>
                            </div>
                            
                            <div v-if="code.days_remaining !== null" class="flex justify-between">
                                <span class="text-sm opacity-75">Days Remaining</span>
                                <span class="text-sm font-semibold" :class="{
                                    'text-green-400': code.days_remaining > 7,
                                    'text-orange-400': code.days_remaining > 0 && code.days_remaining <= 7,
                                    'text-red-400': code.days_remaining <= 0
                                }">
                                    {{ code.days_remaining }} days
                                </span>
                            </div>
                        </div>
                        
                        <!-- Features -->
                        <div v-if="code.features.length > 0" class="mb-4">
                            <p class="text-sm opacity-75 mb-2">Features:</p>
                            <ul class="space-y-1">
                                <li v-for="(feature, index) in code.features.slice(0, 2)" :key="index" 
                                    class="flex items-start space-x-2 text-xs">
                                    <CheckCircle :size="12" class="text-green-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-green-100">{{ feature }}</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button
                                v-if="code.status === 'purchased'"
                                @click="activateCode(code.id)"
                                class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 text-sm"
                            >
                                Activate
                            </button>
                            
                            <button
                                @click="showCodeDetails(code)"
                                class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 text-sm"
                            >
                                Details
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="codes.links && codes.links.length > 3" class="mt-8">
                    <div class="flex items-center justify-center space-x-2">
                        <Link 
                            v-for="link in codes.links" 
                            :key="link.label"
                            :href="link.url"
                            :class="{
                                'bg-blue-600 text-white': link.active,
                                'bg-slate-800/50 text-slate-300 hover:bg-slate-700/50': !link.active && link.url,
                                'bg-slate-800/30 text-slate-500 cursor-not-allowed': !link.url
                            }"
                            class="px-4 py-2 rounded-lg transition-all duration-200"
                            preserve-scroll
                        >
                            <span v-html="link.label"></span>
                        </Link>
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
                                <h4 class="text-white font-bold group-hover:text-green-300">Purchase More</h4>
                                <p class="text-green-400 text-sm">Buy new visa codes</p>
                            </div>
                        </div>
                    </Link>
                    
                    <Link 
                        href="/visa-codes"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <List :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">All Visa Codes</h4>
                                <p class="text-blue-400 text-sm">Browse available codes</p>
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
                                <p class="text-purple-400 text-sm">View transaction history</p>
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
    CreditCard, CheckCircle, Clock, Calendar,
    Search, ShoppingCart, Globe, Copy,
    Eye, List, History, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    codes: Object,
    visaTypes: Object,
});

const search = ref('');
const statusFilter = ref('');
const visaTypeFilter = ref('');
const loading = ref(false);

// Calculate stats
const stats = computed(() => {
    const data = props.codes.data;
    return {
        total: data.length,
        active: data.filter(c => c.status === 'activated').length,
        purchased: data.filter(c => c.status === 'purchased').length,
        expiring: data.filter(c => c.days_remaining !== null && c.days_remaining > 0 && c.days_remaining <= 7).length,
    };
});

// Filter codes
const filteredCodes = computed(() => {
    return props.codes.data.filter(code => {
        // Search filter
        if (search.value) {
            const searchTerm = search.value.toLowerCase();
            if (!code.code.toLowerCase().includes(searchTerm) &&
                !code.country_name.toLowerCase().includes(searchTerm) &&
                !code.visa_name.toLowerCase().includes(searchTerm)) {
                return false;
            }
        }
        
        // Status filter
        if (statusFilter.value && code.status !== statusFilter.value) {
            return false;
        }
        
        // Visa type filter
        if (visaTypeFilter.value && code.visa_type !== visaTypeFilter.value) {
            return false;
        }
        
        return true;
    });
});

const getStatusColor = (color) => {
    const colors = {
        green: 'bg-gradient-to-br from-green-600/20 to-emerald-600/20 border-green-500/30 shadow-green-500/20',
        blue: 'bg-gradient-to-br from-blue-600/20 to-cyan-600/20 border-blue-500/30 shadow-blue-500/20',
        purple: 'bg-gradient-to-br from-purple-600/20 to-pink-600/20 border-purple-500/30 shadow-purple-500/20',
        red: 'bg-gradient-to-br from-red-600/20 to-orange-600/20 border-red-500/30 shadow-red-500/20',
        gray: 'bg-gradient-to-br from-slate-600/20 to-slate-700/20 border-slate-500/30 shadow-slate-500/20',
    };
    return colors[color] || colors.gray;
};

const getStatusBgColor = (color) => {
    const colors = {
        green: 'rgba(34, 197, 94, 0.2)',
        blue: 'rgba(59, 130, 246, 0.2)',
        purple: 'rgba(168, 85, 247, 0.2)',
        red: 'rgba(239, 68, 68, 0.2)',
        gray: 'rgba(100, 116, 139, 0.2)',
    };
    return colors[color] || colors.gray;
};

const getStatusTextColor = (color) => {
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
    try {
        await navigator.clipboard.writeText(text);
        alert('Visa code copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard.');
    }
};

const showCodeDetails = (code) => {
    alert(
        `Visa Code Details:\n\n` +
        `Code: ${code.code}\n` +
        `Type: ${code.visa_name}\n` +
        `Country: ${code.country_name}\n` +
        `Status: ${code.status_badge.text}\n` +
        `Amount: KES ${code.amount.toLocaleString()}\n` +
        `Purchased: ${code.purchased_at}\n` +
        `Expires: ${code.expires_at || 'N/A'}\n` +
        `Days Remaining: ${code.days_remaining !== null ? code.days_remaining + ' days' : 'N/A'}\n\n` +
        `Features:\n${code.features.map(f => `• ${f}`).join('\n')}`
    );
};

const activateCode = async (codeId) => {
    if (!confirm('Are you sure you want to activate this visa code? This action cannot be undone.')) {
        return;
    }

    // Here you would typically make an API call to activate the code
    // For now, we'll just show a message
    alert('Code activation feature will be implemented soon.');
};
</script>