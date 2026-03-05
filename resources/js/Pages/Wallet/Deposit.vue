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
import { ref, computed, reactive, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Wallet, DollarSign, CreditCard, Loader2,
    AlertCircle, Info, Shield, CheckCircle,
    Phone, Building, CreditCard as CardIcon
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    balance: {
        type: [Number, String],
        default: 0
    },
    min_deposit: {
        type: Number,
        default: 1
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
const checkingStatus = ref(false);
let statusCheckInterval = null;
let statusTimeout = null;

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

const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

// Clean up intervals on component unmount
onMounted(() => {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token meta tag not found!');
    }
});

onMounted(() => {
    // Clean up on unmount
    return () => {
        if (statusCheckInterval) {
            clearInterval(statusCheckInterval);
        }
        if (statusTimeout) {
            clearTimeout(statusTimeout);
        }
    };
});

const stopStatusChecking = () => {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
    if (statusTimeout) {
        clearTimeout(statusTimeout);
        statusTimeout = null;
    }
    checkingStatus.value = false;
};

const checkTransactionStatus = async (transactionId) => {
    // Stop any existing checking
    stopStatusChecking();
    
    checkingStatus.value = true;
    
    // Close any existing Swal dialogs
    await Swal.close();
    
    // Show processing dialog
    Swal.fire({
        title: 'Processing Payment',
        html: `
            <div class="text-center">
                <div class="mb-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-500 border-t-transparent mx-auto"></div>
                </div>
                <p class="mb-2">Please wait while we confirm your payment...</p>
                <p class="text-sm text-gray-500">Transaction ID: ${transactionId}</p>
                <p class="text-xs text-gray-400 mt-2">This may take a few moments</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    statusCheckInterval = setInterval(async () => {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch(`/wallet/deposit/check/${transactionId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(token && { 'X-CSRF-TOKEN': token })
                }
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data && data.status) {
                if (data.status === 'completed') {
                    // Stop checking immediately
                    stopStatusChecking();
                    
                    // Close the processing dialog
                    await Swal.close();
                    
                    // Show success message
                    await Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        html: `
                            <div class="text-center">
                                <p class="mb-2">Your deposit has been completed successfully!</p>
                                <p class="text-sm text-gray-600">Transaction ID: ${data.transaction_id || transactionId}</p>
                                ${data.checkout_request_id ? `<p class="text-xs text-gray-500">M-Pesa Ref: ${data.checkout_request_id}</p>` : ''}
                            </div>
                        `,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'View Wallet'
                    });
                    
                    window.location.href = '/wallet';
                    
                } else if (data.status === 'failed') {
                    // Stop checking immediately
                    stopStatusChecking();
                    
                    // Close the processing dialog
                    await Swal.close();
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        text: data.message || 'Your payment could not be processed. Please try again.',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Try Again'
                    });
                    
                    window.location.href = '/wallet/deposit';
                }
                // If still pending, continue checking
            }
        } catch (error) {
            console.error('Status check error:', error);
        }
    }, 3000);

    // Stop checking after 2 minutes
    statusTimeout = setTimeout(() => {
        if (checkingStatus.value) {
            stopStatusChecking();
            
            // Close the processing dialog
            Swal.close();
            
            Swal.fire({
                icon: 'info',
                title: 'Still Processing',
                html: `
                    <div class="text-center">
                        <p class="mb-2">Your payment is still being processed.</p>
                        <p class="text-sm text-gray-600">You can check the status in your transaction history.</p>
                    </div>
                `,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'View History'
            }).then(() => {
                window.location.href = '/wallet/history';
            });
        }
    }, 120000);
};

const submitDeposit = async () => {
    if (!form.amount || form.amount < props.min_deposit || form.amount > props.max_deposit) {
        await Swal.fire({
            icon: 'warning',
            title: 'Invalid Amount',
            text: `Deposit amount must be between KES ${props.min_deposit.toLocaleString()} and KES ${props.max_deposit.toLocaleString()}`,
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    if (!form.payment_method) {
        await Swal.fire({
            icon: 'warning',
            title: 'Payment Method Required',
            text: 'Please select a payment method',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    if (form.payment_method === 'mpesa' && !form.phone_number) {
        await Swal.fire({
            icon: 'warning',
            title: 'Phone Number Required',
            text: 'Please enter your M-Pesa phone number',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    if (form.payment_method === 'mpesa') {
        const phoneRegex = /^[0-9]{10,12}$/;
        if (!phoneRegex.test(form.phone_number)) {
            await Swal.fire({
                icon: 'warning',
                title: 'Invalid Phone Number',
                text: 'Please enter a valid phone number (10-12 digits)',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }
    }

    const result = await Swal.fire({
        title: 'Confirm Deposit',
        html: `
            <div class="text-left">
                <p class="mb-2">Please confirm your deposit details:</p>
                <div class="bg-gray-100 p-3 rounded-lg">
                    <p><strong>Amount:</strong> KES ${formatCurrency(form.amount)}</p>
                    <p><strong>Method:</strong> ${form.payment_method.toUpperCase()}</p>
                    ${form.payment_method === 'mpesa' ? `<p><strong>Phone:</strong> ${form.phone_number}</p>` : ''}
                    <p><strong>Fee:</strong> KES ${formatCurrency(processingFee.value)}</p>
                    <p><strong>Net Deposit:</strong> KES ${formatCurrency(netAmount.value)}</p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Deposit',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }

    loading.value = true;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!token) {
            throw new Error('CSRF token not found');
        }

        const response = await fetch('/api/wallet/deposit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // 'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(form)
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(errorMessages);
            }
            throw new Error(data.message || 'Deposit failed');
        }

        if (data && data.success) {
            if (data.redirect) {
                // Close any existing dialogs
                await Swal.close();
                
                // Show M-Pesa prompt message
                await Swal.fire({
                    icon: 'info',
                    title: 'M-Pesa Prompt Sent',
                    html: `
                        <div class="text-center">
                            <p class="mb-2">${data.message || 'Please check your phone and enter your M-Pesa PIN to complete the deposit.'}</p>
                            <p class="text-sm text-gray-600">Transaction ID: ${data.transaction_id}</p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonColor: '#10b981',
                    confirmButtonText: 'OK, I\'ve entered PIN'
                });
                
                
                    window.location.href = '/dashboard';
                
            } else {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Deposit successful!',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                setTimeout(() => {
                    window.location.href = '/wallet';
                }, 2000);
            }
        } else {
            throw new Error('Invalid response from server');
        }
    } catch (error) {
        console.error('Deposit error:', error);
        
        await Swal.fire({
            icon: 'error',
            title: 'Deposit Failed',
            text: error.message || 'Failed to process deposit. Please try again.',
            confirmButtonColor: '#ef4444'
        });
    } finally {
        loading.value = false;
    }
};
</script>