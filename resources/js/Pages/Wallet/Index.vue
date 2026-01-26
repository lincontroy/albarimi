<template>
    <DashboardLayout title="Wallet">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <Wallet :size="16" class="text-green-400" />
            <span>Wallet</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Dashboard</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">My Wallet</h1>
                    <p class="text-green-300">Manage your funds and transactions</p>
                </div>

                <!-- Balance Card -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-8 shadow-2xl shadow-green-500/20">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <div class="mb-6 lg:mb-0">
                                <h2 class="text-lg font-medium text-green-300 mb-2">Available Balance</h2>
                                <div class="text-4xl lg:text-5xl font-bold text-white mb-2">KES {{ formatCurrency(balance) }}</div>
                                <p class="text-green-300">Total funds available for use</p>
                            </div>
                            
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                                <Link 
                                    href="/wallet/deposit"
                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                >
                                    <PlusCircle :size="18" />
                                    <span>Deposit</span>
                                </Link>
                                
                                <Link 
                                    href="/wallet/withdraw"
                                    class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                >
                                    <MinusCircle :size="18" />
                                    <span>Withdraw</span>
                                </Link>
                                
                                <Link 
                                    href="/wallet/transfer"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 lg:col-span-1"
                                >
                                    <Repeat :size="18" />
                                    <span>Transfer</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Deposits -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <TrendingUp :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Total Deposits</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_deposits || 0) }}</p>
                        <p class="text-sm text-green-300">All time deposits</p>
                    </div>

                    <!-- Total Withdrawals -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <TrendingDown :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Total Withdrawals</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_withdrawals || 0) }}</p>
                        <p class="text-sm text-blue-300">All time withdrawals</p>
                    </div>

                    <!-- Total Transfers -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Repeat :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Transfers</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_transfers || 0) }}</p>
                        <p class="text-sm text-purple-300">All time transfers</p>
                    </div>

                    <!-- Pending -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Clock :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Pending</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.pending_transactions || 0 }}</p>
                        <p class="text-sm text-yellow-300">Pending transactions</p>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Recent Transactions -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-white">Recent Transactions</h2>
                                <Link 
                                    href="/wallet/history"
                                    class="text-blue-400 hover:text-blue-300 text-sm"
                                >
                                    View All →
                                </Link>
                            </div>

                            <div v-if="recentTransactions.length === 0" class="text-center py-8">
                                <History :size="48" class="mx-auto text-blue-400 mb-3" />
                                <h4 class="text-lg font-bold text-white mb-2">No Transactions Yet</h4>
                                <p class="text-blue-300">Your transaction history will appear here</p>
                            </div>

                            <div v-else class="space-y-3">
                                <div 
                                    v-for="transaction in recentTransactions" 
                                    :key="transaction.id"
                                    class="flex items-center justify-between p-4 border border-blue-500/20 rounded-xl hover:bg-black/20 transition-all duration-200"
                                >
                                    <div class="flex items-center space-x-3">
                                        <div 
                                            class="w-10 h-10 rounded-full flex items-center justify-center"
                                            :style="`background-color: ${transaction.type_badge.bgColor}`"
                                        >
                                            <component 
                                                :is="getTransactionIcon(transaction.type_badge.icon)" 
                                                :size="18" 
                                                :style="`color: ${transaction.type_badge.color}`"
                                            />
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-white text-sm">{{ transaction.type_badge.text }}</h4>
                                            <p class="text-xs text-blue-300">{{ transaction.description }}</p>
                                            <p class="text-xs text-blue-400">{{ transaction.created_at }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right">
                                        <div 
                                            class="font-bold text-sm mb-1"
                                            :class="transaction.is_positive ? 'text-green-400' : 'text-red-400'"
                                        >
                                            {{ transaction.is_positive ? '+' : '-' }}{{ transaction.net_amount }}
                                        </div>
                                        <span 
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                            :style="`background-color: ${transaction.status_badge.bgColor}; color: ${transaction.status_badge.color}`"
                                        >
                                            {{ transaction.status_badge.text }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Quick Actions -->
                    <div class="lg:col-span-1">
                        <!-- Quick Actions -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
                            
                            <div class="space-y-3">
                                <Link 
                                    href="/loans"
                                    class="flex items-center justify-between p-3 border border-purple-500/30 rounded-lg hover:bg-purple-500/10 transition-all duration-200 group"
                                >
                                    <div class="flex items-center space-x-3">
                                        <CreditCard :size="20" class="text-purple-400" />
                                        <span class="text-white group-hover:text-purple-300">Apply for Loan</span>
                                    </div>
                                    <ArrowRight :size="16" class="text-purple-400 group-hover:text-purple-300" />
                                </Link>
                                
                                <Link 
                                    href="/jobs"
                                    class="flex items-center justify-between p-3 border border-blue-500/30 rounded-lg hover:bg-blue-500/10 transition-all duration-200 group"
                                >
                                    <div class="flex items-center space-x-3">
                                        <Briefcase :size="20" class="text-blue-400" />
                                        <span class="text-white group-hover:text-blue-300">Browse Jobs</span>
                                    </div>
                                    <ArrowRight :size="16" class="text-blue-400 group-hover:text-blue-300" />
                                </Link>
                                
                                <Link 
                                    href="/wallet/history"
                                    class="flex items-center justify-between p-3 border border-yellow-500/30 rounded-lg hover:bg-yellow-500/10 transition-all duration-200 group"
                                >
                                    <div class="flex items-center space-x-3">
                                        <History :size="20" class="text-yellow-400" />
                                        <span class="text-white group-hover:text-yellow-300">View History</span>
                                    </div>
                                    <ArrowRight :size="16" class="text-yellow-400 group-hover:text-yellow-300" />
                                </Link>
                            </div>
                        </div>

                        <!-- Fee Information -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Fee Information</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-yellow-300">Deposit Fee</span>
                                    <span class="font-bold text-white">1.5%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-yellow-300">Withdrawal Fee</span>
                                    <span class="font-bold text-white">KES 50</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-yellow-300">Transfer Fee</span>
                                    <span class="font-bold text-white">0.5%</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-yellow-300">Loan App Fee</span>
                                    <span class="font-bold text-white">KES 200</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Wallet, PlusCircle, MinusCircle, Repeat, TrendingUp,
    TrendingDown, Clock, History, CreditCard, Briefcase,
    ArrowRight, ArrowDownCircle, ArrowUpCircle, Repeat as RepeatIcon,
    CreditCard as CreditCardIcon, FileText, DollarSign
} from 'lucide-vue-next';

const props = defineProps({
    balance: {
        type: [Number, String],
        default: 0
    },
    recentTransactions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    }
});

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
        'credit-card': CreditCardIcon,
        'file-text': FileText,
        'dollar-sign': DollarSign,
    };
    return icons[iconName] || ArrowDownCircle;
};
</script>