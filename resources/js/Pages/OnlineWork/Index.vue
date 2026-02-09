<template>
    <DashboardLayout title="Online Work Accounts">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Briefcase :size="16" class="text-purple-400" />
            <span>Online Work</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Accounts</span>
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
                                <p class="text-purple-300 text-sm mb-1">Active Accounts</p>
                                <p class="text-2xl font-bold text-white">{{ activeAccounts.filter(a => a.is_active).length }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platforms Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="(platform, key) in platforms" 
                        :key="key"
                        :class="getPlatformColor(key)"
                        class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl"
                    >
                        <div class="text-center mb-6">
                            <component :is="getPlatformIcon(key)" :size="48" class="mx-auto mb-4" :class="getIconColor(key)" />
                            <h3 class="text-2xl font-bold text-white mb-2">{{ platform.name }}</h3>
                            <div class="text-3xl font-bold text-white mb-1">KES {{ platform.amount.toLocaleString() }}</div>
                            <p class="opacity-75 text-sm">{{ platform.duration_days }} days validity</p>
                            <p class="text-sm opacity-90 mt-2">{{ platform.description }}</p>
                        </div>

                        <ul class="space-y-2 mb-6">
                            <li v-for="(feature, index) in platform.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="purchaseAccount(key)"
                            :disabled="!hasSufficientBalance(platform.amount) || processing"
                            :class="getButtonColor(key)"
                            class="w-full text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing && selectedPlatform === key" :size="20" class="animate-spin" />
                            <span v-else>Purchase Account</span>
                        </button>
                    </div>
                </div>

                <!-- Active Accounts Section -->
                <div v-if="activeAccounts.length > 0" class="mb-8">
                    <h3 class="text-xl font-bold text-white mb-6">Your Active Accounts</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div 
                            v-for="account in activeAccounts.filter(a => a.is_active)" 
                            :key="account.id"
                            :class="getPlatformColor(account.platform)"
                            class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <component :is="getPlatformIcon(account.platform)" :size="24" :class="getIconColor(account.platform)" />
                                    <div>
                                        <h4 class="text-lg font-bold text-white">{{ account.platform_name }}</h4>
                                        <p class="text-sm opacity-75">{{ account.account_details.description }}</p>
                                    </div>
                                </div>
                                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-xs font-semibold">
                                    Active
                                </span>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Purchase Date</span>
                                    <span class="text-sm font-semibold">{{ account.purchased_at }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Expires</span>
                                    <span class="text-sm font-semibold">{{ account.expires_at }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm opacity-75">Days Remaining</span>
                                    <span class="text-sm font-semibold">{{ account.days_remaining }} days</span>
                                </div>
                                
                                <!-- Show login credentials -->
                                <div v-if="account.login_credentials" class="mt-4 pt-4 border-t border-white/10">
                                    <p class="text-sm opacity-75 mb-2">Account Details</p>
                                    <div class="space-y-2">
                                        <div v-if="account.login_credentials.email" class="flex justify-between items-center">
                                            <span class="text-xs opacity-75">Email:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-mono">{{ account.login_credentials.email }}</span>
                                                <button @click="copyToClipboard(account.login_credentials.email)" 
                                                        class="text-blue-400 hover:text-blue-300">
                                                    <Copy :size="14" />
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="account.login_credentials.password" class="flex justify-between items-center">
                                            <span class="text-xs opacity-75">Password:</span>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-mono">••••••••</span>
                                                <button @click="copyToClipboard(account.login_credentials.password)" 
                                                        class="text-blue-400 hover:text-blue-300">
                                                    <Copy :size="14" />
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="account.login_credentials.login_url" class="mt-3">
                                            <a :href="account.login_credentials.login_url" 
                                               target="_blank"
                                               class="inline-flex items-center space-x-2 bg-black/30 hover:bg-black/50 text-blue-400 px-3 py-2 rounded-lg text-sm transition-colors">
                                                <ExternalLink :size="14" />
                                                <span>Go to Platform</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link 
                        href="/online-work/history"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3">
                            <History :size="24" class="text-purple-400 group-hover:text-purple-300" />
                            <div>
                                <h4 class="text-white font-bold group-hover:text-purple-300">Purchase History</h4>
                                <p class="text-purple-400 text-sm">View all your account purchases</p>
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
    Briefcase, Building2, Cloud, Globe, Users,
    PenTool, Database, Monitor, FileText, CheckCircle,
    Loader2, Copy, ExternalLink, History, LayoutDashboard
} from 'lucide-vue-next';

const props = defineProps({
    platforms: Object,
    activeAccounts: Array,
    userBalance: Number,
});

const processing = ref(false);
const selectedPlatform = ref(null);

const hasSufficientBalance = (amount) => {
    return props.userBalance >= amount;
};

const getPlatformColor = (platform) => {
    const colors = {
        'handshake': 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30 shadow-blue-500/20',
        'telus': 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30 shadow-green-500/20',
        'imerit': 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30 shadow-purple-500/20',
        'atlas_capture': 'bg-gradient-to-br from-red-600/40 to-orange-600/40 border-red-500/30 shadow-red-500/20',
        'crowdgen': 'bg-gradient-to-br from-yellow-600/40 to-amber-600/40 border-yellow-500/30 shadow-yellow-500/20',
        'mindrift': 'bg-gradient-to-br from-indigo-600/40 to-violet-600/40 border-indigo-500/30 shadow-indigo-500/20',
        'dataannotation': 'bg-gradient-to-br from-teal-600/40 to-cyan-600/40 border-teal-500/30 shadow-teal-500/20',
        'cloudworkers': 'bg-gradient-to-br from-gray-600/40 to-slate-600/40 border-gray-500/30 shadow-gray-500/20',
        'textfactory': 'bg-gradient-to-br from-rose-600/40 to-pink-600/40 border-rose-500/30 shadow-rose-500/20',
    };
    return colors[platform] || 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
};

const getIconColor = (platform) => {
    const colors = {
        'handshake': 'text-blue-400',
        'telus': 'text-green-400',
        'imerit': 'text-purple-400',
        'atlas_capture': 'text-red-400',
        'crowdgen': 'text-yellow-400',
        'mindrift': 'text-indigo-400',
        'dataannotation': 'text-teal-400',
        'cloudworkers': 'text-gray-400',
        'textfactory': 'text-rose-400',
    };
    return colors[platform] || 'text-slate-400';
};

const getButtonColor = (platform) => {
    const colors = {
        'handshake': 'bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500',
        'telus': 'bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500',
        'imerit': 'bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500',
        'atlas_capture': 'bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500',
        'crowdgen': 'bg-gradient-to-r from-yellow-600 to-amber-600 hover:from-yellow-500 hover:to-amber-500',
        'mindrift': 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500',
        'dataannotation': 'bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500',
        'cloudworkers': 'bg-gradient-to-r from-gray-600 to-slate-600 hover:from-gray-500 hover:to-slate-500',
        'textfactory': 'bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500',
    };
    return colors[platform] + ' disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed';
};

const getPlatformIcon = (platform) => {
    const icons = {
        'handshake': Briefcase,
        'telus': Building2,
        'imerit': Cloud,
        'atlas_capture': Globe,
        'crowdgen': Users,
        'mindrift': PenTool,
        'dataannotation': Database,
        'cloudworkers': Monitor,
        'textfactory': FileText,
    };
    return icons[platform] || Briefcase;
};

const purchaseAccount = async (platform) => {
    const platformData = props.platforms[platform];
    if (!hasSufficientBalance(platformData.amount)) {
        alert(`Insufficient balance. You need KES ${platformData.amount.toLocaleString()}`);
        return;
    }

    if (!confirm(`Are you sure you want to purchase ${platformData.name} for KES ${platformData.amount.toLocaleString()}?`)) {
        return;
    }

    selectedPlatform.value = platform;
    processing.value = true;

    try {
        await router.post('/online-work/purchase', {
            platform: platform
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
                console.error('Error purchasing account:', errors);
                alert('Failed to purchase account. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error purchasing account:', error);
        alert('An unexpected error occurred.');
    } finally {
        processing.value = false;
        selectedPlatform.value = null;
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
</script>