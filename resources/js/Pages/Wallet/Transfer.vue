<template>
    <DashboardLayout title="Transfer Funds">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Wallet :size="16" class="text-purple-400" />
            <span>Wallet</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Transfer Funds</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Transfer Funds</h1>
                    <p class="text-purple-300">Transfer money to other users on the platform</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Transfer Form</h3>
                            
                            <form @submit.prevent="submitTransfer">
                                <!-- Recipient -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <User :size="16" class="inline mr-2" />
                                        Recipient Email
                                    </label>
                                    <div class="relative">
                                        <Mail :size="16" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-purple-400" />
                                        <input
                                            v-model="form.recipient_email"
                                            type="email"
                                            required
                                            class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl pl-12 pr-4 py-3 focus:outline-none focus:border-purple-400"
                                            placeholder="Enter recipient's email"
                                        />
                                    </div>
                                    <div v-if="recipientInfo" class="mt-2 p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                                        <div class="flex items-center space-x-2">
                                            <CheckCircle :size="16" class="text-green-400" />
                                            <span class="text-green-300">Recipient found: {{ recipientInfo.name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transfer Amount -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <DollarSign :size="16" class="inline mr-2" />
                                        Transfer Amount (KES)
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-purple-400">KES</span>
                                        <input
                                            v-model="form.amount"
                                            type="number"
                                            :min="min_transfer"
                                            :max="Math.min(max_transfer, balance)"
                                            required
                                            class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl pl-16 pr-4 py-3 focus:outline-none focus:border-purple-400"
                                            placeholder="Enter amount"
                                        />
                                    </div>
                                    <div class="flex justify-between mt-2">
                                        <span class="text-xs text-purple-400">Min: KES {{ formatCurrency(min_transfer) }}</span>
                                        <span class="text-xs text-purple-400">Max: KES {{ formatCurrency(Math.min(max_transfer, balance)) }}</span>
                                    </div>
                                    
                                    <!-- Quick Amounts -->
                                    <div class="grid grid-cols-3 gap-2 mt-3">
                                        <button
                                            v-for="amount in quickAmounts"
                                            :key="amount"
                                            type="button"
                                            @click="form.amount = amount"
                                            :class="form.amount === amount ? 'bg-purple-600 text-white' : 'bg-black/30 text-purple-300'"
                                            class="border border-purple-500/30 rounded-lg py-2 text-center text-sm transition-all duration-200"
                                        >
                                            KES {{ formatCurrency(amount) }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <FileText :size="16" class="inline mr-2" />
                                        Description (Optional)
                                    </label>
                                    <input
                                        v-model="form.description"
                                        type="text"
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400"
                                        placeholder="What is this transfer for?"
                                    />
                                </div>

                                <!-- Submit Button -->
                                <div class="flex space-x-3">
                                    <button
                                        type="submit"
                                        :disabled="loading || totalAmount > balance"
                                        class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="loading" class="animate-spin" :size="20" />
                                        <Repeat v-else :size="20" />
                                        <span>{{ loading ? 'Processing...' : 'Transfer Now' }}</span>
                                    </button>
                                    
                                    <Link
                                        href="/wallet"
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
                        <!-- Transfer Summary -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Transfer Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Transfer Amount</span>
                                    <span class="font-bold text-white">KES {{ formatCurrency(form.amount || 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Transfer Fee (0.5%)</span>
                                    <span class="font-bold text-red-400">- KES {{ formatCurrency(transferFee) }}</span>
                                </div>
                                
                                <div class="pt-3 border-t border-blue-500/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-blue-300">Total Deducted</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(totalAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-blue-300">Recipient Gets</span>
                                        <span class="font-bold text-green-400">KES {{ formatCurrency(form.amount || 0) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-blue-300">New Balance</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(newBalance) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Balance -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Current Balance</h3>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white mb-2">KES {{ formatCurrency(balance) }}</div>
                                <p class="text-sm text-green-300">Available for transfer</p>
                            </div>
                        </div>

                        <!-- Insufficient Balance Warning -->
                        <div v-if="totalAmount > balance" class="bg-gradient-to-br from-red-900/30 to-red-800/30 backdrop-blur-xl border border-red-500/30 rounded-2xl p-6 shadow-2xl shadow-red-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <AlertCircle :size="24" class="text-red-400" />
                                <h3 class="text-lg font-bold text-white">Insufficient Balance</h3>
                            </div>
                            <p class="text-red-300 mb-3">
                                You need KES {{ formatCurrency(totalAmount) }} for this transfer.
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

                        <!-- Security Info -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Transfer Rules</h3>
                            <ul class="space-y-2 text-sm text-blue-200">
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Transfers are instant and cannot be reversed</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>0.5% transfer fee applies to all transfers</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>You cannot transfer to yourself</span>
                                </li>
                                <li class="flex items-start space-x-2">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Recipient must be a registered user</span>
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
import { ref, computed, reactive, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Wallet, DollarSign, User, Mail, FileText,
    Loader2, AlertCircle, CheckCircle, PlusCircle,
    Repeat
} from 'lucide-vue-next';

const props = defineProps({
    balance: {
        type: [Number, String],
        default: 0
    },
    min_transfer: {
        type: Number,
        default: 100
    },
    max_transfer: {
        type: Number,
        default: 50000
    },
    transfer_fee_percentage: {
        type: Number,
        default: 0.5
    }
});

const loading = ref(false);
const quickAmounts = [100, 500, 1000, 2000, 5000, 10000];
const recipientInfo = ref(null);

const form = reactive({
    recipient_email: '',
    amount: 100,
    description: '',
});

// Calculate fees and amounts
const transferFee = computed(() => {
    const amount = parseFloat(form.amount) || 0;
    return amount * (props.transfer_fee_percentage / 100);
});

const totalAmount = computed(() => {
    const amount = parseFloat(form.amount) || 0;
    return amount + transferFee.value;
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

// Watch for email changes to validate recipient
watch(() => form.recipient_email, async (email) => {
    if (email && email.includes('@')) {
        // In a real app, you would make an API call to validate the user
        // For now, we'll simulate it
        setTimeout(() => {
            if (email.includes('@')) {
                recipientInfo.value = {
                    name: email.split('@')[0],
                    email: email
                };
            }
        }, 500);
    } else {
        recipientInfo.value = null;
    }
});

const submitTransfer = async () => {
    if (!form.recipient_email || !form.recipient_email.includes('@')) {
        alert('Please enter a valid recipient email');
        return;
    }

    if (!form.amount || form.amount < props.min_transfer || form.amount > props.max_transfer) {
        alert(`Transfer amount must be between KES ${props.min_transfer.toLocaleString()} and KES ${props.max_transfer.toLocaleString()}`);
        return;
    }

    if (totalAmount.value > parseFloat(props.balance)) {
        alert('Insufficient balance for this transfer');
        return;
    }

    const confirmMessage = `Are you sure you want to transfer KES ${formatCurrency(form.amount)} to ${form.recipient_email}? A fee of KES ${formatCurrency(transferFee.value)} will be charged.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    loading.value = true;

    try {
        await router.post('/wallet/transfer', form, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller redirect
            },
            onError: (errors) => {
                console.error('Transfer error:', errors);
                alert('Failed to process transfer. Please check the form and try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Transfer error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>