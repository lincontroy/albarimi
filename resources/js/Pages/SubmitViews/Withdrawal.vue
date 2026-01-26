<template>
    <DashboardLayout title="WhatsApp Withdrawals">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <MessageCircle :size="16" class="text-purple-400" />
            <span>Cash Flow</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">WhatsApp Withdrawals</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Withdrawal Form -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <h2 class="text-2xl font-bold text-white mb-2">Request WhatsApp Withdrawal</h2>
                    <p class="text-purple-300 mb-6">Withdraw your earnings to your WhatsApp number</p>

                    <form @submit.prevent="submitWithdrawal" class="space-y-6">
                        <!-- WhatsApp Number -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                WhatsApp Number <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Phone :size="20" class="text-purple-400" />
                                </div>
                                <input
                                    v-model="form.whatsapp_number"
                                    type="tel"
                                    required
                                    placeholder="254712345678"
                                    class="w-full bg-slate-800/50 border border-purple-500/30 rounded-xl pl-10 pr-4 py-3 text-white placeholder-purple-400/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                />
                            </div>
                            <p class="text-xs text-purple-400 mt-1">Format: 254712345678 (without +)</p>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                Amount (KES) <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <DollarSign :size="20" class="text-purple-400" />
                                </div>
                                <input
                                    v-model.number="form.amount"
                                    type="number"
                                    :min="limits.min"
                                    :max="limits.max"
                                    required
                                    :placeholder="`Enter amount (KES ${limits.min} - ${limits.max})`"
                                    class="w-full bg-slate-800/50 border border-purple-500/30 rounded-xl pl-10 pr-4 py-3 text-white placeholder-purple-400/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                />
                            </div>
                            <p class="text-xs text-purple-400 mt-1">
                                Minimum: KES {{ limits.min.toLocaleString() }} | Maximum: KES {{ limits.max.toLocaleString() }}
                            </p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                Notes (Optional)
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Any additional information..."
                                class="w-full bg-slate-800/50 border border-purple-500/30 rounded-xl px-4 py-3 text-white placeholder-purple-400/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            ></textarea>
                        </div>

                        <!-- Balance Preview -->
                        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 border border-purple-500/30">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-purple-300 text-sm">Available Balance</p>
                                    <p class="text-2xl font-bold text-white">
                                        KES {{ stats.available_balance.toLocaleString() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-purple-300 text-sm">Requested Amount</p>
                                    <p class="text-2xl font-bold text-white">
                                        KES {{ (form.amount || 0).toLocaleString() }}
                                    </p>
                                    <p class="text-purple-400 text-xs mt-1">
                                        Remaining: KES {{ remainingBalance.toLocaleString() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="!formValid || processing"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-medium py-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing" :size="20" class="animate-spin" />
                            <span>{{ processing ? 'Processing...' : 'Request Withdrawal' }}</span>
                        </button>
                    </form>
                </div>

                <!-- Stats & Information -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Withdrawal Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Total Withdrawn</span>
                                <span class="text-white font-bold">KES {{ stats.total_withdrawn.toLocaleString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Available Balance</span>
                                <span class="text-white font-bold">KES {{ stats.available_balance.toLocaleString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Pending</span>
                                <span class="text-yellow-400 font-bold">{{ stats.pending_withdrawals }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Completed</span>
                                <span class="text-green-400 font-bold">{{ stats.completed_withdrawals }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Withdrawal Guidelines</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Minimum withdrawal: KES {{ limits.min.toLocaleString() }}</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Maximum withdrawal: KES {{ limits.max.toLocaleString() }}</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Processing time: 2-24 hours</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">WhatsApp number must be active</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button
                                @click="fillAmount(stats.available_balance)"
                                class="w-full bg-purple-500/30 hover:bg-purple-500/50 p-3 rounded-xl text-white text-sm transition-all duration-200 text-center"
                            >
                                Withdraw Full Balance
                            </button>
                            <button
                                @click="fillAmount(1000)"
                                class="w-full bg-purple-500/30 hover:bg-purple-500/50 p-3 rounded-xl text-white text-sm transition-all duration-200 text-center"
                            >
                                Withdraw KES 1,000
                            </button>
                            <button
                                @click="fillAmount(5000)"
                                class="w-full bg-purple-500/30 hover:bg-purple-500/50 p-3 rounded-xl text-white text-sm transition-all duration-200 text-center"
                            >
                                Withdraw KES 5,000
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Withdrawals Table -->
            <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Recent Withdrawals</h3>
                    <div class="flex space-x-3">
                        <button
                            @click="refreshWithdrawals"
                            class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded-xl text-white text-sm transition-all duration-200 flex items-center space-x-2"
                        >
                            <RefreshCw :size="16" />
                            <span>Refresh</span>
                        </button>
                       
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-purple-500/30">
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">ID</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Amount</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Phone</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Transaction ID</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Date</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Status</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="withdrawal in withdrawals" 
                                :key="withdrawal.id"
                                class="border-b border-purple-500/10 hover:bg-purple-500/5 transition-colors"
                            >
                                <td class="py-3 px-4 text-white font-mono">#{{ withdrawal.id.toString().padStart(6, '0') }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-green-400 font-bold">KES {{ withdrawal.amount.toLocaleString() }}</span>
                                </td>
                                <td class="py-3 px-4 text-purple-300">{{ withdrawal.whatsapp_number }}</td>
                                <td class="py-3 px-4 text-purple-300 text-sm font-mono">{{ withdrawal.transaction_id }}</td>
                                <td class="py-3 px-4 text-purple-300 text-sm">{{ formatDate(withdrawal.requested_at) }}</td>
                                <td class="py-3 px-4">
                                    <span 
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusClasses(withdrawal.status)"
                                    >
                                        <span class="w-2 h-2 rounded-full mr-1.5" :class="getStatusDotClass(withdrawal.status)"></span>
                                        {{ withdrawal.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="viewWithdrawal(withdrawal)"
                                        class="text-purple-400 hover:text-purple-300 transition-colors p-1"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        v-if="withdrawal.status === 'pending'"
                                        @click="cancelWithdrawal(withdrawal)"
                                        class="text-red-400 hover:text-red-300 transition-colors p-1 ml-2"
                                        title="Cancel Withdrawal"
                                    >
                                        <X :size="16" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Empty State -->
                    <div v-if="withdrawals.length === 0" class="text-center py-8">
                        <Wallet :size="48" class="mx-auto text-purple-400 mb-4" />
                        <p class="text-purple-300 text-lg">No withdrawal requests yet</p>
                        <p class="text-purple-400">Start by requesting your first withdrawal above</p>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { 
    MessageCircle, Phone, DollarSign, CheckCircle, RefreshCw, 
    Loader2, Eye, X, Wallet, History
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import toastr from '@/plugins/toastr';

const props = defineProps({
    withdrawals: Array,
    stats: Object,
    limits: Object
});

// Use Inertia's form helper
const form = useForm({
    whatsapp_number: '',
    amount: '',
    notes: '',
});

const processing = ref(false);

// Computed properties
const formValid = computed(() => {
    return form.whatsapp_number && 
           form.amount >= props.limits.min && 
           form.amount <= props.limits.max &&
           form.amount <= props.stats.available_balance;
});

const remainingBalance = computed(() => {
    return props.stats.available_balance - (form.amount || 0);
});

// Methods
const fillAmount = (amount) => {
    form.amount = Math.min(amount, props.stats.available_balance);
};

const submitWithdrawal = async () => {
    if (!formValid.value) return;

    processing.value = true;

    try {
        const formData = new FormData();
        formData.append('whatsapp_number', form.whatsapp_number);
        formData.append('amount', form.amount);
        if (form.notes) {
            formData.append('notes', form.notes);
        }

        const response = await fetch('/cash-flow/whatsapp-withdrawals', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            resetForm();
            // Refresh the page to get updated data
            router.reload({ only: ['withdrawals', 'stats'] });
            
            if (toastr) {
                toastr.success(result.message || 'Withdrawal request submitted successfully! It will be processed soon.', 'Success!');
            }
        } else {
            let errorMessage = result.message || 'Error submitting withdrawal request. Please check the form and try again.';
            
            if (result.errors) {
                if (result.errors.amount) {
                    errorMessage = result.errors.amount;
                } else if (result.errors.whatsapp_number) {
                    errorMessage = result.errors.whatsapp_number;
                } else if (result.errors.general) {
                    errorMessage = result.errors.general;
                }
            }
            
            throw new Error(errorMessage);
        }
        
    } catch (error) {
        console.error('Error submitting withdrawal:', error);
        
        if (toastr) {
            toastr.error(error.message, 'Error!');
        }
    } finally {
        processing.value = false;
    }
};
const resetForm = () => {
    form.reset();
};

const refreshWithdrawals = () => {
    router.reload({ only: ['withdrawals', 'stats'] });
    if (toastr) {
        toastr.info('Withdrawals list has been refreshed.', 'Refreshed!');
    }
};

const viewWithdrawal = (withdrawal) => {
    router.visit(route('cash-flow.whatsapp-withdrawals.show', withdrawal.id));
};

const cancelWithdrawal = async (withdrawal) => {
    if (!confirm('Are you sure you want to cancel this withdrawal request?')) return;

    try {
        const response = await fetch(`/cash-flow/whatsapp-withdrawals/${withdrawal.id}/cancel`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        const result = await response.json();

        if (response.ok) {
            router.reload({ only: ['withdrawals', 'stats'] });
            if (toastr) {
                toastr.success(result.message, 'Cancelled!');
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error cancelling withdrawal:', error);
        if (toastr) {
            toastr.error(error.message, 'Error!');
        }
    }
};

const getStatusClasses = (status) => {
    const classes = {
        pending: 'bg-yellow-500/20 text-yellow-300',
        approved: 'bg-blue-500/20 text-blue-300',
        processing: 'bg-purple-500/20 text-purple-300',
        completed: 'bg-green-500/20 text-green-300',
        rejected: 'bg-red-500/20 text-red-300',
        failed: 'bg-red-500/20 text-red-300'
    };
    return classes[status.toLowerCase()] || 'bg-gray-500/20 text-gray-300';
};

const getStatusDotClass = (status) => {
    const classes = {
        pending: 'bg-yellow-400',
        approved: 'bg-blue-400',
        processing: 'bg-purple-400',
        completed: 'bg-green-400',
        rejected: 'bg-red-400',
        failed: 'bg-red-400'
    };
    return classes[status.toLowerCase()] || 'bg-gray-400';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<style scoped>
table {
    border-collapse: separate;
    border-spacing: 0;
}

th, td {
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
}

tr:last-child td {
    border-bottom: none;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>