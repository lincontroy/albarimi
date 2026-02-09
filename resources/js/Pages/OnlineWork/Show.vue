<!-- Show.vue -->
<template>
    <DashboardLayout :title="`Account - ${account.platform_name}`">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Briefcase :size="16" class="text-purple-400" />
            <span>Online Work</span>
            <span class="text-purple-400">›</span>
            <Link href="/online-work/history" class="hover:text-purple-300">History</Link>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">{{ account.platform_name }}</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Account Header -->
                <div class="mb-8">
                    <div :class="getPlatformColor(account.platform)" class="backdrop-blur-xl border rounded-2xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-4">
                                <component :is="getPlatformIcon(account.platform)" :size="32" :class="getIconColor(account.platform)" />
                                <div>
                                    <h1 class="text-2xl font-bold text-white">{{ account.platform_name }}</h1>
                                    <p class="text-white/75">{{ account.account_details.description }}</p>
                                </div>
                            </div>
                            <span :class="getStatusColor(account.status)" class="px-4 py-2 rounded-full text-sm font-semibold">
                                {{ account.status.toUpperCase() }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <div class="text-center">
                                <p class="text-sm opacity-75">Amount</p>
                                <p class="text-xl font-bold text-white">KES {{ account.amount.toLocaleString() }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm opacity-75">Purchased</p>
                                <p class="text-lg font-semibold text-white">{{ account.purchased_at }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm opacity-75">Expires</p>
                                <p class="text-lg font-semibold text-white">{{ account.expires_at || 'Never' }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm opacity-75">Days Left</p>
                                <p class="text-xl font-bold" :class="account.days_remaining < 30 ? 'text-red-400' : 'text-green-400'">
                                    {{ account.days_remaining || '∞' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Login Credentials -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                            <Key :size="20" class="text-blue-400" />
                            <span>Login Credentials</span>
                        </h3>
                        
                        <div class="space-y-4" v-if="account.login_credentials">
                            <div v-for="(value, key) in account.login_credentials" :key="key" 
                                 class="border-b border-blue-500/20 pb-3 last:border-0 last:pb-0">
                                <p class="text-sm opacity-75 mb-1 capitalize">{{ key.replace('_', ' ') }}</p>
                                <div class="flex items-center justify-between">
                                    <p class="text-white font-mono text-sm truncate" v-if="key !== 'password'">{{ value }}</p>
                                    <p class="text-white font-mono text-sm" v-else>••••••••</p>
                                    <button @click="copyToClipboard(value)" 
                                            class="text-blue-400 hover:text-blue-300 ml-2 flex-shrink-0">
                                        <Copy :size="16" />
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="account.login_credentials.login_url" class="mt-4">
                                <a :href="account.login_credentials.login_url" 
                                   target="_blank"
                                   class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 flex items-center justify-center space-x-2">
                                    <ExternalLink :size="18" />
                                    <span>Go to Platform</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Account Features -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                            <CheckCircle :size="20" class="text-green-400" />
                            <span>Account Features</span>
                        </h3>
                        
                        <ul class="space-y-2">
                            <li v-for="(feature, index) in account.account_details.features" :key="index" 
                                class="flex items-start space-x-2 text-sm">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-white/90">{{ feature }}</span>
                            </li>
                        </ul>
                        
                        <div class="mt-6 pt-6 border-t border-green-500/20">
                            <p class="text-sm opacity-75 mb-2">Duration</p>
                            <p class="text-white font-semibold">{{ account.account_details.duration_days }} days</p>
                        </div>
                    </div>
                </div>

                <!-- Account Instructions -->
                <div v-if="account.login_credentials?.instructions" class="mt-6">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                            <Info :size="20" class="text-yellow-400" />
                            <span>Instructions</span>
                        </h3>
                        <p class="text-white/90">{{ account.login_credentials.instructions }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <button 
                        v-if="account.is_active && account.days_remaining < 30"
                        @click="renewAccount"
                        :disabled="renewing"
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center justify-center space-x-2"
                    >
                        <Loader2 v-if="renewing" :size="20" class="animate-spin" />
                        <RefreshCw v-else :size="20" />
                        <span>Renew Account (KES 15,000)</span>
                    </button>
                    
                    <Link 
                        href="/online-work/history"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                    >
                        <ArrowLeft :size="20" />
                        <span>Back to History</span>
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
    PenTool, Database, Monitor, FileText, Key,
    CheckCircle, Info, ExternalLink, Copy,
    RefreshCw, ArrowLeft, Loader2
} from 'lucide-vue-next';

const props = defineProps({
    account: Object,
});

const renewing = ref(false);

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
    return colors[props.account.platform] || 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
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

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500/20 text-green-400 border border-green-500/30';
        case 'pending': return 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30';
        case 'expired': return 'bg-red-500/20 text-red-400 border border-red-500/30';
        case 'cancelled': return 'bg-gray-500/20 text-gray-400 border border-gray-500/30';
        default: return 'bg-purple-500/20 text-purple-400 border border-purple-500/30';
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

const renewAccount = async () => {
    if (!confirm('Are you sure you want to renew this account for KES 15,000?')) return;

    renewing.value = true;
    try {
        await router.post(`/online-work/${props.account.id}/renew`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                }
            },
            onError: (errors) => {
                console.error('Error renewing account:', errors);
                alert('Failed to renew account.');
            }
        });
    } catch (error) {
        console.error('Error renewing account:', error);
        alert('An unexpected error occurred.');
    } finally {
        renewing.value = false;
    }
};
</script>