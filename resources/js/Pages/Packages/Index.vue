<template>
    <DashboardLayout title="Earning Packages">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <Package :size="16" class="text-green-400" />
            <span>Packages</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Earning Plans</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- User Balance -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Your Balance</h3>
                                <p class="text-2xl font-bold text-green-400">KES {{ userBalance.toLocaleString() }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-green-300 text-sm mb-1">Active Package</p>
                                <p v-if="currentActivePackage" class="text-2xl font-bold text-white">{{ currentActivePackage.package_name }}</p>
                                <p v-else class="text-2xl font-bold text-yellow-400">None</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Packages Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Entry Package -->
                    <div :class="getPackageColor('entry')" class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="relative">
                                <div class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                    Entry
                                </div>
                                <Package :size="48" class="mx-auto text-blue-400 mb-4" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.entry.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.entry.amount.toLocaleString() }}</div>
                            <p class="text-blue-200 text-sm">{{ packages.entry.duration_days }} days</p>
                            <p class="text-sm opacity-90 mt-2">{{ packages.entry.description }}</p>
                        </div>

                        <!-- Earnings Info -->
                        <div class="mb-6 p-4 bg-black/30 rounded-xl">
                            <div class="text-center mb-3">
                                <p class="text-sm opacity-75">Monthly Potential</p>
                                <p class="text-xl font-bold text-green-400">KES {{ packages.entry.monthly_earnings.toLocaleString() }}</p>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="opacity-75">Daily:</span>
                                <span class="font-semibold text-green-300">KES {{ packages.entry.daily_earnings }}</span>
                            </div>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.entry.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('entry')"
                            :disabled="!hasSufficientBalance(packages.entry.amount) || processing"
                            class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'entry'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>

                    <!-- Lite Package -->
                    <div :class="getPackageColor('lite')" class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="relative">
                                <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                    Lite
                                </div>
                                <Package :size="48" class="mx-auto text-green-400 mb-4" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.lite.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.lite.amount.toLocaleString() }}</div>
                            <p class="text-green-200 text-sm">{{ packages.lite.duration_days }} days</p>
                            <p class="text-sm opacity-90 mt-2">{{ packages.lite.description }}</p>
                        </div>

                        <!-- Earnings Info -->
                        <div class="mb-6 p-4 bg-black/30 rounded-xl">
                            <div class="text-center mb-3">
                                <p class="text-sm opacity-75">Monthly Potential</p>
                                <p class="text-xl font-bold text-green-400">KES {{ packages.lite.monthly_earnings.toLocaleString() }}</p>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="opacity-75">Daily:</span>
                                <span class="font-semibold text-green-300">KES {{ packages.lite.daily_earnings }}</span>
                            </div>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.lite.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('lite')"
                            :disabled="!hasSufficientBalance(packages.lite.amount) || processing"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'lite'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>

                    <!-- Pro Package -->
                    <div :class="getPackageColor('pro')" class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="relative">
                                <div class="absolute -top-2 -right-2 bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                                    Pro
                                </div>
                                <Package :size="48" class="mx-auto text-purple-400 mb-4" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.pro.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.pro.amount.toLocaleString() }}</div>
                            <p class="text-purple-200 text-sm">{{ packages.pro.duration_days }} days</p>
                            <p class="text-sm opacity-90 mt-2">{{ packages.pro.description }}</p>
                        </div>

                        <!-- Earnings Info -->
                        <div class="mb-6 p-4 bg-black/30 rounded-xl">
                            <div class="text-center mb-3">
                                <p class="text-sm opacity-75">Monthly Potential</p>
                                <p class="text-xl font-bold text-green-400">KES {{ packages.pro.monthly_earnings.toLocaleString() }}</p>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="opacity-75">Daily:</span>
                                <span class="font-semibold text-green-300">KES {{ packages.pro.daily_earnings }}</span>
                            </div>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.pro.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('pro')"
                            :disabled="!hasSufficientBalance(packages.pro.amount) || processing"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'pro'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>

                    <!-- BariPlus Package -->
                    <div :class="getPackageColor('bariplus')" class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl">
                        <div class="text-center mb-6">
                            <div class="relative">
                                <div class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-full">
                                    VIP
                                </div>
                                <Crown :size="48" class="mx-auto text-orange-400 mb-4" />
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.bariplus.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.bariplus.amount.toLocaleString() }}</div>
                            <p class="text-orange-200 text-sm">{{ packages.bariplus.duration_days }} days</p>
                            <p class="text-sm opacity-90 mt-2">{{ packages.bariplus.description }}</p>
                        </div>

                        <!-- Earnings Info -->
                        <div class="mb-6 p-4 bg-black/30 rounded-xl">
                            <div class="text-center mb-3">
                                <p class="text-sm opacity-75">Monthly Potential</p>
                                <p class="text-xl font-bold text-green-400">KES {{ packages.bariplus.monthly_earnings.toLocaleString() }}</p>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="opacity-75">Daily:</span>
                                <span class="font-semibold text-green-300">KES {{ packages.bariplus.daily_earnings }}</span>
                            </div>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.bariplus.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('bariplus')"
                            :disabled="!hasSufficientBalance(packages.bariplus.amount) || processing"
                            class="w-full bg-gradient-to-r from-orange-600 to-yellow-600 hover:from-orange-500 hover:to-yellow-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-orange-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'bariplus'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>
                </div>

                <!-- Active Package Section -->
                <div v-if="activePackages.length > 0" class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-6">Your Active Package</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div 
                            v-for="pkg in activePackages.filter(p => p.is_active)" 
                            :key="pkg.id"
                            :class="getPackageColor(pkg.package_type)"
                            class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl"
                        >
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-4">
                                    <component :is="getPackageIcon(pkg.package_type)" :size="32" :class="getIconColor(pkg.package_type)" />
                                    <div>
                                        <h4 class="text-2xl font-bold text-white">{{ pkg.package_name }}</h4>
                                        <p class="text-white/75">{{ packages[pkg.package_type].description }}</p>
                                    </div>
                                </div>
                                <span class="bg-green-500/20 text-green-400 px-4 py-2 rounded-full text-sm font-semibold">
                                    Active
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-2">Package Info</p>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="opacity-75">Investment:</span>
                                            <span class="font-semibold">KES {{ pkg.amount.toLocaleString() }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="opacity-75">Activated:</span>
                                            <span>{{ pkg.activated_at }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="opacity-75">Expires:</span>
                                            <span>{{ pkg.expires_at }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="opacity-75">Days Left:</span>
                                            <span class="font-semibold" :class="pkg.days_remaining < 7 ? 'text-red-400' : 'text-green-400'">
                                                {{ pkg.days_remaining }} days
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-2">Earnings Potential</p>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="opacity-75 text-xs">Daily Earnings:</p>
                                            <p class="text-xl font-bold text-green-400">
                                                KES {{ packages[pkg.package_type].daily_earnings.toLocaleString() }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="opacity-75 text-xs">Monthly Potential:</p>
                                            <p class="text-xl font-bold text-green-400">
                                                KES {{ packages[pkg.package_type].monthly_earnings.toLocaleString() }}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="opacity-75 text-xs">Total Potential:</p>
                                            <p class="text-xl font-bold text-green-400">
                                                KES {{ pkg.potential_earnings.toLocaleString() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-black/30 rounded-xl p-4">
                                    <p class="text-sm opacity-75 mb-2">Quick Actions</p>
                                    <div class="space-y-3">
                                        <button 
                                            @click="renewPackage(pkg.id)"
                                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center justify-center space-x-2"
                                        >
                                            <RefreshCw :size="16" />
                                            <span>Renew Package</span>
                                        </button>
                                        <Link 
                                            :href="'/packages/' + pkg.id"
                                            class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 flex items-center justify-center space-x-2"
                                        >
                                            <Eye :size="16" />
                                            <span>View Details</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        href="/packages/history"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <History :size="24" class="text-green-400 group-hover:text-green-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-green-300">Purchase History</h4>
                                <p class="text-green-400 text-sm">View all your package purchases</p>
                            </div>
                        </div>
                    </Link>
                    
                    <Link 
                        href="/dashboard"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <LayoutDashboard :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">Dashboard</h4>
                                <p class="text-blue-400 text-sm">Back to dashboard</p>
                            </div>
                        </div>
                    </Link>
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
    Package, Crown, CheckCircle, Loader2,
    RefreshCw, Eye, History, LayoutDashboard
} from 'lucide-vue-next';

const props = defineProps({
    packages: Object,
    activePackages: Array,
    userBalance: Number,
});

const processing = ref(false);
const selectedPackage = ref(null);

const currentActivePackage = computed(() => {
    return props.activePackages.find(p => p.is_active) || null;
});

const hasSufficientBalance = (amount) => {
    return props.userBalance >= amount;
};

const getPackageColor = (type) => {
    const colors = {
        'entry': 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30 shadow-blue-500/20',
        'lite': 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30 shadow-green-500/20',
        'pro': 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30 shadow-purple-500/20',
        'bariplus': 'bg-gradient-to-br from-orange-600/40 to-yellow-600/40 border-orange-500/30 shadow-orange-500/20',
    };
    return colors[type] || 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
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

const getPackageIcon = (type) => {
    const icons = {
        'entry': Package,
        'lite': Package,
        'pro': Package,
        'bariplus': Crown,
    };
    return icons[type] || Package;
};

const purchasePackage = async (packageType) => {
    const packageData = props.packages[packageType];
    if (!hasSufficientBalance(packageData.amount)) {
        alert(`Insufficient balance. You need KES ${packageData.amount.toLocaleString()}`);
        return;
    }

    if (!confirm(`Are you sure you want to purchase ${packageData.name} for KES ${packageData.amount.toLocaleString()}?`)) {
        return;
    }

    selectedPackage.value = packageType;
    processing.value = true;

    try {
        await router.post('/packages/purchase', {
            package_type: packageType
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                } else if (flash.error) {
                    alert(flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error purchasing package:', errors);
                alert('Failed to purchase package. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error purchasing package:', error);
        alert('An unexpected error occurred.');
    } finally {
        processing.value = false;
        selectedPackage.value = null;
    }
};

const renewPackage = async (id) => {
    const packageItem = props.activePackages.find(p => p.id === id);
    if (!packageItem) return;

    if (!confirm(`Are you sure you want to renew ${packageItem.package_name} for KES ${packageItem.amount.toLocaleString()}?`)) {
        return;
    }

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
</script>