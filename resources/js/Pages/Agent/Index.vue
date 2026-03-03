<template>
    <DashboardLayout title="Agent Package">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Crown :size="16" class="text-purple-400" />
            <span>Agent</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Agent Package</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Agent Package Form -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <h2 class="text-2xl font-bold text-white mb-2">Become a Barimax Agent</h2>
                    <p class="text-purple-300 mb-6">Upgrade to our premium agent package and unlock exclusive benefits</p>

                    <!-- Agent Badge (if user is agent) - Fixed boolean check -->
                    <div v-if="isAgent" class="mb-6">
                        <div class="bg-gradient-to-r from-emerald-600/40 to-green-600/40 backdrop-blur-xl border border-emerald-500/30 rounded-2xl p-6 shadow-2xl shadow-emerald-500/20">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-green-500 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/50">
                                        <Crown :size="32" class="text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Barimax Agent</h3>
                                        <p class="text-emerald-200">Member since {{ formatDate(packageData.agent_since) }}</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <span class="inline-flex items-center px-3 py-1 bg-emerald-500/30 rounded-full text-sm text-emerald-300 border border-emerald-500/50">
                                                <Star :size="14" class="mr-1" />
                                                Verified Agent
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-left lg:text-right">
                                    <p class="text-emerald-200 text-sm">Monthly Salary</p>
                                    <p class="text-2xl font-bold text-white">KES {{ formatNumber(packageData.agent_salary) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Package Benefits -->
                    <div class="space-y-4 mb-6">
                        <div v-for="(benefit, index) in packageData.benefits" :key="index" class="flex items-center space-x-3 p-3 bg-purple-500/10 rounded-xl border border-purple-500/20">
                            <CheckCircle :size="20" class="text-green-400 flex-shrink-0" />
                            <span class="text-white text-sm">{{ benefit }}</span>
                        </div>
                    </div>

                    <!-- Purchase Section (if not agent) - Fixed boolean check -->
                    <div v-if="!isAgent">
                        <!-- Balance Preview -->
                        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 border border-purple-500/30 mb-6">
                            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                                <div>
                                    <p class="text-purple-300 text-sm">Your Deposit Balance</p>
                                    <p class="text-2xl font-bold text-white">
                                        KES {{ formatNumber(packageData.deposit_balance) }}
                                    </p>
                                </div>
                                <div class="lg:text-right">
                                    <p class="text-purple-300 text-sm">Package Price</p>
                                    <p class="text-2xl font-bold text-white">
                                        KES {{ formatNumber(packageData.package_price) }}
                                    </p>
                                    <p class="text-purple-400 text-xs mt-1" :class="canPurchase ? 'text-green-400' : 'text-red-400'">
                                        {{ canPurchase ? 'Sufficient balance' : 'Insufficient balance' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            @click="purchasePackage"
                            :disabled="!canPurchase || processing"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-medium py-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing" :size="20" class="animate-spin" />
                            <Crown v-else :size="20" />
                            <span>{{ processing ? 'Processing...' : 'Become an Agent Now' }}</span>
                        </button>

                        <p v-if="!canPurchase && !isAgent" class="text-center text-red-400 text-sm mt-3">
                            You need KES {{ formatNumber(packageData.package_price) }} in your deposit balance to purchase this package.
                        </p>
                    </div>

                    <!-- Agent Benefits (if agent) - Fixed boolean check -->
                    <div v-else class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-xl p-4 border border-green-500/30">
                        <h3 class="text-lg font-bold text-white mb-3">Your Agent Benefits</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="text-center">
                                <p class="text-green-200 text-sm">Cashback Bonus</p>
                                <p class="text-2xl font-bold text-white">KES {{ formatNumber(packageData.agent_bonus) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-green-200 text-sm">Monthly Salary</p>
                                <p class="text-2xl font-bold text-white">KES {{ formatNumber(packageData.agent_salary) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-green-200 text-sm">Total Earnings</p>
                                <p class="text-2xl font-bold text-white">KES {{ formatNumber((packageData.agent_bonus || 0) + (packageData.agent_salary || 0)) }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-green-200 text-sm">Member Since</p>
                                <p class="text-lg font-bold text-white">{{ formatDate(packageData.agent_since) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Information -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Agent Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Agent Status</span>
                                <span class="text-white font-bold">{{ isAgent ? 'Active' : 'Not Agent' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Deposit Balance</span>
                                <span class="text-white font-bold">KES {{ formatNumber(packageData.deposit_balance) }}</span>
                            </div>
                            <div v-if="isAgent" class="flex justify-between items-center">
                                <span class="text-green-200">Cashback Bonus</span>
                                <span class="text-green-400 font-bold">KES {{ formatNumber(packageData.agent_bonus) }}</span>
                            </div>
                            <div v-if="isAgent" class="flex justify-between items-center">
                                <span class="text-green-200">Monthly Salary</span>
                                <span class="text-green-400 font-bold">KES {{ formatNumber(packageData.agent_salary) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Package Benefits</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">KES 15,000 instant cashback bonus</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">KES 35,000 monthly agent salary</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Automatic and instant withdrawals</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Most Superior package benefits</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Package Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-purple-200">Package Price</span>
                                <span class="text-white font-bold">KES {{ formatNumber(packageData.package_price) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-purple-200">Cashback Bonus</span>
                                <span class="text-green-400 font-bold">KES {{ formatNumber(packageData.cashback_bonus || 15000) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-purple-200">Monthly Salary</span>
                                <span class="text-green-400 font-bold">KES {{ formatNumber(packageData.monthly_salary || 35000) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-purple-500/30">
                                <span class="text-purple-200 font-bold">Net Profit</span>
                                <span class="text-green-400 font-bold">KES {{ formatNumber((packageData.cashback_bonus || 15000) + (packageData.monthly_salary || 35000)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases Table -->
            <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                    <h3 class="text-xl font-bold text-white">Recent Package Purchases</h3>
                    <button
                        @click="refreshPurchases"
                        class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded-xl text-white text-sm transition-all duration-200 flex items-center justify-center space-x-2"
                    >
                        <RefreshCw :size="16" class="group-hover:rotate-180 transition-transform duration-500" />
                        <span>Refresh</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-purple-500/30">
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">ID</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Amount</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Package</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Transaction ID</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Date</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="purchase in recentPurchases" 
                                :key="purchase.id"
                                class="border-b border-purple-500/10 hover:bg-purple-500/5 transition-colors"
                            >
                                <td class="py-3 px-4 text-white font-mono">#{{ purchase.id.toString().padStart(6, '0') }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-green-400 font-bold">KES {{ formatNumber(purchase.amount) }}</span>
                                </td>
                                <td class="py-3 px-4 text-purple-300">{{ purchase.package_name }}</td>
                                <td class="py-3 px-4 text-purple-300 text-sm font-mono">{{ purchase.transaction_id }}</td>
                                <td class="py-3 px-4 text-purple-300 text-sm">{{ formatDate(purchase.purchased_at) }}</td>
                                <td class="py-3 px-4">
                                    <span 
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusClasses(purchase.status)"
                                    >
                                        <span class="w-2 h-2 rounded-full mr-1.5" :class="getStatusDotClass(purchase.status)"></span>
                                        {{ capitalize(purchase.status) }}
                                    </span>
                                </td>
                            </tr>
                            
                            <!-- Empty State -->
                            <tr v-if="recentPurchases.length === 0">
                                <td colspan="6" class="text-center py-8">
                                    <Package :size="48" class="mx-auto text-purple-400 mb-4" />
                                    <p class="text-purple-300 text-lg">No package purchases yet</p>
                                    <p class="text-purple-400">Become an agent to unlock premium benefits</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Crown, CheckCircle, RefreshCw, Loader2, Star, Package
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

// Initialize toastr if available
const toastr = typeof window !== 'undefined' && window.toastr;

const props = defineProps({
    package: {
        type: Object,
        default: () => ({})
    },
    recentPurchases: {
        type: Array,
        default: () => []
    }
});

const processing = ref(false);

// Rename to avoid conflict with reserved keyword
const packageData = computed(() => {
    return {
        ...props.package,
        package_price: props.package?.package_price || 6500,
        cashback_bonus: props.package?.cashback_bonus || 15000,
        monthly_salary: props.package?.monthly_salary || 35000
    };
});

// Fixed boolean check for is_agent
const isAgent = computed(() => {
    return props.package?.is_agent === true || props.package?.is_agent === 1;
});

// Computed properties
const canPurchase = computed(() => {
    return (props.package?.deposit_balance || 0) >= (props.package?.package_price || 6500) && !isAgent.value;
});

// Helper functions
const formatNumber = (num) => {
    if (num === undefined || num === null) return '0';
    return Number(num).toLocaleString();
};

const capitalize = (str) => {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
};

// Methods
const purchasePackage = async () => {
    if (!canPurchase.value || processing.value) return;

    processing.value = true;

    try {
        const response = await fetch('/agent-package/purchase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const result = await response.json();

        if (response.ok) {
            // Refresh the page to get updated data
            router.reload();
            
            if (toastr) {
                toastr.success(result.message || 'Package purchased successfully!', 'Congratulations! 🎉');
            } else {
                alert(result.message || 'Package purchased successfully!');
            }
        } else {
            let errorMessage = result.message || 'Error purchasing agent package. Please try again.';
            
            if (result.errors) {
                errorMessage = Object.values(result.errors).flat()[0] || errorMessage;
            }
            
            throw new Error(errorMessage);
        }
        
    } catch (error) {
        console.error('Error purchasing package:', error);
        
        if (toastr) {
            toastr.error(error.message, 'Error!');
        } else {
            alert(error.message);
        }
    } finally {
        processing.value = false;
    }
};

const refreshPurchases = () => {
    router.reload({ only: ['recentPurchases'] });
    
    if (toastr) {
        toastr.info('Purchase list has been refreshed.', 'Refreshed!');
    }
};

const getStatusClasses = (status) => {
    const statusLower = (status || '').toLowerCase();
    const classes = {
        pending: 'bg-yellow-500/20 text-yellow-300',
        completed: 'bg-green-500/20 text-green-300',
        failed: 'bg-red-500/20 text-red-300',
        cancelled: 'bg-gray-500/20 text-gray-300'
    };
    return classes[statusLower] || 'bg-gray-500/20 text-gray-300';
};

const getStatusDotClass = (status) => {
    const statusLower = (status || '').toLowerCase();
    const classes = {
        pending: 'bg-yellow-400',
        completed: 'bg-green-400',
        failed: 'bg-red-400',
        cancelled: 'bg-gray-400'
    };
    return classes[statusLower] || 'bg-gray-400';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
};
</script>

<style scoped>
table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

th, td {
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
}

tr:last-child td {
    border-bottom: none;
}

/* Hover effect on table rows */
tbody tr:hover {
    background-color: rgba(168, 85, 247, 0.05);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .lg\:col-span-2, .lg\:col-span-1 {
        padding: 1rem;
    }
    
    table {
        font-size: 0.875rem;
    }
    
    th, td {
        padding: 0.75rem 0.5rem;
    }
}

/* Animation for refresh button */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.group-hover\:rotate-180:hover {
    animation: spin 0.5s ease-in-out;
}

/* Input styling */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>