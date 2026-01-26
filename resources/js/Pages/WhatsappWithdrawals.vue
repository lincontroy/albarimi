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

                <!-- Content -->
                <div class="p-4 lg:p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Withdrawal Form -->
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-lg font-bold text-purple-300 mb-6">Request Withdrawal</h3>
                            <form @submit.prevent="submitWithdrawal" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-purple-300 mb-2">WhatsApp Number</label>
                                    <input
                                        v-model="form.whatsapp_number"
                                        type="tel"
                                        class="w-full bg-slate-700/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="+254 XXX XXX XXX"
                                        required
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-purple-300 mb-2">Amount (KES)</label>
                                    <input
                                        v-model="form.amount"
                                        type="number"
                                        class="w-full bg-slate-700/50 border border-purple-500/30 rounded-lg px-4 py-3 text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        placeholder="Enter amount to withdraw"
                                        required
                                    />
                                </div>
                                
                                <div class="bg-purple-500/10 border border-purple-500/30 rounded-lg p-4">
                                    <h4 class="font-semibold text-purple-300 mb-2">Available Balance</h4>
                                    <p class="text-2xl font-bold text-green-400">KES {{ availableBalance.toLocaleString() }}</p>
                                </div>
                                
                                <button
                                    type="submit"
                                    :disabled="form.processing || form.amount > availableBalance"
                                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 px-6 py-3 rounded-lg font-semibold text-white transition-all duration-200 shadow-lg shadow-purple-500/50 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Processing...' : 'Request Withdrawal' }}
                                </button>
                            </form>
                        </div>

                        <!-- Withdrawal History -->
                        <div class="bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-lg font-bold text-purple-300 mb-6">Withdrawal History</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-300">
                                    <thead class="text-xs text-purple-300 uppercase bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-3">ID</th>
                                            <th class="px-4 py-3">Phone</th>
                                            <th class="px-4 py-3">Amount</th>
                                            <th class="px-4 py-3">Date</th>
                                            <th class="px-4 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="withdrawal in withdrawals" :key="withdrawal.id" class="border-b border-purple-500/20 hover:bg-purple-500/10">
                                            <td class="px-4 py-3 font-mono">{{ withdrawal.id }}</td>
                                            <td class="px-4 py-3">{{ withdrawal.phone }}</td>
                                            <td class="px-4 py-3 text-green-400">KES {{ withdrawal.amount.toLocaleString() }}</td>
                                            <td class="px-4 py-3">{{ withdrawal.date }}</td>
                                            <td class="px-4 py-3">
                                                <span :class="`px-2 py-1 rounded-full text-xs ${
                                                    withdrawal.status === 'completed' ? 'bg-green-500/20 text-green-400' :
                                                    withdrawal.status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' :
                                                    'bg-red-500/20 text-red-400'
                                                }`">
                                                    {{ withdrawal.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
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
import { Menu, ChartBar } from 'lucide-vue-next';

const form = useForm({
    whatsapp_number: '',
    amount: '',
});

const availableBalance = ref(15000);

const withdrawals = ref([
    { id: 'WD001', phone: '+254712345678', amount: 5000, date: '2024-01-15', status: 'completed' },
    { id: 'WD002', phone: '+254723456789', amount: 3000, date: '2024-01-14', status: 'pending' },
    { id: 'WD003', phone: '+254734567890', amount: 7000, date: '2024-01-13', status: 'completed' },
]);

const submitWithdrawal = () => {
    form.post('/whatsapp-withdrawals', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>