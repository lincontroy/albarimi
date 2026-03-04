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
            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Welcome Card -->
                <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-purple-500/20">
                    <h2 class="text-xl font-bold">Welcome Back {{ user?.name?.split(' ')[0] || 'User' }}!</h2>
                    <p class="text-purple-200 text-sm mt-0.5">Good to see you again!</p>
                    <div class="flex items-center mt-1.5 text-xs">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span class="text-purple-300">Last login: {{ formatDate(user?.last_active_at) }}</span>
                    </div>
                    <div class="mt-3 flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full" :class="user?.is_agent ? 'bg-green-500' : 'bg-yellow-500'"></div>
                        <span class="text-sm">{{ user?.is_agent ? 'Verified Agent' : 'Regular Member' }}</span>
                    </div>
                </div>

                <!-- Customer Reviews - Withdrawal Reviews -->
                <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-3 shadow-2xl shadow-purple-500/20">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-purple-300">Withdrawal Reviews</h3>
                        <span class="text-xs text-purple-400 bg-purple-500/20 px-2 py-1 rounded">
                            {{ reviews.length }} Reviews
                        </span>
                    </div>
                    
                    <div class="relative h-32 overflow-hidden">
                        <transition name="fade" mode="out-in">
                            <div 
                                :key="currentReview"
                                class="w-full bg-slate-700/50 backdrop-blur-sm p-3 rounded-xl border border-purple-500/20"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-purple-300">{{ reviews[currentReview].user }}</span>
                                        <span class="text-xs text-green-400">+{{ reviews[currentReview].amount }} KES</span>
                                    </div>
                                    <div class="flex text-yellow-400">
                                        <span v-for="n in 5" :key="n">{{ n <= reviews[currentReview].rating ? '★' : '☆' }}</span>
                                    </div>
                                </div>
                                <p class="text-gray-300 text-sm leading-relaxed line-clamp-2">{{ reviews[currentReview].comment }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-xs text-purple-500">Withdrawal #{{ reviews[currentReview].id }}</span>
                                    <p class="text-xs text-purple-400">{{ formatReviewDate(reviews[currentReview].date) }}</p>
                                </div>
                            </div>
                        </transition>
                    </div>
                    
                    <div class="flex justify-center space-x-2 mt-3">
                        <button
                            v-for="(review, index) in reviews"
                            :key="index"
                            @click="goToReview(index)"
                            class="w-2 h-2 rounded-full transition-all duration-300"
                            :class="currentReview === index ? 'bg-purple-500 w-4' : 'bg-purple-500/30 hover:bg-purple-500/50'"
                        ></button>
                    </div>
                </div>

                <!-- Balance Cards -->
                <div class="bg-gradient-to-br from-cyan-600/40 to-blue-600/40 backdrop-blur-xl border border-cyan-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-cyan-500/20">
                    <h3 class="text-lg font-bold mb-1">WhatsApp Balance</h3>
                    <p class="text-4xl font-bold">KES. {{ balances?.whatsapp_balance?.toLocaleString() || '0' }}</p>
                    <p class="text-xs text-cyan-300 mt-1">Available for withdrawal</p>
                </div>

                <div class="bg-gradient-to-br from-amber-600/40 to-orange-600/40 backdrop-blur-xl border border-amber-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-amber-500/20">
                    <h3 class="text-lg font-bold mb-1">Total Whatsapp Withdrawals</h3>
                    <p class="text-4xl font-bold">KES. {{ stats?.total_whatsapp_withdrawals?.toLocaleString() || '0' }}</p>
                    <p class="text-xs text-amber-300 mt-1">Available for use</p>
                </div>

                <div class="bg-gradient-to-br from-violet-600/40 to-fuchsia-600/40 backdrop-blur-xl border border-violet-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-violet-500/20">
                    <h3 class="text-lg font-bold mb-1">Deposit Balance</h3>
                    <p class="text-4xl font-bold">KES. {{ balances?.deposit_balance?.toLocaleString() || '0' }}</p>
                    <p class="text-xs text-violet-300 mt-1">Ready for investment</p>
                </div>

                <div class="bg-gradient-to-br from-amber-600/40 to-orange-600/40 backdrop-blur-xl border border-amber-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-amber-500/20">
                    <h3 class="text-lg font-bold mb-1">Cashback Bonus</h3>
                    <p class="text-4xl font-bold">KES. {{ balances?.cashback_bonus?.toLocaleString() || '0' }}</p>
                    <p class="text-xs text-amber-300 mt-1">Available for use</p>
                </div>

                <!-- Package Info -->
                <div class="bg-gradient-to-br from-pink-600/40 to-rose-600/40 backdrop-blur-xl border border-pink-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-pink-500/20">
                    <h3 class="text-lg font-bold mb-1">Current Package</h3>
                    <p class="text-2xl font-bold">{{ user?.package || 'No Package' }}</p>
                    <p class="text-sm text-pink-300 mt-1">Status: {{ package?.status || 'Inactive' }}</p>
                    <p v-if="package?.purchased_at" class="text-xs text-pink-400 mt-1">
                        Purchased: {{ formatDate(package.purchased_at) }}
                    </p>
                </div>

                <!-- Certification Status -->
                <div class="bg-gradient-to-br from-emerald-600/40 to-green-600/40 backdrop-blur-xl border border-emerald-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-emerald-500/20">
                    <h3 class="text-lg font-bold mb-1">Verification Status</h3>
                    <div class="mt-3 flex items-center space-x-2">
                        <div class="w-2 h-2 rounded-full" :class="user?.is_agent === 1 ? 'bg-green-500' : 'bg-yellow-500'"></div>
                        <span class="text-sm">{{ user?.is_agent === 1 ? 'Verified Agent' : 'Regular Member' }}</span>
                    </div>
                    <p v-if="certification?.expires_at" class="text-xs text-emerald-400 mt-1">
                        Expires: {{ formatDate(certification.expires_at) }}
                    </p>
                </div>

                <!-- Withdrawal Info -->
                <div class="bg-gradient-to-br from-indigo-600/40 to-purple-600/40 backdrop-blur-xl border border-indigo-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-indigo-500/20">
                    <h3 class="text-lg font-bold mb-1">Total Withdrawn</h3>
                    <p class="text-4xl font-bold">KES. {{ stats?.total_withdrawals?.toLocaleString() || '0' }}</p>
                    <p class="text-xs text-indigo-300 mt-1">Lifetime withdrawals</p>
                </div>

                <!-- Recent Activity -->
                <div class="bg-gradient-to-br from-slate-700/40 to-slate-900/40 backdrop-blur-xl border border-slate-500/30 text-white rounded-2xl p-3 shadow-2xl shadow-slate-500/20">
                    <h3 class="text-lg font-bold mb-2 text-slate-300">Recent Activity</h3>
                    <div class="space-y-2" v-if="recent_withdrawals?.length > 0">
                        <div v-for="(activity, index) in recent_withdrawals" :key="index" class="flex items-center justify-between">
                            <div>
                                <p class="text-sm">{{ activity.type }} Withdrawal</p>
                                <p class="text-xs text-slate-400">{{ formatDate(activity.date) }}</p>
                            </div>
                            <span class="text-green-400 font-bold">+KES {{ activity.amount?.toLocaleString() }}</span>
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
                            <span class="text-sky-300">KES {{ quick_stats?.monthly_income?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Bonus Earned:</span>
                            <span class="text-sky-300">KES {{ quick_stats?.bonus_earned?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Total Invested:</span>
                            <span class="text-sky-300">KES {{ quick_stats?.total_invested?.toLocaleString() || '0' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sky-200">Agent Since:</span>
                            <span class="text-sky-300">{{ formatDate(user?.agent_since) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { User } from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    balances: Object,
    package: Object,
    certification: Object,
    recent_withdrawals: Array,
    stats: Object,
    quick_stats: Object,
});

// 10 hardcoded withdrawal reviews
const reviews = ref([
    {
        id: 'WD2458',
        user: 'James M.',
        amount: '15,000',
        rating: 5,
        comment: 'Fastest withdrawal I\'ve ever experienced! Money hit my M-Pesa in under 2 hours. Highly recommended!',
        date: '2025-02-15'
    },
    {
        id: 'WD2391',
        user: 'Sarah K.',
        amount: '8,500',
        rating: 5,
        comment: 'Smooth process, no complications. The WhatsApp withdrawal feature is a game changer!',
        date: '2026-02-14'
    },
    {
        id: 'WD2512',
        user: 'Peter O.',
        amount: '25,000',
        rating: 4,
        comment: 'Great service! Withdrawal was processed within 24 hours. Will definitely use again.',
        date: '2026-02-13'
    },
    {
        id: 'WD2305',
        user: 'Grace W.',
        amount: '12,000',
        rating: 5,
        comment: 'Very reliable platform. I\'ve withdrawn multiple times and never had any issues.',
        date: '2026-02-12'
    },
    {
        id: 'WD2476',
        user: 'John D.',
        amount: '30,000',
        rating: 5,
        comment: 'Best withdrawal system in Kenya! Instant processing and great customer support.',
        date: '2026-02-11'
    },
    {
        id: 'WD2389',
        user: 'Mary N.',
        amount: '5,500',
        rating: 4,
        comment: 'Easy to use and quick payout. The interface is very user-friendly.',
        date: '2026-02-10'
    },
    {
        id: 'WD2523',
        user: 'David K.',
        amount: '18,000',
        rating: 5,
        comment: 'Impressed with the speed! Withdrew on Sunday and got the money on Monday morning.',
        date: '2026-02-09'
    },
    {
        id: 'WD2412',
        user: 'Lucy A.',
        amount: '10,000',
        rating: 5,
        comment: 'Legit platform! Been using for 6 months and withdrawals are always processed on time.',
        date: '2026-02-08'
    },
    {
        id: 'WD2498',
        user: 'Robert M.',
        amount: '22,000',
        rating: 4,
        comment: 'Good experience overall. Minor delay once but support sorted it out quickly.',
        date: '2026-02-07'
    },
    {
        id: 'WD2356',
        user: 'Agnes W.',
        amount: '7,200',
        rating: 5,
        comment: 'Love the WhatsApp integration! Makes withdrawals so convenient. 5 stars!',
        date: '2026-02-06'
    }
]);

const currentReview = ref(0);
let autoSlideInterval;

const nextReview = () => {
    currentReview.value = (currentReview.value + 1) % reviews.value.length;
};

const prevReview = () => {
    currentReview.value = (currentReview.value - 1 + reviews.value.length) % reviews.value.length;
};

const goToReview = (index) => {
    stopAutoSlide();
    currentReview.value = index;
    startAutoSlide();
};

const startAutoSlide = () => {
    if (reviews.value.length > 1) {
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
    const reviewDate = new Date(date);
    const today = new Date();
    const diffTime = Math.abs(today - reviewDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    return formatDate(date);
};

onMounted(() => {
    startAutoSlide();
});

onUnmounted(() => {
    stopAutoSlide();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>