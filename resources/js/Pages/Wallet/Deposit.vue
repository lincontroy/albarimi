<template>
    <DashboardLayout title="Deposit Funds">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <Wallet :size="16" class="text-green-400" />
            <span>Wallet</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Deposit Funds</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Deposit Funds</h1>
                    <p class="text-green-300">Add funds to your wallet using various payment methods</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Deposit Form</h3>
                            
                            <form @submit.prevent="submitDeposit">
                                <!-- Deposit Amount -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-green-300 mb-2">
                                        <DollarSign :size="16" class="inline mr-2" />
                                        Deposit Amount (KES)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-green-400">KES</span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            :min="min_deposit"
                                            :max="max_deposit"
                                            required
                                            class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl pl-16 pr-4 py-3 focus:outline-none focus:border-green-400"
                                            placeholder="Enter amount"
                                        />
                                    </div>
                                    <div class="flex justify-between mt-2">
                                        <span class="text-xs text-green-400">Min: KES {{ formatCurrency(min_deposit) }}</span>
                                        <span class="text-xs text-green-400">Max: KES {{ formatCurrency(max_deposit) }}</span>
                                    </div>
                                    
                                    <!-- Quick Amounts -->
                                    <div class="grid grid-cols-3 gap-2 mt-3">
                                        <button
                                            v-for="amount in quickAmounts"
                                            :key="amount"
                                            type="button"
                                            @click="form.amount = amount"
                                            :class="form.amount === amount ? 'bg-green-600 text-white' : 'bg-black/30 text-green-300'"
                                            class="border border-green-500/30 rounded-lg py-2 text-center text-sm transition-all duration-200"
                                        >
                                            KES {{ formatCurrency(amount) }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-green-300 mb-2">
                                        <CreditCard :size="16" class="inline mr-2" />
                                        Payment Method
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <button
                                            v-for="method in paymentMethods"
                                            :key="method.id"
                                            type="button"
                                            @click="form.payment_method = method.id"
                                            :class="form.payment_method === method.id ? 'bg-green-600 text-white border-green-500' : 'bg-black/30 text-green-300 border-green-500/30'"
                                            class="border rounded-lg p-4 text-center transition-all duration-200"
                                        >
                                            <component :is="method.icon" :size="24" class="mx-auto mb-2" />
                                            <div class="font-bold">{{ method.name }}</div>
                                        </button>
                                    </div>
                                </div>

                                <!-- Payment Details -->
                                <div class="mb-6">
                                    <!-- M-Pesa Details -->
                                    <div v-if="form.payment_method === 'mpesa'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-green-300 mb-2">
                                                Phone Number
                                            </label>
                                            <input
                                                v-model="form.phone_number"
                                                type="tel"
                                                required
                                                class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-green-400"
                                                placeholder="07XXXXXXXX"
                                            />
                                        </div>
                                        <div class="p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-sm text-yellow-300">
                                            <AlertCircle :size="16" class="inline mr-1" />
                                            You will receive a prompt on your phone to confirm the payment
                                        </div>
                                    </div>

                                    <!-- Bank Details -->
                                    <div v-else-if="form.payment_method === 'bank'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-green-300 mb-2">
                                                Account Number
                                            </label>
                                            <input
                                                v-model="form.account_number"
                                                type="text"
                                                required
                                                class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-green-400"
                                                placeholder="Enter account number"
                                            />
                                        </div>
                                        <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg text-sm text-blue-300">
                                            <Info :size="16" class="inline mr-1" />
                                            Bank transfers may take 1-2 business days to process
                                        </div>
                                    </div>

                                    <!-- Card Details -->
                                    <div v-else-if="form.payment_method === 'card'" class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-green-300 mb-2">
                                                Card Number
                                            </label>
                                            <input
                                                v-model="form.card_number"
                                                type="text"
                                                required
                                                class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-green-400"
                                                placeholder="1234 5678 9012 3456"
                                            />
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-green-300 mb-2">
                                                    Expiry Date
                                                </label>
                                                <input
                                                    type="text"
                                                    class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-green-400"
                                                    placeholder="MM/YY"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-green-300 mb-2">
                                                    CVV
                                                </label>
                                                <input
                                                    type="text"
                                                    class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-green-400"
                                                    placeholder="123"
                                                />
                                            </div>
                                        </div>
                                        <div class="p-3 bg-purple-500/10 border border-purple-500/30 rounded-lg text-sm text-purple-300">
                                            <Shield :size="16" class="inline mr-1" />
                                            Your card details are encrypted and secure
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex space-x-3">
                                    <button
                                        type="submit"
                                        :disabled="loading || !form.payment_method"
                                        class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="loading" class="animate-spin" :size="20" />
                                        <CreditCard v-else :size="20" />
                                        <span>{{ loading ? 'Processing...' : 'Deposit Now' }}</span>
                                    </button>
                                    
                                    <Link
                                        href="/wallet"
                                        class="flex-1 border border-green-500/30 text-green-400 hover:bg-green-500/20 py-3 px-6 rounded-lg text-center transition-all duration-200"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="lg:col-span-1">
                        <!-- Deposit Summary -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Deposit Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Deposit Amount</span>
                                    <span class="font-bold text-white">KES {{ formatCurrency(form.amount || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Processing Fee (1.5%)</span>
                                    <span class="font-bold text-red-400">- KES {{ formatCurrency(processingFee) }}</span>
                                </div>
                                
                                <div class="pt-3 border-t border-blue-500/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-blue-300">Net Deposit</span>
                                        <span class="font-bold text-green-400">KES {{ formatCurrency(netAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-blue-300">New Balance</span>
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
                                <p class="text-sm text-purple-300">Available for use</p>
                            </div>
                        </div>

                        <!-- Security Info -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                                <Shield :size="20" class="text-yellow-400" />
                                <span>Security Notice</span>
                            </h3>
                            <ul class="space-y-2 text-sm text-blue-200">
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>All transactions are encrypted and secure</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>We never store your card details</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Deposits are processed instantly</span>
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
    AlertCircle, Info, Shield, CheckCircle,
    Phone, Building, CreditCard as CardIcon
} from 'lucide-vue-next';

const props = defineProps({
    balance: {
        type: [Number, String],
        default: 0
    },
    min_deposit: {
        type: Number,
        default: 100
    },
    max_deposit: {
        type: Number,
        default: 50000
    },
    deposit_fee_percentage: {
        type: Number,
        default: 1.5
    }
});

const loading = ref(false);
const quickAmounts = [500, 1000, 2000, 5000, 10000, 20000];

const paymentMethods = [
    { id: 'mpesa', name: 'M-Pesa', icon: Phone },
    { id: 'bank', name: 'Bank Transfer', icon: Building },
    { id: 'card', name: 'Credit/Debit Card', icon: CardIcon },
];

const form = reactive({
    amount: 1000,
    payment_method: 'mpesa',
    phone_number: '',
    account_number: '',
    card_number: '',
});

// Calculate fees and amounts
const processingFee = computed(() => {
    const amount = parseFloat(form.amount) || 0;
    return amount * (props.deposit_fee_percentage / 100);
});

const netAmount = computed(() => {
    const amount = parseFloat(form.amount) || 0;
    return amount - processingFee.value;
});

const newBalance = computed(() => {
    return parseFloat(props.balance) + netAmount.value;
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const submitDeposit = async () => {
    if (!form.amount || form.amount < props.min_deposit || form.amount > props.max_deposit) {
        alert(`Deposit amount must be between KES ${props.min_deposit.toLocaleString()} and KES ${props.max_deposit.toLocaleString()}`);
        return;
    }

    if (!form.payment_method) {
        alert('Please select a payment method');
        return;
    }

    const confirmMessage = `Are you sure you want to deposit KES ${formatCurrency(form.amount)} via ${form.payment_method.toUpperCase()}? A fee of KES ${formatCurrency(processingFee.value)} will be charged.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    loading.value = true;

    try {
        await router.post('/wallet/deposit', form, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller redirect
            },
            onError: (errors) => {
                console.error('Deposit error:', errors);
                alert('Failed to process deposit. Please check the form and try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Deposit error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>