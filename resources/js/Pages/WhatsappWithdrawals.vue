<template>
    <AuthenticatedLayout title="WhatsApp Withdrawals">
        <div class="flex h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 overflow-hidden">
            <!-- Sidebar (same as dashboard) -->
            <!-- ... sidebar code ... -->

            <main class="flex-1 overflow-auto">
                <!-- Header -->
                <header class="bg-slate-800/50 backdrop-blur-xl border-b border-purple-500/20 text-white px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-10">
                    <div class="flex items-center space-x-4">
                        <button @click="isSidebarOpen = true" class="lg:hidden hover:bg-purple-500/20 p-2 rounded-lg transition-colors">
                            <Menu :size="24" />
                        </button>
                        <h1 class="text-xl lg:text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">WhatsApp Withdrawals</h1>
                    </div>
                </header>

                <!-- Breadcrumb -->
                <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
                    <ChartBar :size="16" class="text-purple-400" />
                    <span>Cashflow</span>
                    <span class="text-purple-400">›</span>
                    <span class="text-purple-300">WhatsApp Withdrawals</span>
                </div>

                <!-- Package Upgrade Banner - Show if not Bariplus -->
                <div v-if="!hasBariplus" class="mx-4 lg:mx-8 mt-4">
                    <div class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 border border-purple-500/30 backdrop-blur-xl rounded-2xl p-6">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="bg-yellow-500/20 p-3 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-white">Upgrade to Bariplus Package</h3>
                                    <p class="text-purple-300">Access WhatsApp withdrawals and earn more with our premium package!</p>
                                </div>
                            </div>
                            <Link href="/packages" class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-3 px-8 rounded-lg transition-all duration-200 shadow-lg shadow-yellow-500/50">
                                Upgrade Now
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Withdrawal Form -->
                        <div :class="[
                            'bg-slate-800/40 backdrop-blur-xl border rounded-2xl p-6 shadow-2xl',
                            !hasBariplus ? 'border-gray-500/30 opacity-60' : 'border-purple-500/30 shadow-purple-500/20'
                        ]">
                            <h3 class="text-lg font-bold text-purple-300 mb-6">Request Withdrawal</h3>
                            
                            <!-- Disabled overlay for non-Bariplus users -->
                            <div v-if="!hasBariplus" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                <div class="text-center p-6">
                                    <Lock class="h-12 w-12 text-yellow-400 mx-auto mb-4" />
                                    <p class="text-white font-semibold mb-2">Feature Locked</p>
                                    <p class="text-purple-300 text-sm mb-4">Upgrade to Bariplus Package to access withdrawals</p>
                                    <Link href="/packages" class="inline-block bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-2 px-6 rounded-lg text-sm">
                                        Upgrade Now
                                    </Link>
                                </div>
                            </div>

                            <form @submit.prevent="submitWithdrawal" class="space-y-4 relative">
                                <div>
                                    <label class="block text-sm font-medium text-purple-300 mb-2">WhatsApp Number</label>
                                    <input
                                        v-model="form.whatsapp_number"
                                        type="tel"
                                        class="w-full bg-slate-700/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="254XXXXXXXXX"
                                        :disabled="!hasBariplus || form.processing"
                                        required
                                    />
                                    <p class="text-xs text-purple-400 mt-1">Format: 254XXXXXXXXX (e.g., 254712345678)</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-purple-300 mb-2">Amount (KES)</label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        class="w-full bg-slate-700/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="Enter amount"
                                        :min="limits.min"
                                        :max="limits.max"
                                        :disabled="!hasBariplus || form.processing"
                                        required
                                    />
                                    <p class="text-xs text-purple-400 mt-1">Min: KES {{ limits.min.toLocaleString() }} | Max: KES {{ limits.max.toLocaleString() }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-purple-300 mb-2">Notes (Optional)</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full bg-slate-700/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="Any additional information..."
                                        :disabled="!hasBariplus || form.processing"
                                    ></textarea>
                                </div>
                                
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4">
                                    <h4 class="font-semibold text-purple-300 mb-2">Available Balance</h4>
                                    <p class="text-2xl font-bold text-green-400">KES {{ stats.available_balance.toLocaleString() }}</p>
                                </div>
                                
                                <button
                                    type="submit"
                                    :disabled="!hasBariplus || form.processing || form.amount > stats.available_balance"
                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200 shadow-lg shadow-purple-500/50 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Processing...' : 'Request Withdrawal' }}
                                </button>
                            </form>
                        </div>

                        <!-- Withdrawal History -->
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-lg font-bold text-purple-300 mb-6">Recent Withdrawals</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-300">
                                    <thead class="text-xs text-purple-300 uppercase bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-3">Transaction ID</th>
                                            <th class="px-4 py-3">Phone</th>
                                            <th class="px-4 py-3">Amount</th>
                                            <th class="px-4 py-3">Date</th>
                                            <th class="px-4 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="border-b border-purple-500/20 hover:bg-purple-500/10">
                                            <td class="px-4 py-3 font-mono text-xs">{{ withdrawal.transaction_id || withdrawal.id }}</td>
                                            <td class="px-4 py-3">{{ withdrawal.whatsapp_number }}</td>
                                            <td class="px-4 py-3 text-green-400">KES {{ withdrawal.amount.toLocaleString() }}</td>
                                            <td class="px-4 py-3">{{ formatDate(withdrawal.requested_at) }}</td>
                                            <td class="px-4 py-3">
                                                <span :class="`px-2 py-1 rounded-full text-xs ${
                                                    withdrawal.status === 'completed' ? 'bg-green-500/20 text-green-400' :
                                                    withdrawal.status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' :
                                                    withdrawal.status === 'processing' ? 'bg-blue-500/20 text-blue-400' :
                                                    'bg-red-500/20 text-red-400'
                                                }`">
                                                    {{ withdrawal.status }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="withdrawals.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-purple-400">
                                                No withdrawal requests yet
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- View All Link -->
                            <div class="mt-4 text-right">
                                <Link href="/whatsapp-withdrawals/history" class="text-purple-400 hover:text-purple-300 text-sm font-medium">
                                    View All History →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-xl p-4">
                            <p class="text-purple-400 text-sm mb-1">Total Withdrawn</p>
                            <p class="text-2xl font-bold text-white">KES {{ stats.total_withdrawn.toLocaleString() }}</p>
                        </div>
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-xl p-4">
                            <p class="text-purple-400 text-sm mb-1">Pending Requests</p>
                            <p class="text-2xl font-bold text-yellow-400">{{ stats.pending_withdrawals }}</p>
                        </div>
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-xl p-4">
                            <p class="text-purple-400 text-sm mb-1">Completed</p>
                            <p class="text-2xl font-bold text-green-400">{{ stats.completed_withdrawals }}</p>
                        </div>
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-xl p-4">
                            <p class="text-purple-400 text-sm mb-1">Available Balance</p>
                            <p class="text-2xl font-bold text-green-400">KES {{ stats.available_balance.toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Menu, ChartBar, Lock } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';

const props = defineProps({
    withdrawals: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_withdrawn: 0,
            pending_withdrawals: 0,
            completed_withdrawals: 0,
            available_balance: 0
        })
    },
    limits: {
        type: Object,
        default: () => ({
            min: 100,
            max: 100000
        })
    },
    hasBariplus: {
        type: Boolean,
        default: false
    },
    userPackage: {
        type: String,
        default: ''
    }
});

const toast = useToast();

const form = useForm({
    whatsapp_number: '',
    amount: '',
    notes: ''
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const submitWithdrawal = () => {
    // Double-check package requirement
    if (!props.hasBariplus) {
        toast.error('You need to upgrade to Bariplus Package to access withdrawals!', {
            timeout: 5000,
            position: 'top-right',
            icon: '🔒'
        });
        return;
    }

    form.post('/whatsapp-withdrawals', {
        preserveScroll: true,
        onSuccess: (response) => {
            form.reset();
            toast.success('Withdrawal request submitted successfully! It will be processed soon.', {
                timeout: 5000,
                position: 'top-right',
                icon: '✅'
            });
        },
        onError: (errors) => {
            if (errors.package || errors.message?.includes('Bariplus')) {
                toast.error(errors.package || 'You need to upgrade to Bariplus Package!', {
                    timeout: 5000,
                    position: 'top-right',
                    icon: '🔒'
                });
            } else if (errors.amount) {
                toast.error(errors.amount, {
                    timeout: 4000,
                    position: 'top-right'
                });
            } else {
                toast.error('Error submitting request. Please try again.', {
                    timeout: 4000,
                    position: 'top-right'
                });
            }
        }
    });
};
</script>

<style scoped>
/* Add any custom styles here */
</style>