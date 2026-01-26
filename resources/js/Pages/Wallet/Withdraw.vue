<template>
    <DashboardLayout title="Withdraw Funds">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Wallet :size="16" class="text-blue-400" />
            <span>Wallet</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Withdraw Funds</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Withdraw Funds</h1>
                    <p class="text-blue-300">Withdraw funds from your wallet to your preferred account</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Withdrawal Form</h3>
                            
                            <form @submit.prevent="submitWithdrawal">
                                <!-- Withdrawal Amount -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-blue-300 mb-2">
                                        <DollarSign :size="16" class="inline mr-2" />
                                        Withdrawal Amount (KES)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400">KES</span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            :min="min_withdrawal"
                                            :max="Math.min(max_withdrawal, balance)"
                                            required
                                            class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl pl-16 pr-4 py-3 focus:outline-none focus:border-blue-400"
                                            placeholder="Enter amount"
                                        />
                                    </div>
                                    <div class="flex justify-between mt-2">
                                        <span class="text-xs text-blue-400">Min: KES {{ formatCurrency(min_withdrawal) }}</span>
                                        <span class="text-xs text-blue-400">Max: KES {{ formatCurrency(Math.min(max_withdrawal, balance)) }}</span>
                                    </div>
                                    
                                    <!-- Quick Amounts -->
                                    <div class="grid grid-cols-3 gap-2 mt-3">
                                        <button
                                            v-for="amount in quickAmounts"
                                            :key="amount"
                                            type="button"
                                            @click="form.amount = amount"
                                            :class="form.amount === amount ? 'bg-blue-600 text-white' : 'bg-black/30 text-blue-300'"
                                            class="border border-blue-500/30 rounded-lg py-2 text-center text-sm transition-all duration-200"
                                        >
                                            KES {{ formatCurrency(amount) }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Withdrawal Method -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-blue-300 mb-2">
                                        <CreditCard :size="16" class="inline mr-2" />
                                        Withdrawal Method
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <button
                                            v-for="method in withdrawalMethods"
                                            :key="method.id"
                                            type="button"
                                            @click="form.withdrawal_method = method.id"
                                            :class="form.withdrawal_method === method.id ? 'bg-blue-600 text-white border-blue-500' : 'bg-black/30 text-blue-300 border-blue-500/30'"
                                            class="border rounded-lg p-4 text-center transition-all duration-200"
                                        >
                                            <component :is="method.icon" :size="24" class="mx-auto mb-2" />
                                            <div class="font-bold">{{ method.name }}</div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Withdrawal Details -->
                                <div class="mb-6">
                                    <!-- M-Pesa Details -->
                                    <div v-if="form.withdrawal_method === 'mpesa'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                                M-Pesa Phone Number
                                            </label>
                                            <input
                                                v-model="form.phone_number"
                                                type="tel"
                                                required
                                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                                placeholder="07XXXXXXXX"
                                            />
                                        </div>
                                        <div class="p-3 bg-green-500/10 border border-green-500/30 rounded-lg text-sm text-green-300">
                                            <CheckCircle :size="16" class="inline mr-1" />
                                            Funds will be sent to this M-Pesa number within 5-10 minutes
                                        </div>
                                    </div>

                                    <!-- Bank Details -->
                                    <div v-else-if="form.withdrawal_method === 'bank'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                                Bank Name
                                            </label>
                                            <input
                                                v-model="form.bank_name"
                                                type="text"
                                                required
                                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                                placeholder="Enter bank name"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                                Account Number
                                            </label>
                                            <input
                                                v-model="form.account_number"
                                                type="text"
                                                required
                                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                                placeholder="Enter account number"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                                Account Name
                                            </label>
                                            <input
                                                v-model="form.account_name"
                                                type="text"
                                                required
                                                class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                                                placeholder="Enter account name"
                                            />
                                        </div>
                                        <div class="p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-sm text-yellow-300">
                                            <Clock :size="16" class="inline mr-1" />
                                            Bank transfers may take 1-2 business days to process
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex space-x-3">
                                    <button
                                        type="submit"
                                        :disabled="loading || !form.withdrawal_method || totalAmount > balance"
                                        class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="loading" class="animate-spin" :size="20" />
                                        <DollarSign v-else :size="20" />
                                        <span>{{ loading ? 'Processing...' : 'Withdraw Now' }}</span>
                                    </button>
                                    
                                    <Link
                                        href="/wallet"
                                        class="flex-1 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 py-3 px-6 rounded-lg text-center transition-all duration-200"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="lg:col-span-1">
                        <!-- Withdrawal Summary -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Withdrawal Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-green-300">Withdrawal Amount</span>
                                    <span class="font-bold text-white">KES {{ formatCurrency(form.amount || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-green-300">Processing Fee</span>
                                    <span class="font-bold text-red-400">- KES {{ withdrawal_fee }}</span>
                                </div>
                                
                                <div class="pt-3 border-t border-green-500/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-green-300">Total Deducted</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(totalAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-green-300">You Receive</span>
                                        <span class="font-bold text-green-400">KES {{ formatCurrency(form.amount || 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-green-300">New Balance</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(newBalance) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Balance -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Current Balance</h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(balance) }}</div>
                                <p class="text-sm text-purple-300">Available for withdrawal</p>
                            </div>
                        </div>

                        <!-- Insufficient Balance Warning -->
                        <div v-if="totalAmount > balance" class="bg-gradient-to-br from-red-900/30 to-red-800/30 backdrop-blur-xl border border-red-500/30 rounded-2xl p-6 shadow-2xl shadow-red-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <AlertCircle :size="24" class="text-red-400" />
                                <h3 class="text-lg font-bold text-white">Insufficient Balance</h3>
                            </div>
                            <p class="text-red-300 mb-3">
                                You need KES {{ formatCurrency(totalAmount) }} for this withdrawal.
                                Your current balance is KES {{ formatCurrency(balance) }}.
                            </p>
                            <Link 
                                href="/wallet/deposit"
                                class="bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                            >
                                <PlusCircle :size="16" />
                                <span>Deposit Funds</span>
                            </Link>
                        </div>

                        <!-- Processing Time -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Processing Time</h3>
                            <ul class="space-y-2 text-sm text-blue-200">
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>M-Pesa: 5-10 minutes</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Bank Transfer: 1-2 business days</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>All withdrawals subject to KES 50 fee</span>
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
    Wallet, DollarSign, CreditCard, Loader2,
    AlertCircle, CheckCircle, Clock, PlusCircle,
    Phone, Building
} from 'lucide-vue-next';

const props = defineProps({
    balance: {
        type: [Number, String],
        default: 0
    },
    min_withdrawal: {
        type: Number,
        default: 500
    },
    max_withdrawal: {
        type: Number,
        default: 50000
    },
    withdrawal_fee: {
        type: Number,
        default: 50
    }
});

const loading = ref(false);
const quickAmounts = [500, 1000, 2000, 5000, 10000, 20000];

const withdrawalMethods = [
    { id: 'mpesa', name: 'M-Pesa', icon: Phone },
    { id: 'bank', name: 'Bank Transfer', icon: Building },
];

const form = reactive({
    amount: 1000,
    withdrawal_method: 'mpesa',
    phone_number: '',
    bank_name: '',
    account_number: '',
    account_name: '',
});

// Calculate total amount
const totalAmount = computed(() => {
    const amount = parseFloat(form.amount) || 0;
    return amount + props.withdrawal_fee;
});

const newBalance = computed(() => {
    return parseFloat(props.balance) - totalAmount.value;
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const submitWithdrawal = async () => {
    if (!form.amount || form.amount < props.min_withdrawal || form.amount > props.max_withdrawal) {
        alert(`Withdrawal amount must be between KES ${props.min_withdrawal.toLocaleString()} and KES ${props.max_withdrawal.toLocaleString()}`);
        return;
    }

    if (!form.withdrawal_method) {
        alert('Please select a withdrawal method');
        return;
    }

    if (totalAmount.value > parseFloat(props.balance)) {
        alert('Insufficient balance for this withdrawal');
        return;
    }

    const confirmMessage = `Are you sure you want to withdraw KES ${formatCurrency(form.amount)} via ${form.withdrawal_method.toUpperCase()}? A fee of KES ${props.withdrawal_fee} will be charged.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    loading.value = true;

    try {
        await router.post('/wallet/withdraw', form, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller redirect
            },
            onError: (errors) => {
                console.error('Withdrawal error:', errors);
                alert('Failed to process withdrawal. Please check the form and try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Withdrawal error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>