<template>
    <DashboardLayout title="Starlink">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Satellite :size="16" class="text-blue-400" />
            <span>Starlink</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Dashboard</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Starlink Internet Service</h1>
                    <p class="text-blue-300">High-speed satellite internet for everyone, everywhere</p>
                </div>

                <!-- User Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Balance Card -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Wallet :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Your Balance</h3>
                        </div>
                        <p class="text-3xl font-bold text-purple-400 mb-2">KES {{ formatCurrency(userBalance) }}</p>
                        <p class="text-sm text-purple-300">Available for Starlink subscription</p>
                    </div>
                    
                    <!-- Active Subscription Card -->
                    <div v-if="activeSubscription" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Satellite :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Active Subscription</h3>
                        </div>
                        <p class="text-2xl font-bold text-white mb-1">{{ activeSubscription.package_name }}</p>
                        <p class="text-sm text-green-300 mb-2">Expires in {{ activeSubscription.days_remaining }} days</p>
                        <Link 
                            href="/starlink/packages"
                            class="text-sm text-green-400 hover:text-green-300 flex items-center space-x-1"
                        >
                            <span>Manage Subscription</span>
                            <ArrowRight :size="14" />
                        </Link>
                    </div>
                    
                    <!-- No Subscription Card -->
                    <div v-else class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <SatelliteDish :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">No Active Subscription</h3>
                        </div>
                        <p class="text-sm text-blue-300 mb-3">Subscribe to start using Starlink internet</p>
                        <Link 
                            href="/starlink/packages"
                            class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <Satellite :size="16" />
                            <span>Browse Packages</span>
                        </Link>
                    </div>
                    
                    <!-- Quick Links Card -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Zap :size="24" class="text-orange-400" />
                            <h3 class="text-lg font-bold text-white">Quick Actions</h3>
                        </div>
                        <div class="space-y-2">
                            <Link 
                                href="/starlink/packages"
                                class="flex items-center justify-between text-orange-300 hover:text-orange-200 transition-colors"
                            >
                                <span>View All Packages</span>
                                <ArrowRight :size="14" />
                            </Link>
                            <Link 
                                v-if="activeSubscription"
                                :href="`/starlink/subscription/${activeSubscription.id}`"
                                class="flex items-center justify-between text-orange-300 hover:text-orange-200 transition-colors"
                            >
                                <span>View Subscription Details</span>
                                <ArrowRight :size="14" />
                            </Link>
                            <Link 
                                href="/wallet/deposit"
                                class="flex items-center justify-between text-orange-300 hover:text-orange-200 transition-colors"
                            >
                                <span>Deposit Funds</span>
                                <ArrowRight :size="14" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Active Subscription Details -->
                <div v-if="activeSubscription" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                            <Satellite :size="20" class="text-green-400" />
                            <span>Your Active Subscription</span>
                        </h3>
                        <Link 
                            :href="`/starlink/subscription/${activeSubscription.id}`"
                            class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                        >
                            <span>View Details</span>
                            <ArrowRight :size="14" />
                        </Link>
                    </div>
                    
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Subscription Info -->
                            <div>
                                <h4 class="text-lg font-bold text-white mb-3">Subscription Details</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Subscription Code:</span>
                                        <code class="text-sm font-mono text-white">{{ activeSubscription.subscription_code }}</code>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Status:</span>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold" :style="`background-color: ${getStatusBgColor(activeSubscription.status_color)}; color: ${getStatusTextColor(activeSubscription.status_color)}`">
                                            {{ activeSubscription.status_text }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Amount Paid:</span>
                                        <span class="text-sm font-semibold text-green-400">{{ activeSubscription.formatted_amount }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Package Info -->
                            <div>
                                <h4 class="text-lg font-bold text-white mb-3">Package Features</h4>
                                <ul class="space-y-1">
                                    <li v-for="(feature, index) in activeSubscription.package_features.slice(0, 3)" :key="index" 
                                        class="flex items-start space-x-2 text-sm">
                                        <CheckCircle :size="12" class="text-green-400 mt-0.5 flex-shrink-0" />
                                        <span class="text-green-100">{{ feature }}</span>
                                    </li>
                                    <li v-if="activeSubscription.package_features.length > 3" class="text-xs text-green-300 pl-6">
                                        +{{ activeSubscription.package_features.length - 3 }} more features
                                    </li>
                                </ul>
                            </div>
                            
                            <!-- Timeline -->
                            <div>
                                <h4 class="text-lg font-bold text-white mb-3">Timeline</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Activated:</span>
                                        <span class="text-sm font-semibold">{{ activeSubscription.activated_at }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Expires:</span>
                                        <span class="text-sm font-semibold">{{ activeSubscription.expires_at }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm opacity-75">Days Remaining:</span>
                                        <span class="text-sm font-semibold text-green-400">{{ activeSubscription.days_remaining }} days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Packages -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                            <Package :size="20" />
                            <span>Featured Starlink Packages</span>
                        </h3>
                        <Link 
                            href="/starlink/packages"
                            class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                        >
                            <span>View All Packages</span>
                            <ArrowRight :size="14" />
                        </Link>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div 
                            v-for="pkg in packages.slice(0, 4)" 
                            :key="pkg.id"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200"
                        >
                            <div class="text-center mb-4">
                                <Satellite :size="32" class="mx-auto text-blue-400 mb-3" />
                                <h4 class="text-lg font-bold text-white mb-1">{{ pkg.name }}</h4>
                                <p class="text-xs text-blue-300">{{ pkg.description.substring(0, 60) }}...</p>
                            </div>
                            
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-sm opacity-75">Speed</p>
                                        <p class="text-lg font-bold text-white">{{ pkg.speed_mbps }} Mbps</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm opacity-75">Data</p>
                                        <p class="text-lg font-bold text-green-400">{{ pkg.data_limit_text }}</p>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm opacity-75 mb-2">Key Features:</p>
                                    <ul class="space-y-1">
                                        <li v-for="(feature, index) in pkg.features.slice(0, 2)" :key="index" 
                                            class="flex items-start space-x-2 text-xs">
                                            <CheckCircle :size="10" class="text-green-400 mt-0.5 flex-shrink-0" />
                                            <span class="text-green-100">{{ feature }}</span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm opacity-75">Setup Fee</span>
                                        <span class="text-xl font-bold text-white">{{ pkg.formatted_price }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm opacity-75">Monthly Fee</span>
                                        <span class="text-lg font-bold text-green-400">{{ pkg.formatted_monthly_fee }}/month</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button
                                @click="subscribeToPackage(pkg.id)"
                                :disabled="activeSubscription || processing"
                                class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 text-sm"
                            >
                                {{ activeSubscription ? 'Already Subscribed' : 'Subscribe Now' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Subscription History -->
                <div v-if="subscriptions.length > 0" class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Recent Subscriptions</h3>
                    
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl shadow-2xl shadow-purple-500/20 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-black/30">
                                        <th class="text-left p-4 text-purple-300 font-semibold">Subscription Code</th>
                                        <th class="text-left p-4 text-purple-300 font-semibold">Package</th>
                                        <th class="text-left p-4 text-purple-300 font-semibold">Amount</th>
                                        <th class="text-left p-4 text-purple-300 font-semibold">Status</th>
                                        <th class="text-left p-4 text-purple-300 font-semibold">Date</th>
                                        <th class="text-left p-4 text-purple-300 font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr 
                                        v-for="subscription in subscriptions" 
                                        :key="subscription.id"
                                        class="border-b border-purple-500/10 hover:bg-black/20 transition-colors duration-200"
                                    >
                                        <td class="p-4">
                                            <code class="text-white font-mono text-sm">{{ subscription.subscription_code }}</code>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-white font-semibold">{{ subscription.package_name }}</p>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-lg font-bold text-green-400">{{ subscription.formatted_amount }}</p>
                                        </td>
                                        <td class="p-4">
                                            <span 
                                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                                :style="`background-color: ${getStatusBgColor(subscription.status_color)}; color: ${getStatusTextColor(subscription.status_color)}`"
                                            >
                                                {{ subscription.status_text }}
                                            </span>
                                        </td>
                                        <td class="p-4">
                                            <p class="text-white">{{ subscription.subscribed_at }}</p>
                                        </td>
                                        <td class="p-4">
                                            <Link 
                                                :href="`/starlink/subscription/${subscription.id}`"
                                                class="text-purple-400 hover:text-purple-300 flex items-center space-x-1"
                                            >
                                                <Eye :size="16" />
                                                <span class="text-sm">View</span>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Why Choose Starlink?</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <Zap :size="24" class="text-green-400" />
                                <h4 class="text-lg font-bold text-white">High Speed</h4>
                            </div>
                            <p class="text-green-300 text-sm">Enjoy download speeds from 50 Mbps to 500 Mbps depending on your package.</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <Globe :size="24" class="text-blue-400" />
                                <h4 class="text-lg font-bold text-white">Global Coverage</h4>
                            </div>
                            <p class="text-blue-300 text-sm">Access high-speed internet anywhere with Starlink's satellite network.</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <div class="flex items-center space-x-3 mb-3">
                                <Shield :size="24" class="text-purple-400" />
                                <h4 class="text-lg font-bold text-white">Reliable Service</h4>
                            </div>
                            <p class="text-purple-300 text-sm">24/7 customer support and reliable connectivity with minimal downtime.</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Link 
                        href="/starlink/packages"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <Package :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">All Packages</h4>
                                <p class="text-blue-400 text-sm">Browse all Starlink packages</p>
                            </div>
                        </div>
                    </Link>
                    
                    <Link 
                        href="/wallet/deposit"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 hover:border-green-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <Wallet :size="24" class="text-green-400 group-hover:text-green-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-green-300">Deposit Funds</h4>
                                <p class="text-green-400 text-sm">Add funds to your wallet</p>
                            </div>
                        </div>
                    </Link>
                    
                    <Link 
                        href="/help/starlink"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <HelpCircle :size="24" class="text-purple-400 group-hover:text-purple-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-purple-300">Help & Support</h4>
                                <p class="text-purple-400 text-sm">Get help with Starlink</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Satellite, Wallet, ArrowRight, Zap,
    SatelliteDish, Package, CheckCircle,
    Eye, Globe, Shield, HelpCircle
} from 'lucide-vue-next';

const props = defineProps({
    packages: {
        type: Array,
        default: () => []
    },
    activeSubscription: {
        type: Object,
        default: null
    },
    subscriptions: {
        type: Array,
        default: () => []
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const processing = ref(false);

const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getStatusBgColor = (color) => {
    const colors = {
        green: 'rgba(34, 197, 94, 0.2)',
        yellow: 'rgba(250, 204, 21, 0.2)',
        orange: 'rgba(251, 146, 60, 0.2)',
        red: 'rgba(239, 68, 68, 0.2)',
        gray: 'rgba(100, 116, 139, 0.2)',
        blue: 'rgba(59, 130, 246, 0.2)',
    };
    return colors[color] || colors.gray;
};

const getStatusTextColor = (color) => {
    const colors = {
        green: '#4ade80',
        yellow: '#fbbf24',
        orange: '#fb923c',
        red: '#f87171',
        gray: '#94a3b8',
        blue: '#60a5fa',
    };
    return colors[color] || colors.gray;
};

const subscribeToPackage = async (packageId) => {
    if (props.activeSubscription) {
        alert('You already have an active subscription. Please cancel it before subscribing to a new one.');
        return;
    }

    const packageToSubscribe = props.packages.find(p => p.id === packageId);
    if (!packageToSubscribe) return;

    if (parseFloat(props.userBalance) < packageToSubscribe.price) {
        alert(`Insufficient balance. You need KES ${formatCurrency(packageToSubscribe.price)} to subscribe to this package.`);
        return;
    }

    if (!confirm(`Are you sure you want to subscribe to ${packageToSubscribe.name} for KES ${formatCurrency(packageToSubscribe.price)}?`)) {
        return;
    }

    processing.value = true;

    try {
        await router.post('/starlink/subscribe', {
            package_id: packageId
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
                console.error('Error subscribing:', errors);
                alert('Failed to subscribe. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error subscribing:', error);
        alert('An unexpected error occurred.');
    } finally {
        processing.value = false;
    }
};
</script>