<template>
    <DashboardLayout title="Visa Code History">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <History :size="16" class="text-purple-400" />
            <span>Visa Codes</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Purchase History</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Purchase History</h1>
                    <p class="text-purple-300">View all your visa code transactions</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <CreditCard :size="20" class="text-blue-400" />
                            <p class="text-sm text-blue-300">Total Purchases</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-4 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <CheckCircle :size="20" class="text-green-400" />
                            <p class="text-sm text-green-300">Completed</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.completed }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-4 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <Clock :size="20" class="text-yellow-400" />
                            <p class="text-sm text-yellow-300">Pending</p>
                        </div>
                        <p class="text-2xl font-bold text-white">{{ stats.pending }}</p>
                    </div>
                    
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-2 mb-2">
                            <DollarSign :size="20" class="text-purple-400" />
                            <p class="text-sm text-purple-300">Total Spent</p>
                        </div>
                        <p class="text-2xl font-bold text-white">KES {{ stats.totalSpent.toLocaleString() }}</p>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="mb-6">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="flex-1 w-full">
                                <div class="relative">
                                    <Search :size="18" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-purple-400" />
                                    <input
                                        v-model="search"
                                        placeholder="Search transactions..."
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl pl-10 pr-4 py-2 focus:outline-none focus:border-purple-400"
                                    />
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <select 
                                    v-model="statusFilter"
                                    class="bg-black/30 border border-purple-500/30 text-white rounded-xl px-3 py-2 focus:outline-none focus:border-purple-400"
                                >
                                    <option value="">All Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                
                                <input
                                    v-model="dateFilter"
                                    type="date"
                                    class="bg-black/30 border border-purple-500/30 text-white rounded-xl px-3 py-2 focus:outline-none focus:border-purple-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-8">
                    <Loader2 class="animate-spin mx-auto text-purple-400" :size="32" />
                    <p class="text-purple-300 mt-2">Loading transaction history...</p>
                </div>

                <!-- No History -->
                <div v-else-if="filteredPurchases.length === 0" class="text-center py-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl">
                    <History :size="48" class="mx-auto text-purple-400 mb-3" />
                    <h4 class="text-lg font-bold text-white mb-2">No Purchase History</h4>
                    <p class="text-purple-300 mb-4">You haven't made any visa code purchases yet.</p>
                    <Link 
                        href="/visa-codes/purchase"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-6 rounded-xl transition-all duration-200 inline-flex items-center space-x-2"
                    >
                        <ShoppingCart :size="16" />
                        <span>Purchase Visa Codes</span>
                    </Link>
                </div>

                <!-- Transaction Table -->
                <div v-else class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl shadow-2xl shadow-purple-500/20 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-black/30">
                                    <th class="text-left p-4 text-purple-300 font-semibold">Transaction ID</th>
                                    <th class="text-left p-4 text-purple-300 font-semibold">Visa Code</th>
                                    <th class="text-left p-4 text-purple-300 font-semibold">Amount</th>
                                    <th class="text-left p-4 text-purple-300 font-semibold">Status</th>
                                    <th class="text-left p-4 text-purple-300 font-semibold">Date</th>
                                    <th class="text-left p-4 text-purple-300 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="purchase in filteredPurchases" 
                                    :key="purchase.id"
                                    class="border-b border-purple-500/10 hover:bg-black/20 transition-colors duration-200"
                                >
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <CreditCard :size="16" class="text-blue-400" />
                                            <code class="text-white font-mono text-sm">{{ purchase.transaction_id }}</code>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <p class="text-white font-semibold">{{ purchase.visa_code }}</p>
                                            <p class="text-sm text-blue-300">{{ purchase.visa_type }} • {{ purchase.country }}</p>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-lg font-bold text-green-400">KES {{ purchase.amount.toLocaleString() }}</p>
                                        <p class="text-sm text-green-300">{{ purchase.payment_method }}</p>
                                    </td>
                                    <td class="p-4">
                                        <span 
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${getStatusBgColor(purchase.status)}; color: ${getStatusTextColor(purchase.status)}`"
                                        >
                                            {{ purchase.status }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-white">{{ purchase.created_at }}</p>
                                    </td>
                                    <td class="p-4">
                                        <button
                                            @click="showPurchaseDetails(purchase)"
                                            class="text-purple-400 hover:text-purple-300 flex items-center space-x-1"
                                        >
                                            <Eye :size="16" />
                                            <span class="text-sm">View</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="purchases.links && purchases.links.length > 3" class="mt-8">
                    <div class="flex items-center justify-center space-x-2">
                        <Link 
                            v-for="link in purchases.links" 
                            :key="link.label"
                            :href="link.url"
                            :class="{
                                'bg-purple-600 text-white': link.active,
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
                        href="/visa-codes/my-codes"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <CreditCard :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">My Visa Codes</h4>
                                <p class="text-blue-400 text-sm">View purchased codes</p>
                            </div>
                        </div>
                    </Link>
                    
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
                        href="/wallet"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <Wallet :size="24" class="text-purple-400 group-hover:text-purple-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-purple-300">Wallet</h4>
                                <p class="text-purple-400 text-sm">Manage your balance</p>
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
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    History, CreditCard, CheckCircle, Clock,
    DollarSign, Search, ShoppingCart, Eye, Wallet, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    purchases: Object,
});

const search = ref('');
const statusFilter = ref('');
const dateFilter = ref('');
const loading = ref(false);

// Calculate stats
const stats = computed(() => {
    const data = props.purchases.data;
    return {
        total: data.length,
        completed: data.filter(p => p.status === 'completed').length,
        pending: data.filter(p => p.status === 'pending').length,
        totalSpent: data.reduce((sum, p) => sum + p.amount, 0),
    };
});

// Filter purchases
const filteredPurchases = computed(() => {
    return props.purchases.data.filter(purchase => {
        // Search filter
        if (search.value) {
            const searchTerm = search.value.toLowerCase();
            if (!purchase.transaction_id.toLowerCase().includes(searchTerm) &&
                !purchase.visa_code.toLowerCase().includes(searchTerm)) {
                return false;
            }
        }
        
        // Status filter
        if (statusFilter.value && purchase.status !== statusFilter.value) {
            return false;
        }
        
        // Date filter
        if (dateFilter.value) {
            const purchaseDate = new Date(purchase.created_at).toISOString().split('T')[0];
            if (purchaseDate !== dateFilter.value) {
                return false;
            }
        }
        
        return true;
    });
});

const getStatusBgColor = (status) => {
    const colors = {
        completed: 'rgba(34, 197, 94, 0.2)',
        pending: 'rgba(250, 204, 21, 0.2)',
        failed: 'rgba(239, 68, 68, 0.2)',
        cancelled: 'rgba(100, 116, 139, 0.2)',
    };
    return colors[status] || 'rgba(100, 116, 139, 0.2)';
};

const getStatusTextColor = (status) => {
    const colors = {
        completed: '#4ade80',
        pending: '#fbbf24',
        failed: '#f87171',
        cancelled: '#94a3b8',
    };
    return colors[status] || '#94a3b8';
};

const showPurchaseDetails = (purchase) => {
    const details = purchase.payment_details || {};
    alert(
        `Transaction Details:\n\n` +
        `Transaction ID: ${purchase.transaction_id}\n` +
        `Visa Code: ${purchase.visa_code}\n` +
        `Visa Type: ${purchase.visa_type}\n` +
        `Country: ${purchase.country}\n` +
        `Amount: KES ${purchase.amount.toLocaleString()}\n` +
        `Payment Method: ${purchase.payment_method}\n` +
        `Status: ${purchase.status}\n` +
        `Date: ${purchase.created_at}\n\n` +
        `Payment Details:\n` +
        `Balance Before: ${details.balance_before ? `KES ${details.balance_before.toLocaleString()}` : 'N/A'}\n` +
        `Balance After: ${details.balance_after ? `KES ${details.balance_after.toLocaleString()}` : 'N/A'}`
    );
};
</script>