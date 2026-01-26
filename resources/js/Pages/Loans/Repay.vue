<template>
    <DashboardLayout title="Repay Loan">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <CreditCard :size="16" class="text-purple-400" />
            <span>Loans</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Repay Loan</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-md mx-auto">
                <!-- Repayment Form -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h2 class="text-xl font-bold text-white mb-6">Make Loan Payment</h2>
                    
                    <!-- Loan Info -->
                    <div class="mb-6">
                        <div class="text-center mb-4">
                            <div class="text-3xl font-bold text-white mb-1">{{ loan.amount }}</div>
                            <div class="text-sm text-blue-300">Loan Balance: {{ loan.balance }}</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="text-center">
                                <div class="text-blue-300">Monthly Payment</div>
                                <div class="font-bold text-white">{{ loan.monthly_payment }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-blue-300">Due Date</div>
                                <div class="font-bold text-white">{{ loan.due_date }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Amount -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-green-300 mb-2">
                            Payment Amount (KES)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-green-400">KES</span>
                            <input
                                v-model="paymentAmount"
                                type="number"
                                :min="100"
                                :max="parseFloat(loan.balance.replace(/[^0-9.]/g, ''))"
                                step="100"
                                required
                                class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl pl-16 pr-4 py-3 focus:outline-none focus:border-green-400"
                                placeholder="Enter amount"
                            />
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xs text-green-400">Min: KES 100</span>
                            <span class="text-xs text-green-400">Max: {{ loan.balance }}</span>
                        </div>
                        
                        <!-- Quick Amounts -->
                        <div class="grid grid-cols-3 gap-2 mt-3">
                            <button
                                v-for="amount in quickAmounts"
                                :key="amount"
                                type="button"
                                @click="paymentAmount = amount"
                                :class="paymentAmount === amount ? 'bg-green-600 text-white' : 'bg-black/30 text-green-300'"
                                class="border border-green-500/30 rounded-lg py-2 text-center text-sm transition-all duration-200"
                            >
                                KES {{ amount.toLocaleString() }}
                            </button>
                        </div>
                    </div>

                    <!-- Current Balance -->
                    <div class="mb-6 p-3 bg-black/20 border border-blue-500/30 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-blue-300">Your Balance</span>
                            <span class="font-bold text-white">KES {{ formatCurrency(userBalance) }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm text-blue-300">After Payment</span>
                            <span class="font-bold text-green-400">KES {{ formatCurrency(userBalance - paymentAmount) }}</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="space-y-3">
                        <button
                            @click="submitPayment"
                            :disabled="loading || parseFloat(paymentAmount) > parseFloat(userBalance) || parseFloat(paymentAmount) <= 0"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="loading" class="animate-spin" :size="20" />
                            <DollarSign v-else :size="20" />
                            <span>{{ loading ? 'Processing...' : 'Make Payment' }}</span>
                        </button>
                        
                        <Link
                            href="/loans/my-loans"
                            class="w-full border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 py-3 px-6 rounded-lg text-center block transition-all duration-200"
                        >
                            Cancel
                        </Link>
                    </div>
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
    CreditCard, DollarSign, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    loan: {
        type: Object,
        required: true
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const loading = ref(false);
const paymentAmount = ref(0);
const quickAmounts = [1000, 5000, 10000];

// Calculate default payment amount (monthly payment or balance, whichever is smaller)
const defaultPaymentAmount = computed(() => {
    const monthly = parseFloat(props.loan.monthly_payment?.replace(/[^0-9.]/g, '') || 0);
    const balance = parseFloat(props.loan.balance?.replace(/[^0-9.]/g, '') || 0);
    const userBal = parseFloat(props.userBalance || 0);
    
    let amount = Math.min(monthly, balance);
    amount = Math.min(amount, userBal);
    
    return amount > 0 ? amount : 1000; // Default to 1000 if no amount found
});

// Set default payment amount on mount
paymentAmount.value = defaultPaymentAmount.value;

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const submitPayment = async () => {
    const amount = parseFloat(paymentAmount.value);
    
    if (amount <= 0) {
        alert('Please enter a valid payment amount');
        return;
    }
    
    if (amount > parseFloat(props.userBalance)) {
        alert('Insufficient balance for this payment');
        return;
    }
    
    const loanBalance = parseFloat(props.loan.balance?.replace(/[^0-9.]/g, '') || 0);
    if (amount > loanBalance) {
        alert('Payment amount cannot exceed loan balance');
        return;
    }
    
    if (!confirm(`Are you sure you want to make a payment of KES ${formatCurrency(amount)} for this loan?`)) {
        return;
    }
    
    loading.value = true;
    
    try {
        await router.post(`/loans/${props.loan.id}/repay`, {
            amount: amount
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller
            },
            onError: (errors) => {
                console.error('Payment error:', errors);
                alert('Failed to process payment. Please try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Payment error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>