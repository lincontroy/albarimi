<!-- History.vue -->
<template>
    <DashboardLayout title="Packages - History">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <History :size="16" class="text-green-400" />
            <span>Packages</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Purchase History</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-white">Package Purchase History</h1>
                    <Link 
                        href="/packages"
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50"
                    >
                        Back to Packages
                    </Link>
                </div>

                <!-- History Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-green-500/20">
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Package</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Amount</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Purchase Date</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Expires</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Earnings</th>
                                    <th class="text-left py-3 px-4 text-green-300 text-sm font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="purchase in purchases.data" 
                                    :key="purchase.id"
                                    class="border-b border-green-500/10 hover:bg-green-500/5 transition-colors"
                                >
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <component :is="getPackageIcon(purchase.package_type)" :size="20" :class="getIconColor(purchase.package_type)" />
                                            <div>
                                                <p class="text-white font-semibold">{{ purchase.package_name }}</p>
                                                <p class="text-green-300 text-xs">{{ formatPackageType(purchase.package_type) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white font-semibold">KES {{ purchase.amount.toLocaleString() }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ purchase.activated_at }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span 
                                            :class="getStatusColor(purchase.status)"
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ purchase.status }}
                                        </span>
                                        <p v-if="purchase.is_active" class="text-green-400 text-xs mt-1">
                                            {{ purchase.days_remaining }} days remaining
                                        </p>
                                        <p v-if="purchase.is_expired" class="text-red-400 text-xs mt-1">
                                            Expired
                                        </p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ purchase.expires_at || 'N/A' }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-green-400 font-semibold">KES {{ purchase.potential_earnings.toLocaleString() }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <Link 
                                                :href="'/packages/' + purchase.id"
                                                class="text-blue-400 hover:text-blue-300 transition-colors p-2 hover:bg-blue-500/10 rounded-lg"
                                                title="View Details"
                                            >
                                                <Eye :size="16" />
                                            </Link>
                                            <button 
                                                v-if="purchase.is_active && purchase.days_remaining < 7"
                                                @click="renewPackage(purchase.id)"
                                                class="text-green-400 hover:text-green-300 transition-colors p-2 hover:bg-green-500/10 rounded-lg"
                                                title="Renew Package"
                                            >
                                                <RefreshCw :size="16" />
                                            </button>
                                            <button 
                                                v-if="canUpgrade(purchase)"
                                                @click="upgradePackage(purchase.id)"
                                                class="text-purple-400 hover:text-purple-300 transition-colors p-2 hover:bg-purple-500/10 rounded-lg"
                                                title="Upgrade Package"
                                            >
                                                <TrendingUp :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-4">
                        <div 
                            v-for="purchase in purchases.data" 
                            :key="purchase.id"
                            :class="getPackageColor(purchase.package_type)"
                            class="border rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <component :is="getPackageIcon(purchase.package_type)" :size="20" />
                                    <div>
                                        <p class="text-white font-semibold">{{ purchase.package_name }}</p>
                                        <p class="opacity-75 text-xs">{{ formatPackageType(purchase.package_type) }}</p>
                                    </div>
                                </div>
                                <span 
                                    :class="getStatusColor(purchase.status)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                >
                                    {{ purchase.status }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="opacity-75">Amount:</span>
                                    <span class="font-semibold">KES {{ purchase.amount.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Purchased:</span>
                                    <span>{{ purchase.activated_at }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Expires:</span>
                                    <span>{{ purchase.expires_at || 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Earnings Potential:</span>
                                    <span class="font-semibold text-green-400">KES {{ purchase.potential_earnings.toLocaleString() }}</span>
                                </div>
                                <div v-if="purchase.is_active" class="flex justify-between text-green-400">
                                    <span>Days Remaining:</span>
                                    <span class="font-semibold">{{ purchase.days_remaining }} days</span>
                                </div>
                                <div v-if="purchase.is_expired" class="flex justify-between text-red-400">
                                    <span>Status:</span>
                                    <span class="font-semibold">Expired</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                                <Link 
                                    :href="'/packages/' + purchase.id"
                                    class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                >
                                    <Eye :size="14" />
                                    <span>Details</span>
                                </Link>
                                <button 
                                    v-if="purchase.is_active && purchase.days_remaining < 7"
                                    @click="renewPackage(purchase.id)"
                                    class="text-green-400 hover:text-green-300 text-sm flex items-center space-x-1"
                                >
                                    <RefreshCw :size="14" />
                                    <span>Renew</span>
                                </button>
                                <button 
                                    v-if="canUpgrade(purchase)"
                                    @click="upgradePackage(purchase.id)"
                                    class="text-purple-400 hover:text-purple-300 text-sm flex items-center space-x-1"
                                >
                                    <TrendingUp :size="14" />
                                    <span>Upgrade</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="purchases.data.length === 0" class="text-center py-12">
                        <Package :size="64" class="mx-auto text-green-400 mb-4 opacity-50" />
                        <p class="text-green-300 text-lg mb-2">No package purchases yet</p>
                        <p class="text-green-400 text-sm mb-6">Purchase your first earning package to get started</p>
                        <Link 
                            href="/packages"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 inline-flex items-center space-x-2"
                        >
                            <Package :size="18" />
                            <span>View Packages</span>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="purchases.total > 0" class="flex items-center justify-between mt-6 pt-6 border-t border-green-500/20">
                        <p class="text-green-300 text-sm">
                            Showing {{ purchases.from }} to {{ purchases.to }} of {{ purchases.total }} purchases
                        </p>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="previousPage"
                                :disabled="!purchases.prev_page_url"
                                class="bg-black/30 border border-green-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-green-500 transition-colors"
                            >
                                <ChevronLeft :size="16" />
                            </button>
                            <span class="text-white text-sm">
                                Page {{ purchases.current_page }} of {{ purchases.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="!purchases.next_page_url"
                                class="bg-black/30 border border-green-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-green-500 transition-colors"
                            >
                                <ChevronRight :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-4 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <p class="text-green-300 text-sm mb-1">Total Purchases</p>
                            <p class="text-2xl font-bold text-white">{{ purchases.total }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20">
                        <div class="text-center">
                            <p class="text-blue-300 text-sm mb-1">Active Packages</p>
                            <p class="text-2xl font-bold text-white">{{ activePackagesCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-600/40 to-amber-600/40 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-4 shadow-2xl shadow-yellow-500/20">
                        <div class="text-center">
                            <p class="text-yellow-300 text-sm mb-1">Expiring Soon</p>
                            <p class="text-2xl font-bold text-white">{{ expiringSoonCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-600/40 to-red-600/40 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-4 shadow-2xl shadow-orange-500/20">
                        <div class="text-center">
                            <p class="text-orange-300 text-sm mb-1">Total Earnings Potential</p>
                            <p class="text-2xl font-bold text-white">KES {{ totalEarningsPotential.toLocaleString() }}</p>
                        </div>
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
    Package, Crown, Eye, RefreshCw, TrendingUp,
    ChevronLeft, ChevronRight, History
} from 'lucide-vue-next';

const props = defineProps({
    purchases: Object,
});

// Compute stats
const activePackagesCount = computed(() => {
    return props.purchases.data.filter(p => p.is_active).length;
});

const expiringSoonCount = computed(() => {
    return props.purchases.data.filter(p => p.is_active && p.days_remaining < 7).length;
});

const totalEarningsPotential = computed(() => {
    return props.purchases.data.reduce((total, purchase) => {
        return total + (purchase.potential_earnings || 0);
    }, 0);
});

const getPackageColor = (type) => {
    const colors = {
        'entry': 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30',
        'lite': 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30',
        'pro': 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30',
        'bariplus': 'bg-gradient-to-br from-orange-600/40 to-yellow-600/40 border-orange-500/30',
    };
    return colors[type] || 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
};

const getPackageIcon = (type) => {
    const icons = {
        'entry': Package,
        'lite': Package,
        'pro': Package,
        'bariplus': Crown,
    };
    return icons[type] || Package;
};

const getIconColor = (type) => {
    const colors = {
        'entry': 'text-blue-400',
        'lite': 'text-green-400',
        'pro': 'text-purple-400',
        'bariplus': 'text-orange-400',
    };
    return colors[type] || 'text-slate-400';
};

const formatPackageType = (type) => {
    return type.replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500/20 text-green-400';
        case 'pending': return 'bg-yellow-500/20 text-yellow-400';
        case 'expired': return 'bg-red-500/20 text-red-400';
        case 'cancelled': return 'bg-gray-500/20 text-gray-400';
        default: return 'bg-green-500/20 text-green-400';
    }
};

const canUpgrade = (purchase) => {
    if (!purchase.is_active) return false;
    
    const packageOrder = ['entry', 'lite', 'pro', 'bariplus'];
    const currentIndex = packageOrder.indexOf(purchase.package_type);
    return currentIndex < packageOrder.length - 1;
};

const renewPackage = async (id) => {
    if (!confirm('Are you sure you want to renew this package?')) return;

    try {
        await router.post(`/packages/${id}/renew`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                }
            },
            onError: (errors) => {
                console.error('Error renewing package:', errors);
                alert('Failed to renew package.');
            }
        });
    } catch (error) {
        console.error('Error renewing package:', error);
        alert('An unexpected error occurred.');
    }
};

const upgradePackage = async (id) => {
    const purchase = props.purchases.data.find(p => p.id === id);
    if (!purchase) return;

    const packageOrder = ['entry', 'lite', 'pro', 'bariplus'];
    const currentIndex = packageOrder.indexOf(purchase.package_type);
    const nextPackage = packageOrder[currentIndex + 1];

    if (!nextPackage) {
        alert('You are already on the highest package!');
        return;
    }

    if (!confirm(`Are you sure you want to upgrade to ${formatPackageType(nextPackage)} package?`)) return;

    try {
        await router.post(`/packages/${id}/upgrade`, {
            upgrade_to: nextPackage
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                }
            },
            onError: (errors) => {
                console.error('Error upgrading package:', errors);
                alert('Failed to upgrade package.');
            }
        });
    } catch (error) {
        console.error('Error upgrading package:', error);
        alert('An unexpected error occurred.');
    }
};

const previousPage = () => {
    if (props.purchases.prev_page_url) {
        router.visit(props.purchases.prev_page_url);
    }
};

const nextPage = () => {
    if (props.purchases.next_page_url) {
        router.visit(props.purchases.next_page_url);
    }
};
</script>