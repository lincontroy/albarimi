<template>
    <DashboardLayout title="Apply for Loan">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <CreditCard :size="16" class="text-purple-400" />
            <span>Loans</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Apply for Loan</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Apply for Loan</h1>
                    <p class="text-purple-300">Fill in the details below to apply for a quick loan</p>
                </div>

                <!-- Application Fee Alert -->
                <div v-if="!hasEnoughBalance" class="mb-6 bg-gradient-to-br from-red-900/30 to-red-800/30 backdrop-blur-xl border border-red-500/30 rounded-2xl p-6 shadow-2xl shadow-red-500/20">
                    <div class="flex items-center space-x-3 mb-3">
                        <AlertCircle :size="24" class="text-red-400" />
                        <h3 class="text-lg font-bold text-white">Insufficient Balance</h3>
                    </div>
                    <p class="text-red-300 mb-3">
                        You need at least KES {{ applicationFee }} for the application fee. 
                        Your current balance is KES {{ formatCurrency(userBalance) }}.
                    </p>
                    <Link 
                        href="/wallet/deposit"
                        class="bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                    >
                        <PlusCircle :size="16" />
                        <span>Deposit Funds</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Loan Application Form</h3>
                            
                            <form @submit.prevent="submitApplication">
                                <!-- Loan Amount -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <DollarSign :size="16" class="inline mr-2" />
                                        Loan Amount (KES)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-purple-400">KES</span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            :min="minAmount"
                                            :max="maxAmount"
                                            required
                                            :disabled="!hasEnoughBalance"
                                            class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl pl-16 pr-4 py-3 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                            placeholder="Enter amount"
                                        />
                                    </div>
                                    <div class="flex justify-between mt-2">
                                        <span class="text-xs text-purple-400">Min: KES {{ formatCurrency(minAmount) }}</span>
                                        <span class="text-xs text-purple-400">Max: KES {{ formatCurrency(maxAmount) }}</span>
                                    </div>
                                </div>

                                <!-- Loan Term -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <Calendar :size="16" class="inline mr-2" />
                                        Loan Term (Months)
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button
                                            v-for="term in termOptions"
                                            :key="term"
                                            type="button"
                                            @click="form.term_months = term"
                                            :class="form.term_months === term ? 'bg-purple-600 text-white border-purple-500' : 'bg-black/30 text-purple-300 border-purple-500/30'"
                                            class="border rounded-lg py-2 text-center transition-all duration-200"
                                        >
                                            {{ term }} months
                                        </button>
                                    </div>
                                </div>

                                <!-- Purpose -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <FileText :size="16" class="inline mr-2" />
                                        Loan Purpose (Optional)
                                    </label>
                                    <textarea
                                        v-model="form.purpose"
                                        :disabled="!hasEnoughBalance"
                                        rows="3"
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                        placeholder="What will you use the loan for?"
                                    ></textarea>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="mb-6">
                                    <div class="flex items-start space-x-2">
                                        <input
                                            v-model="form.agreeTerms"
                                            type="checkbox"
                                            id="terms"
                                            :disabled="!hasEnoughBalance"
                                            required
                                            class="mt-1"
                                        />
                                        <label for="terms" class="text-sm text-purple-300">
                                            I agree to the loan terms and conditions. I understand that a non-refundable application fee of KES {{ applicationFee }} will be deducted from my balance.
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex space-x-3">
                                    <button
                                        type="submit"
                                        :disabled="!hasEnoughBalance || loading"
                                        class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="loading" class="animate-spin" :size="20" />
                                        <FilePlus v-else :size="20" />
                                        <span>{{ loading ? 'Processing...' : 'Submit Application' }}</span>
                                    </button>
                                    
                                    <Link
                                        href="/loans"
                                        class="flex-1 border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 py-3 px-6 rounded-lg text-center transition-all duration-200"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="lg:col-span-1">
                        <!-- Loan Summary -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Loan Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Loan Amount</span>
                                    <span class="font-bold text-white">KES {{ formatCurrency(form.amount || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Term</span>
                                    <span class="font-bold text-white">{{ form.term_months }} months</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Interest Rate</span>
                                    <span class="font-bold text-white">{{ interestRate }}% p.a.</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Application Fee</span>
                                    <span class="font-bold text-red-400">KES {{ applicationFee }}</span>
                                </div>
                                
                                <div class="pt-3 border-t border-blue-500/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-blue-300">Estimated Monthly Payment</span>
                                        <span class="font-bold text-green-400">KES {{ formatCurrency(estimatedMonthlyPayment) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-blue-300">Total Repayable</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(estimatedTotalAmount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Requirements</h3>
                            
                            <ul class="space-y-2">
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Valid account with sufficient balance for application fee</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>No active or pending loans</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Loan amount between KES 1,000 - 50,000</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Term between 3 - 36 months</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    CreditCard, AlertCircle, PlusCircle, DollarSign,
    Calendar, FileText, FilePlus, Loader2, CheckCircle
} from 'lucide-vue-next';

const props = defineProps({
    applicationFee: {
        type: Number,
        default: 200
    },
    hasEnoughBalance: {
        type: Boolean,
        default: false
    },
    userBalance: {
        type: [Number, String],
        default: 0
    },
    minAmount: {
        type: Number,
        default: 1000
    },
    maxAmount: {
        type: Number,
        default: 50000
    },
    defaultTerm: {
        type: Number,
        default: 12
    },
    interestRate: {
        type: Number,
        default: 5.0
    }
});

const loading = ref(false);
const termOptions = [3, 6, 12, 24, 36];

const form = reactive({
    amount: 10000,
    term_months: props.defaultTerm,
    purpose: '',
    agreeTerms: false,
});

// Calculate estimated payments
const estimatedMonthlyPayment = computed(() => {
    const principal = parseFloat(form.amount) || 0;
    const termMonths = form.term_months;
    const monthlyInterestRate = (props.interestRate / 100) / 12;
    
    if (monthlyInterestRate > 0 && termMonths > 0) {
        const payment = principal * (monthlyInterestRate * Math.pow(1 + monthlyInterestRate, termMonths)) / 
                       (Math.pow(1 + monthlyInterestRate, termMonths) - 1);
        return Math.round(payment);
    }
    return 0;
});

const estimatedTotalAmount = computed(() => {
    return estimatedMonthlyPayment.value * form.term_months;
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const submitApplication = async () => {
    if (!props.hasEnoughBalance) {
        alert('Insufficient balance for application fee');
        return;
    }

    if (!form.agreeTerms) {
        alert('You must agree to the terms and conditions');
        return;
    }

    if (form.amount < props.minAmount || form.amount > props.maxAmount) {
        alert(`Loan amount must be between KES ${props.minAmount.toLocaleString()} and KES ${props.maxAmount.toLocaleString()}`);
        return;
    }

    if (!confirm(`Are you sure you want to apply for a loan of KES ${formatCurrency(form.amount)}? KES ${props.applicationFee} application fee will be deducted.`)) {
        return;
    }

    loading.value = true;

    try {
        await router.post('/loans/apply', form, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller redirect
            },
            onError: (errors) => {
                console.error('Loan application error:', errors);
                alert('Failed to submit application. Please check the form and try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Loan application error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>