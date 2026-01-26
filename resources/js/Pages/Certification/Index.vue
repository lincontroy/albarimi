<template>
    <DashboardLayout title="Certification">
        <!-- Breadcrumb - Updated from "Dollar Zone" to "Certification" -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <BadgeCheck :size="16" class="text-blue-400" />
            <span>Certification</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Packages</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- User Balance -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Your Balance</h3>
                                <p class="text-2xl font-bold text-purple-400">KES {{ userBalance.toLocaleString() }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-purple-300 text-sm mb-1">Active Packages</p>
                                <p class="text-2xl font-bold text-white">{{ activePurchases.filter(p => p.is_active).length }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Packages Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Certification Package -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="text-center mb-6">
                            <Award :size="48" class="mx-auto text-blue-400 mb-4" />
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.certification.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.certification.amount.toLocaleString() }}</div>
                            <p class="text-blue-200 text-sm">{{ packages.certification.duration_days }} days validity</p>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.certification.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('certification')"
                            :disabled="!hasSufficientBalance(packages.certification.amount) || processing"
                            class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'certification'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>

                    <!-- Access Code Package -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="text-center mb-6">
                            <Key :size="48" class="mx-auto text-green-400 mb-4" />
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.access_code.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.access_code.amount.toLocaleString() }}</div>
                            <p class="text-green-200 text-sm">{{ packages.access_code.duration_days }} days validity</p>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.access_code.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-green-100">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('access_code')"
                            :disabled="!hasSufficientBalance(packages.access_code.amount) || processing"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'access_code'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>

                    <!-- Verification Package -->
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="text-center mb-6">
                            <ShieldCheck :size="48" class="mx-auto text-purple-400 mb-4" />
                            <h3 class="text-2xl font-bold text-white mb-2">{{ packages.verification.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ packages.verification.amount.toLocaleString() }}</div>
                            <p class="text-purple-200 text-sm">{{ packages.verification.duration_days }} days validity</p>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in packages.verification.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-purple-100">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchasePackage('verification')"
                            :disabled="!hasSufficientBalance(packages.verification.amount) || processing"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPackage === 'verification'" :size="20" class="animate-spin" />
                            <span v-else>Purchase Package</span>
                        </button>
                    </div>
                </div>

                <!-- Active Packages Section -->
                <div v-if="activePurchases.length > 0" class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-6">Your Active Packages</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div 
                            v-for="purchase in activePurchases.filter(p => p.is_active)" 
                            :key="purchase.id"
                            :class="getPackageColor(purchase.package_type)"
                            class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <component :is="getPackageIcon(purchase.package_type)" :size="24" />
                                    <h4 class="text-lg font-bold text-white">{{ purchase.package_name }}</h4>
                                </div>
                                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Purchase Date</span>
                                    <span class="text-sm font-semibold">{{ purchase.activated_at }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Expires</span>
                                    <span class="text-sm font-semibold">{{ purchase.expires_at }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Days Remaining</span>
                                    <span class="text-sm font-semibold">{{ purchase.days_remaining }} days</span>
                                </div>
                                
                                <!-- Show relevant code -->
                                <div v-if="purchase.codes.certification" class="mt-4">
                                    <p class="text-sm opacity-75 mb-1">Certification Code</p>
                                    <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                        <code class="text-white font-mono text-sm">{{ purchase.codes.certification }}</code>
                                        <button @click="copyToClipboard(purchase.codes.certification)" class="text-blue-400 hover:text-blue-300">
                                            <Copy :size="16" />
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="purchase.codes.access" class="mt-4">
                                    <p class="text-sm opacity-75 mb-1">Access Code</p>
                                    <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                        <code class="text-white font-mono text-sm">{{ purchase.codes.access }}</code>
                                        <button @click="copyToClipboard(purchase.codes.access)" class="text-blue-400 hover:text-blue-300">
                                            <Copy :size="16" />
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="purchase.codes.verification" class="mt-4">
                                    <p class="text-sm opacity-75 mb-1">Verification Code</p>
                                    <div class="flex items-center justify-between bg-black/30 rounded-xl p-3">
                                        <code class="text-white font-mono text-sm">{{ purchase.codes.verification }}</code>
                                        <button @click="copyToClipboard(purchase.codes.verification)" class="text-blue-400 hover:text-blue-300">
                                            <Copy :size="16" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check Code Status -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">Check Package Status</h3>
                    <div class="flex items-center space-x-2">
                        <input
                            v-model="checkCode"
                            type="text"
                            placeholder="Enter certification/access/verification code"
                            class="flex-1 bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-blue-400"
                        />
                        <button
                            @click="checkPackageStatus"
                            :disabled="!checkCode || checkingStatus"
                            class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50"
                        >
                            <Loader2 v-if="checkingStatus" :size="20" class="animate-spin" />
                            <span v-else>Check</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        href="/certification/history"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <History :size="24" class="text-purple-400 group-hover:text-purple-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-purple-300">Purchase History</h4>
                                <p class="text-purple-400 text-sm">View all your certification purchases</p>
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
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Award, Key, ShieldCheck, CheckCircle,
    Loader2, Copy, History, LayoutDashboard,
    BadgeCheck
} from 'lucide-vue-next';

const props = defineProps({
    packages: Object,
    activePurchases: Array,
    userBalance: Number,
});

const processing = ref(false);
const selectedPackage = ref(null);
const checkCode = ref('');
const checkingStatus = ref(false);

const hasSufficientBalance = (amount) => {
    return props.userBalance >= amount;
};

const getPackageColor = (type) => {
    switch(type) {
        case 'certification': return 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30 shadow-blue-500/20';
        case 'access_code': return 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30 shadow-green-500/20';
        case 'verification': return 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30 shadow-purple-500/20';
        default: return 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
    }
};

const getPackageIcon = (type) => {
    switch(type) {
        case 'certification': return Award;
        case 'access_code': return Key;
        case 'verification': return ShieldCheck;
        default: return Award;
    }
};

const purchasePackage = async (packageType) => {
    if (!hasSufficientBalance(props.packages[packageType].amount)) {
        alert(`Insufficient balance. You need KES ${props.packages[packageType].amount.toLocaleString()}`);
        return;
    }

    if (!confirm(`Are you sure you want to purchase ${props.packages[packageType].name} for KES ${props.packages[packageType].amount.toLocaleString()}?`)) {
        return;
    }

    selectedPackage.value = packageType;
    processing.value = true;

    try {
        // Changed from '/dollar-zone/certification/purchase' to '/certification/purchase'
        await router.post('/certification/purchase', {
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

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        alert('Copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard.');
    }
};

const checkPackageStatus = async () => {
    if (!checkCode.value.trim()) return;

    checkingStatus.value = true;

    try {
        // Changed from '/dollar-zone/certification/check-status' to '/certification/check-status'
        const response = await fetch('/certification/check-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ code: checkCode.value })
        });

        const data = await response.json();

        if (data.valid) {
            alert(`Package: ${data.data.package_name}\nStatus: ${data.data.status}\nExpires: ${data.data.expires_at || 'Never'}\nActive: ${data.data.is_active ? 'Yes' : 'No'}`);
        } else {
            alert(data.message || 'Invalid code.');
        }
    } catch (error) {
        console.error('Error checking status:', error);
        alert('Failed to check package status.');
    } finally {
        checkingStatus.value = false;
    }
};
</script>