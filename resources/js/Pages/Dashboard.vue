<template>
    <DashboardLayout title="Dashboard">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <User :size="16" class="text-purple-400" />
            <span>Account</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Dashboard</span>
        </div>
        
        <!-- Dashboard Content -->
        <div class="p-4 lg:p-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 text-white rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-200">Total Earnings</p>
                            <p class="text-2xl font-bold">KES {{ totalEarnings.toLocaleString() }}</p>
                        </div>
                        <TrendingUp :size="24" class="text-purple-300" />
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-600/40 to-teal-600/40 backdrop-blur-xl border border-emerald-500/30 text-white rounded-2xl p-4 shadow-2xl shadow-emerald-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-emerald-200">Active Referrals</p>
                            <p class="text-2xl font-bold">{{ stats.active_referrals }}</p>
                        </div>
                        <Users :size="24" class="text-emerald-300" />
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-cyan-600/40 to-blue-600/40 backdrop-blur-xl border border-cyan-500/30 text-white rounded-2xl p-4 shadow-2xl shadow-cyan-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-cyan-200">Job Applications</p>
                            <p class="text-2xl font-bold">{{ stats.job_applications }}</p>
                        </div>
                        <Briefcase :size="24" class="text-cyan-300" />
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-amber-600/40 to-orange-600/40 backdrop-blur-xl border border-amber-500/30 text-white rounded-2xl p-4 shadow-2xl shadow-amber-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-amber-200">Endorsements</p>
                            <p class="text-2xl font-bold">{{ stats.active_endorsements }}</p>
                        </div>
                        <Award :size="24" class="text-amber-300" />
                    </div>
                </div>
            </div>
            
            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Welcome Card -->
                <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-purple-500/20">
                    <h2 class="text-xl font-bold">Welcome Back {{ $page.props.auth.user.name.split(' ')[0] }}!</h2>
                    <p class="text-purple-200 text-sm mt-0.5">Good to see you again!</p>
                    <div class="flex items-center mt-1.5 text-xs">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-purple-300">Last login: {{ formatDate(user.last_active_at) }}</span>
                    </div>
                    <div class="mt-3 flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full" :class="user.is_agent ? 'bg-green-500' : 'bg-yellow-500'"></div>
                        <span class="text-sm">{{ user.is_agent ? 'Verified Agent' : 'Regular Member' }}</span>
                    </div>
                </div>

                <!-- Customer Reviews -->
                <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-3 shadow-2xl shadow-purple-500/20">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-purple-300">Recent Reviews</h3>
                        <span class="text-xs text-purple-400 bg-purple-500/20 px-2 py-1 rounded">{{ reviews.length }} Reviews</span>
                    </div>
                    
                    <div class="relative h-28 overflow-hidden">
                        <transition name="fade" mode="out-in">
                            <div 
                                :key="currentReview"
                                class="w-full bg-slate-700/50 backdrop-blur-sm p-2.5 rounded-xl border border-purple-500/20"
                            >
                                <div class="flex items-center space-x-2 mb-1.5">
                                    <span class="font-bold text-purple-300">{{ reviews[currentReview].user }}</span>
                                    <div class="flex text-yellow-400">
                                        <span v-for="n in 5" :key="n">{{ n <= reviews[currentReview].rating ? '★' : '☆' }}</span>
                                    </div>
                                </div>
                                <p class="text-gray-300 text-sm leading-relaxed line-clamp-2">{{ reviews[currentReview].comment }}</p>
                                <p class="text-xs text-purple-400 mt-1.5 text-right">{{ formatReviewDate(reviews[currentReview].date) }}</p>
                            </div>
                        </transition>
                    </div>
                    
                    <div class="flex justify-center space-x-2 mt-2">
                        <button
                            v-for="(review, index) in reviews"
                            :key="index"
                            @click="goToReview(index)"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="currentReview === index ? 'bg-purple-500 w-4' : 'bg-purple-500/30'"
                        ></button>
                    </div>
                </div>

                <!-- Balance Cards -->
                <div class="bg-gradient-to-br from-cyan-600/40 to-blue-600/40 backdrop-blur-xl border border-cyan-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-cyan-500/20">
                    <h3 class="text-lg font-bold mb-1">WhatsApp Balance</h3>
                    <p class="text-4xl font-bold">KES. {{ balances.whatsapp_balance.toLocaleString() }}</p>
                    <p class="text-xs text-cyan-300 mt-1">Available for withdrawal</p>
                </div>

                <div class="bg-gradient-to-br from-violet-600/40 to-fuchsia-600/40 backdrop-blur-xl border border-violet-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-violet-500/20">
                    <h3 class="text-lg font-bold mb-1">Deposit Balance</h3>
                    <p class="text-4xl font-bold">KES. {{ balances.deposit_balance.toLocaleString() }}</p>
                    <p class="text-xs text-violet-300 mt-1">Ready for investment</p>
                </div>

                <div class="bg-gradient-to-br from-amber-600/40 to-orange-600/40 backdrop-blur-xl border border-amber-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-amber-500/20">
                    <h3 class="text-lg font-bold mb-1">Cashback Bonus</h3>
                    <p class="text-4xl font-bold">KES. {{ balances.cashback_bonus.toLocaleString() }}</p>
                    <p class="text-xs text-amber-300 mt-1">Available for use</p>
                </div>

                <!-- Package Info -->
                <div class="bg-gradient-to-br from-pink-600/40 to-rose-600/40 backdrop-blur-xl border border-pink-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-pink-500/20">
                    <h3 class="text-lg font-bold mb-1">Current Package</h3>
                    <p class="text-2xl font-bold">{{ package.current }}</p>
                    <p class="text-sm text-pink-300 mt-1">Status: {{ package.status }}</p>
                    <p v-if="package.purchased_at" class="text-xs text-pink-400 mt-1">
                        Purchased: {{ formatDate(package.purchased_at) }}
                    </p>
                </div>

                <!-- Certification Status -->
                <div class="bg-gradient-to-br from-emerald-600/40 to-green-600/40 backdrop-blur-xl border border-emerald-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-emerald-500/20">
                    <h3 class="text-lg font-bold mb-1">Verification Status</h3>
                    <p class="text-2xl font-bold">{{ certification.status }}</p>
                    <p class="text-sm mt-1" :class="certification.verified ? 'text-emerald-300' : 'text-yellow-300'">
                        {{ certification.verified ? 'Account verified' : 'Verification pending' }}
                    </p>
                    <p v-if="certification.expires_at" class="text-xs text-emerald-400 mt-1">
                        Expires: {{ formatDate(certification.expires_at) }}
                    </p>
                </div>

                <!-- Withdrawal Info -->
                <div class="bg-gradient-to-br from-indigo-600/40 to-purple-600/40 backdrop-blur-xl border border-indigo-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-indigo-500/20">
                    <h3 class="text-lg font-bold mb-1">Total Withdrawn</h3>
                    <p class="text-4xl font-bold">KES. {{ balances.total_withdrawn.toLocaleString() }}</p>
                    <p class="text-xs text-indigo-300 mt-1">Lifetime withdrawals</p>
                </div>

                <!-- Recent Activity -->
                <div class="bg-gradient-to-br from-slate-700/40 to-slate-900/40 backdrop-blur-xl border border-slate-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-slate-500/20">
                    <h3 class="text-lg font-bold mb-2 text-slate-300">Recent Activity</h3>
                    <div class="space-y-2" v-if="recent_withdrawals.length > 0">
                        <div v-for="(activity, index) in recent_withdrawals" :key="index" class="flex items-center justify-between">
                            <div>
                                <p class="text-sm">{{ activity.type }} Withdrawal</p>
                                <p class="text-xs text-slate-400">{{ formatDate(activity.date) }}</p>
                            </div>
                            <span class="text-green-400 font-bold">+KES {{ activity.amount.toLocaleString() }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <p class="text-slate-400">No recent activity</p>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-gradient-to-br from-sky-600/40 to-blue-600/40 backdrop-blur-xl border border-sky-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-sky-500/20">
                    <h3 class="text-lg font-bold mb-2 text-sky-300">Quick Stats</h3>
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Monthly Salary:</span>
                            <span class="text-sky-300">KES {{ quick_stats.monthly_income?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Bonus Earned:</span>
                            <span class="text-sky-300">KES {{ quick_stats.bonus_earned?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Total Invested:</span>
                            <span class="text-sky-300">KES {{ quick_stats.total_invested?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Agent Since:</span>
                            <span class="text-sky-300">{{ formatDate(user.agent_since) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Referral Stats -->
                <div class="bg-gradient-to-br from-fuchsia-600/40 to-purple-600/40 backdrop-blur-xl border border-fuchsia-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-fuchsia-500/20">
                    <h3 class="text-lg font-bold mb-2">Referral Stats</h3>
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center">
                            <span class="text-fuchsia-200">Total Referrals:</span>
                            <span class="text-fuchsia-300">{{ stats.total_referrals }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-fuchsia-200">Active Referrals:</span>
                            <span class="text-green-400">{{ stats.active_referrals }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-fuchsia-200">Inactive Referrals:</span>
                            <span class="text-red-400">{{ stats.total_referrals - stats.active_referrals }}</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm text-fuchsia-300">Your Referral Link:</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <input 
                                    type="text" 
                                    :value="referralLink"
                                    readonly
                                    class="flex-1 bg-fuchsia-500/20 border border-fuchsia-500/30 rounded-lg px-3 py-1 text-sm text-fuchsia-300"
                                />
                                <button 
                                    @click="copyReferralLink"
                                    class="bg-fuchsia-500 hover:bg-fuchsia-600 px-3 py-1 rounded-lg text-sm transition-colors"
                                >
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
    Package, X, Bell, User, DollarSign, Wallet, Gift, CreditCard, TrendingUp,
    Users, Briefcase, Award
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    balances: Object,
    package: Object,
    certification: Object,
    recent_withdrawals: Array,
    reviews: Array,
    stats: Object,
    quick_stats: Object,
});

const user = computed(() => props.user || {});
const totalEarnings = computed(() => props.balances?.total_earnings || 0);

const currentReview = ref(0);
let autoSlideInterval;

const nextReview = () => {
    currentReview.value = (currentReview.value + 1) % props.reviews.length;
};

const prevReview = () => {
    currentReview.value = (currentReview.value - 1 + props.reviews.length) % props.reviews.length;
};

const goToReview = (index) => {
    stopAutoSlide();
    currentReview.value = index;
    startAutoSlide();
};

const startAutoSlide = () => {
    if (props.reviews?.length > 1) {
        autoSlideInterval = setInterval(nextReview, 5000);
    }
};

const stopAutoSlide = () => {
    if (autoSlideInterval) {
        clearInterval(autoSlideInterval);
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
};

const formatReviewDate = (date) => {
    return formatDate(date);
};

const referralLink = computed(() => {
    return `${window.location.origin}/register?ref=${user.value.id}`;
});

const copyReferralLink = async () => {
    try {
        await navigator.clipboard.writeText(referralLink.value);
        alert('Referral link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

onMounted(() => {
    startAutoSlide();
});

onUnmounted(() => {
    stopAutoSlide();
});
</script>