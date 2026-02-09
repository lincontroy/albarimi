<!-- History.vue -->
<template>
    <DashboardLayout title="Online Work - History">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <History :size="16" class="text-purple-400" />
            <span>Online Work</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Purchase History</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-white">Account Purchase History</h1>
                    <Link 
                        href="/online-work"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50"
                    >
                        Back to Accounts
                    </Link>
                </div>

                <!-- History Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-purple-500/20">
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Platform</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Amount</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Purchase Date</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Expires</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="account in accounts.data" 
                                    :key="account.id"
                                    class="border-b border-purple-500/10 hover:bg-purple-500/5 transition-colors"
                                >
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <component :is="getPlatformIcon(account.platform)" :size="20" :class="getIconColor(account.platform)" />
                                            <div>
                                                <p class="text-white font-semibold">{{ account.platform_name }}</p>
                                                <p class="text-purple-300 text-xs">{{ formatPlatform(account.platform) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white font-semibold">KES {{ account.amount.toLocaleString() }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ account.purchased_at }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span 
                                            :class="getStatusColor(account.status)"
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ account.status }}
                                        </span>
                                        <p v-if="account.is_active" class="text-green-400 text-xs mt-1">
                                            {{ account.days_remaining }} days remaining
                                        </p>
                                        <p v-if="account.is_expired" class="text-red-400 text-xs mt-1">
                                            Expired
                                        </p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ account.expires_at || 'N/A' }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <Link 
                                                :href="'/online-work/' + account.id"
                                                class="text-blue-400 hover:text-blue-300 transition-colors p-2 hover:bg-blue-500/10 rounded-lg"
                                                title="View Details"
                                            >
                                                <Eye :size="16" />
                                            </Link>
                                            <button 
                                                v-if="account.is_active && account.days_remaining < 30"
                                                @click="renewAccount(account.id)"
                                                class="text-green-400 hover:text-green-300 transition-colors p-2 hover:bg-green-500/10 rounded-lg"
                                                title="Renew Account"
                                            >
                                                <RefreshCw :size="16" />
                                            </button>
                                            <button 
                                                v-if="account.login_credentials"
                                                @click="copyCredentials(account)"
                                                class="text-purple-400 hover:text-purple-300 transition-colors p-2 hover:bg-purple-500/10 rounded-lg"
                                                title="Copy Credentials"
                                            >
                                                <Copy :size="16" />
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
                            v-for="account in accounts.data" 
                            :key="account.id"
                            :class="getPlatformColor(account.platform)"
                            class="border rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <component :is="getPlatformIcon(account.platform)" :size="20" />
                                    <div>
                                        <p class="text-white font-semibold">{{ account.platform_name }}</p>
                                        <p class="opacity-75 text-xs">{{ formatPlatform(account.platform) }}</p>
                                    </div>
                                </div>
                                <span 
                                    :class="getStatusColor(account.status)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                >
                                    {{ account.status }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="opacity-75">Amount:</span>
                                    <span class="font-semibold">KES {{ account.amount.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Purchased:</span>
                                    <span>{{ account.purchased_at }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Expires:</span>
                                    <span>{{ account.expires_at || 'N/A' }}</span>
                                </div>
                                <div v-if="account.is_active" class="flex justify-between text-green-400">
                                    <span>Days Remaining:</span>
                                    <span class="font-semibold">{{ account.days_remaining }} days</span>
                                </div>
                                <div v-if="account.is_expired" class="flex justify-between text-red-400">
                                    <span>Status:</span>
                                    <span class="font-semibold">Expired</span>
                                </div>
                            </div>
                            
                            <div v-if="account.login_credentials" class="mt-3 pt-3 border-t border-white/10">
                                <p class="text-xs opacity-75 mb-1">Account Credentials:</p>
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs opacity-75">Email:</span>
                                        <div class="flex items-center">
                                            <span class="text-xs font-mono truncate max-w-[120px]">
                                                {{ account.login_credentials.email }}
                                            </span>
                                            <button @click="copyToClipboard(account.login_credentials.email)" 
                                                    class="text-purple-400 hover:text-purple-300 ml-1">
                                                <Copy :size="12" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                                <Link 
                                    :href="'/online-work/' + account.id"
                                    class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                >
                                    <Eye :size="14" />
                                    <span>Details</span>
                                </Link>
                                <button 
                                    v-if="account.is_active && account.days_remaining < 30"
                                    @click="renewAccount(account.id)"
                                    class="text-green-400 hover:text-green-300 text-sm flex items-center space-x-1"
                                >
                                    <RefreshCw :size="14" />
                                    <span>Renew</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="accounts.data.length === 0" class="text-center py-12">
                        <Briefcase :size="64" class="mx-auto text-purple-400 mb-4 opacity-50" />
                        <p class="text-purple-300 text-lg mb-2">No account purchases yet</p>
                        <p class="text-purple-400 text-sm mb-6">Purchase your first online work account to get started</p>
                        <Link 
                            href="/online-work"
                            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 inline-flex items-center space-x-2"
                        >
                            <Briefcase :size="18" />
                            <span>View Accounts</span>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="accounts.total > 0" class="flex items-center justify-between mt-6 pt-6 border-t border-purple-500/20">
                        <p class="text-purple-300 text-sm">
                            Showing {{ accounts.from }} to {{ accounts.to }} of {{ accounts.total }} accounts
                        </p>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="previousPage"
                                :disabled="!accounts.prev_page_url"
                                class="bg-black/30 border border-purple-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-purple-500 transition-colors"
                            >
                                <ChevronLeft :size="16" />
                            </button>
                            <span class="text-white text-sm">
                                Page {{ accounts.current_page }} of {{ accounts.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="!accounts.next_page_url"
                                class="bg-black/30 border border-purple-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-purple-500 transition-colors"
                            >
                                <ChevronRight :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                        <div class="text-center">
                            <p class="text-purple-300 text-sm mb-1">Total Purchases</p>
                            <p class="text-2xl font-bold text-white">{{ accounts.total }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-4 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <p class="text-green-300 text-sm mb-1">Active Accounts</p>
                            <p class="text-2xl font-bold text-white">{{ activeAccountsCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-600/40 to-amber-600/40 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-4 shadow-2xl shadow-yellow-500/20">
                        <div class="text-center">
                            <p class="text-yellow-300 text-sm mb-1">Expiring Soon</p>
                            <p class="text-2xl font-bold text-white">{{ expiringSoonCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-red-600/40 to-orange-600/40 backdrop-blur-xl border border-red-500/30 rounded-2xl p-4 shadow-2xl shadow-red-500/20">
                        <div class="text-center">
                            <p class="text-red-300 text-sm mb-1">Expired</p>
                            <p class="text-2xl font-bold text-white">{{ expiredAccountsCount }}</p>
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
    Briefcase, Building2, Cloud, Globe, Users,
    PenTool, Database, Monitor, FileText, Eye,
    RefreshCw, Copy, ChevronLeft, ChevronRight,
    History
} from 'lucide-vue-next';

const props = defineProps({
    accounts: Object,
});

// Compute stats
const activeAccountsCount = computed(() => {
    return props.accounts.data.filter(a => a.is_active).length;
});

const expiringSoonCount = computed(() => {
    return props.accounts.data.filter(a => a.is_active && a.days_remaining < 30).length;
});

const expiredAccountsCount = computed(() => {
    return props.accounts.data.filter(a => a.is_expired).length;
});

const getPlatformColor = (platform) => {
    const colors = {
        'handshake': 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30',
        'telus': 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30',
        'imerit': 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30',
        'atlas_capture': 'bg-gradient-to-br from-red-600/40 to-orange-600/40 border-red-500/30',
        'crowdgen': 'bg-gradient-to-br from-yellow-600/40 to-amber-600/40 border-yellow-500/30',
        'mindrift': 'bg-gradient-to-br from-indigo-600/40 to-violet-600/40 border-indigo-500/30',
        'dataannotation': 'bg-gradient-to-br from-teal-600/40 to-cyan-600/40 border-teal-500/30',
        'cloudworkers': 'bg-gradient-to-br from-gray-600/40 to-slate-600/40 border-gray-500/30',
        'textfactory': 'bg-gradient-to-br from-rose-600/40 to-pink-600/40 border-rose-500/30',
    };
    return colors[platform] || 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
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

const formatPlatform = (platform) => {
    return platform.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500/20 text-green-400';
        case 'pending': return 'bg-yellow-500/20 text-yellow-400';
        case 'expired': return 'bg-red-500/20 text-red-400';
        case 'cancelled': return 'bg-gray-500/20 text-gray-400';
        default: return 'bg-purple-500/20 text-purple-400';
    }
};

const copyCredentials = (account) => {
    if (account.login_credentials) {
        const text = `Email: ${account.login_credentials.email}\nPassword: ${account.login_credentials.password}`;
        copyToClipboard(text);
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

const renewAccount = async (id) => {
    if (!confirm('Are you sure you want to renew this account for KES 15,000?')) return;

    try {
        await router.post(`/online-work/${id}/renew`, {}, {
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
    }
};

const previousPage = () => {
    if (props.accounts.prev_page_url) {
        router.visit(props.accounts.prev_page_url);
    }
};

const nextPage = () => {
    if (props.accounts.next_page_url) {
        router.visit(props.accounts.next_page_url);
    }
};
</script>