<template>
    <DashboardLayout title="Loans">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <CreditCard :size="16" class="text-purple-400" />
            <span>Loans</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Loan Services</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Quick Loan Services</h1>
                    <p class="text-purple-300">Get instant loans with competitive rates. Application fee: KES 200</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Borrowed -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <DollarSign :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Borrowed</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_borrowed || 0) }}</p>
                        <p class="text-sm text-purple-300">All time borrowing</p>
                    </div>
                    
                    <!-- Total Repaid -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CheckCircle :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Total Repaid</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(stats.total_repaid || 0) }}</p>
                        <p class="text-sm text-green-300">Amount repaid</p>
                    </div>
                    
                    <!-- Active Loans -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CreditCard :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Active Loans</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.active_loans || 0 }}</p>
                        <p class="text-sm text-blue-300">Currently active</p>
                    </div>
                    
                    <!-- Application Fee -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <AlertCircle :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Application Fee</h3>
                        </div>
                        <p class="text-3xl font-bold text-yellow-400 mb-2">KES 200</p>
                        <p class="text-sm text-yellow-300">Non-refundable fee</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Apply for Loan -->
                    <Link 
                        href="/loans/apply"
                        :class="canApply ? 'hover:border-green-500' : 'opacity-50 cursor-not-allowed'"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3 mb-3">
                            <FilePlus :size="24" :class="canApply ? 'text-green-400 group-hover:text-green-300' : 'text-gray-400'" />
                            <h3 class="text-lg font-bold text-white">Apply for Loan</h3>
                        </div>
                        <p class="text-white mb-4">Get instant approval for loans up to KES 50,000</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-green-400">KES 200 fee required</span>
                            <span class="text-green-400 group-hover:text-green-300">Apply Now →</span>
                        </div>
                        <div v-if="!canApply" class="mt-2 text-sm text-red-400">
                            You have an active loan. Repay first to apply for a new one.
                        </div>
                        <div v-if="hasPendingApplication" class="mt-2 text-sm text-yellow-400">
                            You have a pending application.
                        </div>
                    </Link>
                    
                    <!-- Loan Calculator -->
                    <Link 
                        href="/loans/calculator"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3 mb-3">
                            <Calculator :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <h3 class="text-lg font-bold text-white group-hover:text-blue-300">Loan Calculator</h3>
                        </div>
                        <p class="text-white mb-4">Calculate your monthly payments and interest</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-blue-300">Plan your loan</span>
                            <span class="text-blue-400 group-hover:text-blue-300">Calculate →</span>
                        </div>
                    </Link>
                    
                    <!-- My Loans -->
                    <Link 
                        href="/loans/my-loans"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3 mb-3">
                            <ListChecks :size="24" class="text-purple-400 group-hover:text-purple-300" />
                            <h3 class="text-lg font-bold text-white group-hover:text-purple-300">My Loans</h3>
                        </div>
                        <p class="text-white mb-4">View and manage all your loans</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-purple-300">{{ stats.total_loans || 0 }} loan(s)</span>
                            <span class="text-purple-400 group-hover:text-purple-300">View All →</span>
                        </div>
                    </Link>
                </div>

                <!-- Active Loans -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Active Loans</h2>
                        <Link 
                            href="/loans/my-loans"
                            class="text-purple-400 hover:text-purple-300 text-sm"
                        >
                            View All
                        </Link>
                    </div>

                    <div v-if="activeLoans.length === 0" class="text-center py-12 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl">
                        <CreditCard :size="48" class="mx-auto text-purple-400 mb-3" />
                        <h4 class="text-lg font-bold text-white mb-2">No Active Loans</h4>
                        <p class="text-purple-300 mb-4">You don't have any active loans at the moment</p>
                        <Link 
                            v-if="canApply"
                            href="/loans/apply"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <FilePlus :size="16" />
                            <span>Apply for Loan</span>
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="loan in activeLoans" 
                            :key="loan.id"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <span 
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :style="`background-color: ${loan.status_badge.bgColor}; color: ${loan.status_badge.color}`"
                                >
                                    {{ loan.status_badge.text }}
                                </span>
                                <span class="text-xs text-blue-300">{{ loan.term_months }} months</span>
                            </div>
                            
                            <h3 class="text-lg font-bold text-white mb-2">{{ loan.amount }}</h3>
                            <p class="text-sm text-blue-300 mb-4">{{ loan.purpose || 'General Purpose' }}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-300">Monthly Payment</span>
                                    <span class="font-bold text-white">{{ loan.monthly_payment }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-300">Remaining Balance</span>
                                    <span class="font-bold text-white">{{ loan.balance }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-blue-300">Due Date</span>
                                    <span class="font-bold text-white">{{ loan.due_date }}</span>
                                </div>
                            </div>
                            
                            <div v-if="loan.days_until_due !== null" class="mb-4">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-blue-300">Days until due</span>
                                    <span :class="loan.days_until_due < 7 ? 'text-red-400' : 'text-green-400'">
                                        {{ loan.days_until_dose }} days
                                    </span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2">
                                    <div 
                                        :class="loan.days_until_due < 7 ? 'bg-red-500' : 'bg-blue-500'"
                                        class="h-2 rounded-full"
                                        :style="`width: ${Math.max(0, Math.min(100, 100 - (loan.days_until_dose / 30 * 100)))}%`"
                                    ></div>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <Link 
                                    :href="`/loans/${loan.id}/repay`"
                                    class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-4 rounded-lg text-center text-sm transition-all duration-200"
                                >
                                    Make Payment
                                </Link>
                                <Link 
                                    href="/loans/my-loans"
                                    class="flex-1 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 py-2 px-4 rounded-lg text-center text-sm transition-all duration-200"
                                >
                                    Details
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Features -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">Why Choose Our Loans?</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="flex items-start space-x-3">
                            <Zap :size="20" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-bold text-white text-sm">Quick Approval</h4>
                                <p class="text-blue-300 text-xs">Get approved within 24 hours</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <Percent :size="20" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-bold text-white text-sm">Low Interest Rates</h4>
                                <p class="text-blue-300 text-xs">Only 5% interest rate per annum</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <Shield :size="20" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <div>
                                <h4 class="font-bold text-white text-sm">Secure & Safe</h4>
                                <p class="text-blue-300 text-xs">Your data is protected and secure</p>
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
    CreditCard, DollarSign, CheckCircle, AlertCircle,
    FilePlus, Calculator, ListChecks, Zap, Percent, Shield
} from 'lucide-vue-next';

const props = defineProps({
    activeLoans: {
        type: Array,
        default: () => []
    },
    canApply: {
        type: Boolean,
        default: false
    },
    hasPendingApplication: {
        type: Boolean,
        default: false
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

const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};
</script>