<template>
    <DashboardLayout title="Transaction History">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <Wallet :size="16" class="text-green-400" />
            <span>Wallet</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Transaction History</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Transaction History</h1>
                    <p class="text-green-300">View all your wallet transactions and activities</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Transactions -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <History :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Total Transactions</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total_transactions || 0 }}</p>
                        <p class="text-sm text-green-300">All transactions</p>
                    </div>

                    <!-- Total Deposits -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <TrendingUp :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Total Deposits</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total_deposits || 0 }}</p>
                        <p class="text-sm text-blue-300">Deposit transactions</p>
                    </div>

                    <!-- Total Withdrawals -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <TrendingDown :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Withdrawals</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total_withdrawals || 0 }}</p>
                        <p class="text-sm text-purple-300">Withdrawal transactions</p>
                    </div>

                    <!-- Total Transfers -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Repeat :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Total Transfers</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total_transfers || 0 }}</p>
                        <p class="text-sm text-yellow-300">Transfer transactions</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                <Search :size="16" class="inline mr-2" />
                                Search Transactions
                            </label>
                            <div class="relative">
                                <Search :size="18" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400" />
                                <input
                                    v-model="search"
                                    @input="debouncedFilter"
                                    placeholder="Search by ID, description, or reference..."
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl pl-12 pr-4 py-3 focus:outline-none focus:border-blue-400"
                                />
                            </div>
                        </div>
                        
                        <!-- Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                <Filter :size="16" class="inline mr-2" />
                                Transaction Type
                            </label>
                            <select 
                                v-model="type"
                                @change="filterTransactions"
                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-3 focus:outline-none focus:border-blue-400"
                            >
                                <option v-for="(text, value) in typeOptions" :key="value" :value="value">
                                    {{ text }}
                                </option>
                            </select>
                        </div>
                        
                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                <CheckCircle :size="16" class="inline mr-2" />
                                Status
                            </label>
                            <select 
                                v-model="status"
                                @change="filterTransactions"
                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-3 focus:outline-none focus:border-blue-400"
                            >
                                <option v-for="(text, value) in statusOptions" :key="value" :value="value">
                                    {{ text }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Filter Buttons -->
                    <div class="flex justify-end mt-4 space-x-2">
                        <button
                            @click="clearFilters"
                            class="border border-red-500/30 text-red-400 hover:bg-red-500/20 rounded-lg px-4 py-2 text-sm transition-all duration-200 flex items-center space-x-1"
                        >
                            <X :size="14" />
                            <span>Clear Filters</span>
                        </button>
                        <button
                            @click="exportHistory"
                            class="border border-green-500/30 text-green-400 hover:bg-green-500/20 rounded-lg px-4 py-2 text-sm transition-all duration-200 flex items-center space-x-1"
                        >
                            <Download :size="14" />
                            <span>Export CSV</span>
                        </button>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <!-- Table Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">All Transactions</h2>
                        <div class="text-blue-300 text-sm">
                            Showing {{ transactions.data.length }} of {{ transactions.total }}
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-12">
                        <Loader2 class="animate-spin mx-auto text-blue-400" :size="48" />
                        <p class="text-blue-300 mt-4">Loading transactions...</p>
                    </div>

                    <!-- No Transactions -->
                    <div v-else-if="transactions.data.length === 0" class="text-center py-12">
                        <FileText :size="48" class="mx-auto text-blue-400 mb-3" />
                        <h4 class="text-lg font-bold text-white mb-2">No Transactions Found</h4>
                        <p class="text-blue-300 mb-4">Try adjusting your filters or make your first transaction</p>
                        <Link 
                            href="/wallet/deposit"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <PlusCircle :size="16" />
                            <span>Make a Deposit</span>
                        </Link>
                    </div>

                    <!-- Transactions Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-blue-500/20">
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Transaction</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Type</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Amount</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Date</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="transaction in transactions.data" 
                                    :key="transaction.id"
                                    class="border-b border-blue-500/10 hover:bg-black/20 transition-colors"
                                >
                                    <!-- Transaction Details -->
                                    <td class="py-4 px-4">
                                        <div>
                                            <div class="text-white font-bold text-sm mb-1">
                                                {{ transaction.transaction_id }}
                                            </div>
                                            <div class="text-xs text-blue-300">
                                                {{ transaction.description }}
                                            </div>
                                            <div v-if="transaction.recipient_name" class="text-xs text-purple-300 mt-1">
                                                To: {{ transaction.recipient_name }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${transaction.type_badge.bgColor}; color: ${transaction.type_badge.color}`"
                                        >
                                            <component 
                                                :is="getTransactionIcon(transaction.type_badge.icon)" 
                                                :size="12" 
                                                class="mr-1"
                                            />
                                            {{ transaction.type_badge.text }}
                                        </span>
                                        <div v-if="transaction.payment_method" class="text-xs text-blue-300 mt-1">
                                            {{ transaction.payment_method }}
                                        </div>
                                    </td>

                                    <!-- Amount -->
                                    <td class="py-4 px-4">
                                        <div class="space-y-1">
                                            <div 
                                                class="font-bold text-sm"
                                                :class="transaction.is_positive ? 'text-green-400' : 'text-red-400'"
                                            >
                                                {{ transaction.is_positive ? '+' : '-' }}{{ transaction.net_amount }}
                                            </div>
                                            <div class="text-xs text-blue-300">
                                                Fee: {{ transaction.fee }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${transaction.status_badge.bgColor}; color: ${transaction.status_badge.color}`"
                                        >
                                            {{ transaction.status_badge.text }}
                                        </span>
                                        <div v-if="transaction.payment_reference" class="text-xs text-blue-300 mt-1">
                                            Ref: {{ transaction.payment_reference }}
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td class="py-4 px-4 text-blue-200 text-sm">
                                        {{ transaction.created_at }}
                                        <div v-if="transaction.completed_at" class="text-xs text-green-300">
                                            Completed: {{ transaction.completed_at }}
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                @click="viewTransaction(transaction.id)"
                                                class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                            >
                                                <Eye :size="14" />
                                                <span>View</span>
                                            </button>
                                            
                                            <button 
                                                v-if="transaction.status === 'pending'"
                                                @click="cancelTransaction(transaction.id)"
                                                class="text-red-400 hover:text-red-300 text-sm flex items-center space-x-1"
                                            >
                                                <X :size="14" />
                                                <span>Cancel</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="transactions.links && transactions.links.length > 3" class="mt-6">
                            <div class="flex items-center justify-center space-x-2">
                                <template v-for="link in transactions.links" :key="link.label">
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
                </div>

                <!-- Transaction Summary -->
                <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">Current Balance</h3>
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                        <div class="mb-4 lg:mb-0">
                            <div class="text-3xl lg:text-4xl font-bold text-white mb-2">KES {{ formatCurrency(balance) }}</div>
                            <p class="text-green-300">Available for transactions</p>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                            <Link 
                                href="/wallet/deposit"
                                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-center text-sm"
                            >
                                Deposit
                            </Link>
                            <Link 
                                href="/wallet/withdraw"
                                class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-center text-sm"
                            >
                                Withdraw
                            </Link>
                            <Link 
                                href="/wallet"
                                class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-center text-sm lg:col-span-1"
                            >
                                Back to Wallet
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Wallet, History, TrendingUp, TrendingDown, Repeat,
    Search, Filter, CheckCircle, X, Download, Loader2,
    FileText, PlusCircle, Eye, ArrowDownCircle, ArrowUpCircle,
    Repeat as RepeatIcon, CreditCard, FileText as FileTextIcon, DollarSign
} from 'lucide-vue-next';

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    typeOptions: {
        type: Object,
        default: () => ({})
    },
    statusOptions: {
        type: Object,
        default: () => ({})
    },
    balance: {
        type: [Number, String],
        default: 0
    }
});

const loading = ref(false);
const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const status = ref(props.filters.status || '');
let debounceTimer = null;

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getTransactionIcon = (iconName) => {
    const icons = {
        'arrow-down-circle': ArrowDownCircle,
        'arrow-up-circle': ArrowUpCircle,
        'repeat': RepeatIcon,
        'credit-card': CreditCard,
        'file-text': FileTextIcon,
        'dollar-sign': DollarSign,
    };
    return icons[iconName] || ArrowDownCircle;
};

const debouncedFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        filterTransactions();
    }, 500);
};

const filterTransactions = () => {
    loading.value = true;
    
    const params = {};
    if (search.value) params.search = search.value;
    if (type.value) params.type = type.value;
    if (status.value) params.status = status.value;
    
    router.get('/wallet/history', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        }
    });
};

const clearFilters = () => {
    search.value = '';
    type.value = '';
    status.value = '';
    filterTransactions();
};

const exportHistory = () => {
    // In a real app, this would trigger a CSV export
    alert('Export feature would generate a CSV file with all transactions');
};

const viewTransaction = (transactionId) => {
    // In a real app, this would open a transaction details modal
    alert(`View transaction ${transactionId} details`);
};

const cancelTransaction = async (transactionId) => {
    if (!confirm('Are you sure you want to cancel this pending transaction?')) {
        return;
    }

    try {
        // In a real app, you would have an API endpoint to cancel transactions
        alert('Transaction cancellation would be processed here');
    } catch (error) {
        console.error('Cancel transaction error:', error);
        alert('Failed to cancel transaction');
    }
};

onMounted(() => {
    console.log('History component mounted');
});
</script>