<template>
    <DashboardLayout title="Starlink Packages">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Package :size="16" class="text-blue-400" />
            <span>Starlink</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Packages</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Starlink Internet Packages</h1>
                    <p class="text-blue-300">Choose the perfect plan for your internet needs</p>
                </div>

                <!-- User Balance -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Your Balance</h3>
                                <p class="text-2xl font-bold text-purple-400">KES {{ formatCurrency(userBalance) }}</p>
                            </div>
                            <div>
                                <p class="text-purple-300 text-sm mb-1">Available for subscription</p>
                                <Link 
                                    href="/wallet/deposit"
                                    class="text-purple-400 hover:text-purple-300 text-sm flex items-center space-x-1"
                                >
                                    <span>Deposit Funds</span>
                                    <ArrowRight :size="14" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Packages Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Package Cards -->
                    <div 
                        v-for="(pkg, index) in packages" 
                        :key="pkg.id"
                        :class="getPackageColor(index)"
                        class="backdrop-blur-xl border rounded-2xl p-8 shadow-2xl relative overflow-hidden"
                    >
                        <!-- Popular Badge -->
                        <div v-if="index === 1" class="absolute top-4 right-4">
                            <span class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold">
                                Most Popular
                            </span>
                        </div>
                        
                        <!-- Package Header -->
                        <div class="text-center mb-6">
                            <Satellite :size="48" class="mx-auto mb-4" :class="getPackageIconColor(index)" />
                            <h3 class="text-2xl font-bold text-white mb-2">{{ pkg.name }}</h3>
                            <p class="text-sm opacity-75">{{ pkg.description }}</p>
                        </div>
                        
                        <!-- Price Section -->
                        <div class="text-center mb-6">
                            <div class="mb-2">
                                <p class="text-sm opacity-75">Setup Fee</p>
                                <p class="text-4xl font-bold text-white mb-1">{{ pkg.formatted_price }}</p>
                            </div>
                            <div>
                                <p class="text-sm opacity-75">Monthly Fee</p>
                                <p class="text-2xl font-bold" :class="getPackagePriceColor(index)">{{ pkg.formatted_monthly_fee }}/month</p>
                            </div>
                            <div v-if="pkg.monthly_savings > 0" class="mt-2">
                                <p class="text-sm text-green-400">
                                    Save KES {{ formatCurrency(pkg.monthly_savings) }} with {{ pkg.duration_text }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Features -->
                        <div class="mb-6">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="text-center">
                                    <p class="text-sm opacity-75">Speed</p>
                                    <p class="text-xl font-bold text-white">{{ pkg.speed_mbps }} Mbps</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm opacity-75">Data</p>
                                    <p class="text-xl font-bold text-green-400">{{ pkg.data_limit_text }}</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-sm opacity-75 mb-2">Features Included:</p>
                                <ul class="space-y-2">
                                    <li v-for="(feature, featureIndex) in pkg.features" :key="featureIndex" 
                                        class="flex items-start space-x-2 text-sm">
                                        <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                        <span class="text-white">{{ feature }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Subscribe Button -->
                        <button
                            @click="subscribeToPackage(pkg.id)"
                            :disabled="processing || parseFloat(userBalance) < pkg.price"
                            :class="getPackageButtonColor(index)"
                            class="w-full text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center space-x-2 text-lg"
                        >
                            <Loader2 v-if="processing && selectedPackage === pkg.id" :size="20" class="animate-spin" />
                            <template v-else>
                                <Satellite :size="20" />
                                <span>Subscribe Now</span>
                            </template>
                        </button>
                        
                        <!-- Insufficient Balance Warning -->
                        <div v-if="parseFloat(userBalance) < pkg.price" class="mt-3 text-center">
                            <p class="text-red-400 text-sm">
                                Insufficient balance. Need KES {{ formatCurrency(pkg.price - parseFloat(userBalance)) }} more.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Package Comparison</h3>
                    
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl shadow-2xl shadow-blue-500/20 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-black/30">
                                        <th class="text-left p-4 text-blue-300 font-semibold">Feature</th>
                                        <th v-for="pkg in packages" :key="pkg.id" class="text-left p-4 text-blue-300 font-semibold">
                                            {{ pkg.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-blue-500/10">
                                        <td class="p-4 text-white">Download Speed</td>
                                        <td v-for="pkg in packages" :key="pkg.id" class="p-4 text-white">
                                            {{ pkg.speed_mbps }} Mbps
                                        </td>
                                    </tr>
                                    <tr class="border-b border-blue-500/10">
                                        <td class="p-4 text-white">Data Limit</td>
                                        <td v-for="pkg in packages" :key="pkg.id" class="p-4 text-white">
                                            {{ pkg.data_limit_text }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-blue-500/10">
                                        <td class="p-4 text-white">Setup Fee</td>
                                        <td v-for="pkg in packages" :key="pkg.id" class="p-4 text-white">
                                            {{ pkg.formatted_price }}
                                        </td>
                                    </tr>
                                    <tr class="border-b border-blue-500/10">
                                        <td class="p-4 text-white">Monthly Fee</td>
                                        <td v-for="pkg in packages" :key="pkg.id" class="p-4 text-white">
                                            {{ pkg.formatted_monthly_fee }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 text-white">Key Features</td>
                                        <td v-for="pkg in packages" :key="pkg.id" class="p-4">
                                            <ul class="space-y-1">
                                                <li v-for="(feature, index) in pkg.features.slice(0, 2)" :key="index" 
                                                    class="text-xs text-blue-300">
                                                    • {{ feature }}
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-4">Frequently Asked Questions</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h4 class="text-lg font-bold text-white mb-2">How does Starlink work?</h4>
                            <p class="text-purple-300 text-sm">Starlink uses a constellation of low Earth orbit satellites to provide high-speed internet access. The kit includes a satellite dish, router, and cables.</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h4 class="text-lg font-bold text-white mb-2">What's included in the setup fee?</h4>
                            <p class="text-green-300 text-sm">The setup fee includes the Starlink kit (satellite dish, router, power supply, cables) and initial activation of your service.</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                            <h4 class="text-lg font-bold text-white mb-2">Can I cancel anytime?</h4>
                            <p class="text-blue-300 text-sm">Yes, you can cancel your subscription at any time. The service will remain active until the end of your current billing period.</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/20">
                            <h4 class="text-lg font-bold text-white mb-2">How long does delivery take?</h4>
                            <p class="text-orange-300 text-sm">Delivery typically takes 2-4 weeks depending on your location. You'll receive tracking information once your kit ships.</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        href="/starlink"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <ArrowLeft :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-blue-300">Back to Starlink</h4>
                                <p class="text-blue-400 text-sm">Return to main dashboard</p>
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
                                <p class="text-green-400 text-sm">Add funds to subscribe</p>
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
    Package, Satellite, Wallet, ArrowRight,
    ArrowLeft, CheckCircle, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    packages: {
        type: Array,
        default: () => []
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const processing = ref(false);
const selectedPackage = ref(null);

const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getPackageColor = (index) => {
    const colors = [
        'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30 shadow-blue-500/20',
        'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30 shadow-purple-500/20',
        'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30 shadow-green-500/20',
        'bg-gradient-to-br from-orange-600/40 to-red-600/40 border-orange-500/30 shadow-orange-500/20',
    ];
    return colors[index] || colors[0];
};

const getPackageIconColor = (index) => {
    const colors = [
        'text-blue-400',
        'text-purple-400',
        'text-green-400',
        'text-orange-400',
    ];
    return colors[index] || colors[0];
};

const getPackagePriceColor = (index) => {
    const colors = [
        'text-cyan-400',
        'text-pink-400',
        'text-emerald-400',
        'text-red-400',
    ];
    return colors[index] || colors[0];
};

const getPackageButtonColor = (index) => {
    const colors = [
        'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600',
        'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600',
        'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600',
        'bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 disabled:from-slate-600 disabled:to-slate-600',
    ];
    return colors[index] || colors[0];
};

const subscribeToPackage = async (packageId) => {
    const packageToSubscribe = props.packages.find(p => p.id === packageId);
    if (!packageToSubscribe) return;

    if (parseFloat(props.userBalance) < packageToSubscribe.price) {
        alert(`Insufficient balance. You need KES ${formatCurrency(packageToSubscribe.price)} to subscribe to this package.`);
        return;
    }

    if (!confirm(`Are you sure you want to subscribe to ${packageToSubscribe.name}?\n\nSetup Fee: ${packageToSubscribe.formatted_price}\nMonthly Fee: ${packageToSubscribe.formatted_monthly_fee}\n\nTotal initial payment: ${packageToSubscribe.formatted_price}`)) {
        return;
    }

    selectedPackage.value = packageId;
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
                    router.visit('/starlink');
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
        selectedPackage.value = null;
    }
};
</script>