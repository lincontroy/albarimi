<template>
    <DashboardLayout title="My Loans">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <CreditCard :size="16" class="text-purple-400" />
            <span>Loans</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">My Loans</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">My Loans</h1>
                    <p class="text-purple-300">View and manage all your loan applications</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Loans -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CreditCard :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Loans</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total || 0 }}</p>
                        <p class="text-sm text-purple-300">All loan applications</p>
                    </div>

                    <!-- Active Loans -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <TrendingUp :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Active Loans</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.active || 0 }}</p>
                        <p class="text-sm text-blue-300">Currently active</p>
                    </div>

                    <!-- Total Borrowed -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <DollarSign :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Total Borrowed</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_borrowed || 0) }}</p>
                        <p class="text-sm text-green-300">All time borrowing</p>
                    </div>

                    <!-- Total Repaid -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CheckCircle :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Total Repaid</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_repaid || 0) }}</p>
                        <p class="text-sm text-yellow-300">Amount repaid</p>
                    </div>
                </div>

                <!-- Loans Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-8">
                    <!-- Table Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">All Loan Applications</h2>
                        <div class="flex items-center space-x-2">
                            <Link 
                                href="/loans/apply"
                                class="text-green-400 hover:text-green-300 flex items-center space-x-1 text-sm"
                            >
                                <FilePlus :size="16" />
                                <span>Apply for New Loan</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-12">
                        <Loader2 class="animate-spin mx-auto text-blue-400" :size="48" />
                        <p class="text-blue-300 mt-4">Loading loans...</p>
                    </div>

                    <!-- No Loans -->
                    <div v-else-if="!loans.data || loans.data.length === 0" class="text-center py-12">
                        <CreditCard :size="48" class="mx-auto text-blue-400 mb-3" />
                        <h4 class="text-lg font-bold text-white mb-2">No Loans Found</h4>
                        <p class="text-blue-300 mb-4">You haven't applied for any loans yet</p>
                        <Link 
                            href="/loans/apply"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <FilePlus :size="16" />
                            <span>Apply for Loan</span>
                        </Link>
                    </div>

                    <!-- Loans Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-blue-500/20">
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Loan Details</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Dates</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Payment</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="loan in loans.data" 
                                    :key="loan.id"
                                    class="border-b border-blue-500/10 hover:bg-black/20 transition-colors"
                                >
                                    <!-- Loan Details -->
                                    <td class="py-4 px-4">
                                        <div>
                                            <div class="text-white font-bold mb-1">
                                                {{ loan.amount }}
                                            </div>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="text-xs text-blue-300">{{ loan.term_months }} months</span>
                                                <span class="text-xs text-blue-300">•</span>
                                                <span class="text-xs text-blue-300">{{ loan.interest_rate }}% interest</span>
                                            </div>
                                            <div v-if="loan.purpose" class="text-xs text-purple-300 mt-1">
                                                {{ loan.purpose }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${loan.status_badge.bgColor}; color: ${loan.status_badge.color}`"
                                        >
                                            {{ loan.status_badge.text }}
                                        </span>
                                        <div v-if="loan.due_date" class="text-xs text-blue-300 mt-1">
                                            Due: {{ loan.due_date }}
                                        </div>
                                    </td>

                                    <!-- Dates -->
                                    <td class="py-4 px-4 text-blue-200 text-sm">
                                        <div>Applied: {{ loan.created_at }}</div>
                                        <div v-if="loan.approved_at" class="text-green-300">
                                            Approved: {{ loan.approved_at }}
                                        </div>
                                        <div v-if="loan.disbursed_at" class="text-green-300">
                                            Disbursed: {{ loan.disbursed_at }}
                                        </div>
                                    </td>

                                    <!-- Payment Info -->
                                    <td class="py-4 px-4">
                                        <div class="space-y-1">
                                            <div class="text-sm font-bold text-white">{{ loan.monthly_payment }}/month</div>
                                            <div class="text-xs text-green-400">Paid: {{ loan.amount_paid }}</div>
                                            <div class="text-xs text-blue-300">Balance: {{ loan.balance }}</div>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col space-y-2">
                                            <Link 
                                                v-if="loan.can_repay"
                                                :href="`/loans/${loan.id}/repay`"
                                                class="text-green-400 hover:text-green-300 text-sm flex items-center space-x-1"
                                            >
                                                <DollarSign :size="14" />
                                                <span>Make Payment</span>
                                            </Link>
                                            
                                            <Link 
                                                :href="`/loans/calculator?amount=${parseFloat(loan.amount.replace(/[^0-9.]/g, ''))}&term=${loan.term_months}`"
                                                class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                            >
                                                <Calculator :size="14" />
                                                <span>View Details</span>
                                            </Link>
                                            
                                            <div v-if="loan.status === 'pending'" class="text-xs text-yellow-400">
                                                Under review
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="loans.links && loans.links.length > 3" class="mt-6">
                            <div class="flex items-center justify-center space-x-2">
                                <template v-for="link in loans.links" :key="link.label">
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

                <!-- Loan Status Guide -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">Loan Status Guide</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Pending</h4>
                                <p class="text-xs text-blue-300">Application under review</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Approved</h4>
                                <p class="text-xs text-blue-300">Loan approved, awaiting disbursement</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Active</h4>
                                <p class="text-xs text-blue-300">Loan active, repayments ongoing</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Rejected</h4>
                                <p class="text-xs text-blue-300">Application not approved</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Completed</h4>
                                <p class="text-xs text-blue-300">Loan fully repaid</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 rounded-full bg-red-700"></div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Defaulted</h4>
                                <p class="text-xs text-blue-300">Loan payments overdue</p>
                            </div>
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
    CreditCard, TrendingUp, DollarSign, CheckCircle,
    FilePlus, Loader2, Calculator
} from 'lucide-vue-next';

const props = defineProps({
    loans: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const loading = ref(false);

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

onMounted(() => {
    console.log('MyLoans component mounted');
    console.log('Loans:', props.loans);
    console.log('Stats:', props.stats);
});
</script>