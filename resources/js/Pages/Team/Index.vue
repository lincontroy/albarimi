<template>
    <DashboardLayout title="My Team">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-indigo-500/20 to-purple-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-indigo-500/20">
            <Users :size="16" class="text-indigo-400" />
            <span>Team</span>
            <span class="text-indigo-400">›</span>
            <span class="text-indigo-300">My Referrals</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Referrals -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-300 text-sm mb-1">Total Referrals</p>
                                <p class="text-3xl font-bold text-white">{{ stats.total_referrals }}</p>
                            </div>
                            <Users :size="32" class="text-blue-400" />
                        </div>
                        <p class="text-blue-200 text-xs mt-2">All time referrals</p>
                    </div>

                    <!-- Active Referrals -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-300 text-sm mb-1">Active Referrals</p>
                                <p class="text-3xl font-bold text-white">{{ stats.active_referrals }}</p>
                            </div>
                            <UserCheck :size="32" class="text-green-400" />
                        </div>
                        <p class="text-green-200 text-xs mt-2">{{ active_percentage }}% active rate</p>
                    </div>

                    <!-- This Month -->
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-300 text-sm mb-1">This Month</p>
                                <p class="text-3xl font-bold text-white">{{ monthly_referrals }}</p>
                            </div>
                            <Calendar :size="32" class="text-purple-400" />
                        </div>
                        <p class="text-purple-200 text-xs mt-2">New referrals this month</p>
                    </div>

                    <!-- Total Earned -->
                    <div class="bg-gradient-to-br from-orange-600/40 to-yellow-600/40 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-300 text-sm mb-1">Total Earned</p>
                                <p class="text-3xl font-bold text-white">KES {{ stats.total_earned.toLocaleString() }}</p>
                            </div>
                            <Coins :size="32" class="text-orange-400" />
                        </div>
                        <p class="text-orange-200 text-xs mt-2">From all referrals</p>
                    </div>
                </div>

                <!-- Referral Link Section -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-indigo-500/30 rounded-2xl p-6 shadow-2xl shadow-indigo-500/20 mb-8">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white mb-2 flex items-center">
                                <Link :size="20" class="mr-2 text-indigo-400" />
                                Your Referral Link
                            </h3>
                            <p class="text-indigo-300 text-sm mb-3">
                                Share this link with friends to earn commissions on their activities
                            </p>
                            
                            <!-- Referral Link -->
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-black/30 border border-indigo-500/30 rounded-xl p-3">
                                    <p class="text-white font-mono text-sm break-all">{{ stats.referral_link }}</p>
                                </div>
                                <button
                                    @click="copyReferralLink"
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/50 flex items-center space-x-2"
                                >
                                    <Copy :size="18" />
                                    <span>Copy</span>
                                </button>
                            </div>

                            <!-- Referral Code -->
                            <div class="mt-4">
                                <p class="text-indigo-300 text-sm mb-1">Your Referral Code:</p>
                                <div class="flex items-center space-x-2">
                                    <div class="bg-gradient-to-r from-indigo-700/40 to-purple-700/40 border border-indigo-500/30 rounded-xl p-3">
                                        <p class="text-white font-bold text-xl">{{ stats.id || 'Not Generated' }}</p>
                                    </div>
                                    <button
                                        v-if="!stats.referral_code"
                                        @click="generateReferralCode"
                                        :disabled="generatingCode"
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 text-white font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center space-x-2"
                                    >
                                        <Loader2 v-if="generatingCode" :size="18" class="animate-spin" />
                                        <Key v-else :size="18" />
                                        <span>Generate Code</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code (Optional) -->
                        <div class="bg-black/20 p-4 rounded-xl border border-indigo-500/20">
                            <div class="text-center">
                                <QrCode :size="100" class="mx-auto text-indigo-400 mb-2" />
                                <p class="text-indigo-300 text-xs">Scan to join</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referrals Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center">
                            <Users :size="20" class="mr-2" />
                            My Referrals ({{ referrals.total }})
                        </h3>
                        
                        <!-- Filters -->
                        <div class="flex items-center space-x-2">
                            <select 
                                v-model="statusFilter"
                                class="bg-black/30 border border-purple-500/30 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-purple-500"
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                                <option value="agent">Agents Only</option>
                            </select>
                            
                            <button
                                @click="refreshReferrals"
                                class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-3 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center space-x-2"
                            >
                                <RefreshCw :size="16" />
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-purple-500/20">
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">User</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Joined Date</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Last Active</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Balance</th>
                                    <th class="text-left py-3 px-4 text-purple-300 text-sm font-semibold">Agent Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="referral in filteredReferrals" 
                                    :key="referral.id"
                                    class="border-b border-purple-500/10 hover:bg-purple-500/5 transition-colors"
                                >
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ referral.name.charAt(0) }}</span>
                                            </div>
                                            <div>
                                                <p class="text-white font-semibold">{{ referral.name }}</p>
                                                <p class="text-purple-300 text-xs">{{ referral.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ referral.joined_date }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ referral.last_active }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span 
                                            :class="referral.is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ referral.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white font-semibold">KES {{ referral.balance.toLocaleString() }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div v-if="referral.is_agent">
                                            <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-xs font-semibold">
                                                Agent
                                            </span>
                                            <p class="text-blue-300 text-xs mt-1">Since {{ referral.agent_since }}</p>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">Not an agent</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-4">
                        <div 
                            v-for="referral in filteredReferrals" 
                            :key="referral.id"
                            class="bg-black/20 border border-purple-500/20 rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ referral.name.charAt(0) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-white font-semibold">{{ referral.name }}</p>
                                        <p class="text-purple-300 text-xs">{{ referral.email }}</p>
                                    </div>
                                </div>
                                <span 
                                    :class="referral.is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                >
                                    {{ referral.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-purple-300">Joined</p>
                                    <p class="text-white">{{ referral.joined_date }}</p>
                                </div>
                                <div>
                                    <p class="text-purple-300">Last Active</p>
                                    <p class="text-white">{{ referral.last_active }}</p>
                                </div>
                                <div>
                                    <p class="text-purple-300">Balance</p>
                                    <p class="text-white font-semibold">KES {{ referral.balance.toLocaleString() }}</p>
                                </div>
                                <div>
                                    <p class="text-purple-300">Agent Status</p>
                                    <div v-if="referral.is_agent">
                                        <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded-full text-xs font-semibold">
                                            Agent
                                        </span>
                                    </div>
                                    <p v-else class="text-gray-400">Not an agent</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredReferrals.length === 0" class="text-center py-12">
                        <Users :size="64" class="mx-auto text-purple-400 mb-4 opacity-50" />
                        <p class="text-purple-300 text-lg mb-2">No referrals yet</p>
                        <p class="text-purple-400 text-sm mb-6">Start sharing your referral link to build your team</p>
                        <button
                            @click="copyReferralLink"
                            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center space-x-2 mx-auto"
                        >
                            <Share2 :size="18" />
                            <span>Share Referral Link</span>
                        </button>
                    </div>

                    <!-- Pagination -->
                    <div v-if="referrals.total > 0" class="flex items-center justify-between mt-6 pt-6 border-t border-purple-500/20">
                        <p class="text-purple-300 text-sm">
                            Showing {{ filteredReferrals.length }} of {{ referrals.total }} referrals
                        </p>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="previousPage"
                                :disabled="!referrals.prev_page_url"
                                class="bg-black/30 border border-purple-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-purple-500 transition-colors"
                            >
                                <ChevronLeft :size="16" />
                            </button>
                            <span class="text-white text-sm">
                                Page {{ referrals.current_page }} of {{ referrals.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="!referrals.next_page_url"
                                class="bg-black/30 border border-purple-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-purple-500 transition-colors"
                            >
                                <ChevronRight :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- How It Works -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <Share2 :size="32" class="mx-auto text-green-400 mb-4" />
                            <h4 class="text-white font-bold mb-2">1. Share Your Link</h4>
                            <p class="text-green-200 text-sm">
                                Share your unique referral link with friends, family, and on social media
                            </p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="text-center">
                            <UserPlus :size="32" class="mx-auto text-blue-400 mb-4" />
                            <h4 class="text-white font-bold mb-2">2. They Join & Earn</h4>
                            <p class="text-blue-200 text-sm">
                                When they sign up and start earning, you get commission on their activities
                            </p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="text-center">
                            <Coins :size="32" class="mx-auto text-purple-400 mb-4" />
                            <h4 class="text-white font-bold mb-2">3. Earn Rewards</h4>
                            <p class="text-purple-200 text-sm">
                                Earn commission bonuses when your referrals complete tasks and make purchases
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Users, UserCheck, Calendar, Coins, Link, Copy,
    Key, Loader2, QrCode, RefreshCw, ChevronLeft,
    ChevronRight, Share2, UserPlus
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    referrals: Object,
    stats: Object,
    monthly_referrals: Number,
    active_percentage: Number
});

const statusFilter = ref('all');
const generatingCode = ref(false);

// Filter referrals based on status
const filteredReferrals = computed(() => {
    if (statusFilter.value === 'all') {
        return props.referrals.data;
    } else if (statusFilter.value === 'active') {
        return props.referrals.data.filter(r => r.is_active);
    } else if (statusFilter.value === 'inactive') {
        return props.referrals.data.filter(r => !r.is_active);
    } else if (statusFilter.value === 'agent') {
        return props.referrals.data.filter(r => r.is_agent);
    }
    return props.referrals.data;
});

// Copy referral link to clipboard
const copyReferralLink = async () => {
    try {
        await navigator.clipboard.writeText(props.stats.referral_link);
        alert('Referral link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = props.stats.referral_link;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Referral link copied to clipboard!');
    }
};

// Generate referral code
const generateReferralCode = async () => {
    generatingCode.value = true;
    
    try {
        await router.post(route('team.generate-code'), {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                } else if (flash.info) {
                    alert(flash.info);
                }
            },
            onError: (errors) => {
                console.error('Error generating code:', errors);
                alert('Failed to generate referral code. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error generating code:', error);
        alert('An unexpected error occurred.');
    } finally {
        generatingCode.value = false;
    }
};

// Refresh referrals
const refreshReferrals = () => {
    router.reload();
};

// Pagination
const previousPage = () => {
    if (props.referrals.prev_page_url) {
        router.visit(props.referrals.prev_page_url);
    }
};

const nextPage = () => {
    if (props.referrals.next_page_url) {
        router.visit(props.referrals.next_page_url);
    }
};
</script>