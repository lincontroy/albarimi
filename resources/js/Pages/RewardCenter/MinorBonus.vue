<template>
    <DashboardLayout title="Minor Bonus">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <Star :size="16" class="text-green-400" />
            <span>Reward Center</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Minor Bonus</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Bonus Card -->
                <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-8 shadow-2xl shadow-green-500/20 mb-6">
                    <div class="text-center">
                        <Star :size="64" class="mx-auto text-green-400 mb-4" />
                        <h1 class="text-4xl font-bold text-white mb-2">{{ bonusInfo.name }}</h1>
                        <p class="text-green-200 text-lg mb-6">{{ bonusInfo.description }}</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                            <!-- Bonus Amount -->
                            <div class="bg-black/30 rounded-xl p-6 border border-green-500/20">
                                <p class="text-green-300 text-sm mb-2">You Get</p>
                                <p class="text-3xl font-bold text-white">KES {{ bonusInfo.bonus_amount.toLocaleString() }}</p>
                            </div>
                            
                            <!-- Claim Cost -->
                            <div class="bg-black/30 rounded-xl p-6 border border-green-500/20">
                                <p class="text-green-300 text-sm mb-2">Cost</p>
                                <p class="text-3xl font-bold text-white">KES {{ bonusInfo.claim_cost.toLocaleString() }}</p>
                            </div>
                        </div>
                        
                        <!-- Net Profit -->
                        <div class="mt-6 bg-gradient-to-r from-emerald-700/40 to-green-700/40 rounded-xl p-4 max-w-md mx-auto">
                            <p class="text-white text-sm mb-1">Net Profit After Claim</p>
                            <p class="text-2xl font-bold text-emerald-300">
                                KES {{ (bonusInfo.bonus_amount - bonusInfo.claim_cost).toLocaleString() }}
                            </p>
                            <p class="text-emerald-200 text-xs mt-1">That's {{ ((bonusInfo.bonus_amount - bonusInfo.claim_cost) / bonusInfo.claim_cost * 100).toFixed(0) }}% return on investment!</p>
                        </div>
                    </div>
                </div>

                <!-- User Balance & Action -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                        <!-- Balance Info -->
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-white mb-2">Your Balance</h3>
                            <p class="text-2xl font-bold text-green-400">KES {{ userBalance.toLocaleString() }}</p>
                            <p class="text-green-300 text-sm mt-1">
                                Required: KES {{ bonusInfo.claim_cost.toLocaleString() }}
                                • After claim: KES {{ (userBalance + (bonusInfo.bonus_amount - bonusInfo.claim_cost)).toLocaleString() }}
                            </p>
                        </div>

                        <!-- Claim Button -->
                        <div class="flex flex-col items-center space-y-3">
                            <button
                                @click="claimBonus"
                                :disabled="hasClaimed || !hasSufficientBalance || processing"
                                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-4 px-8 rounded-xl transition-all duration-200 shadow-lg shadow-green-500/50 flex items-center space-x-2 text-lg min-w-[200px]"
                            >
                                <Loader2 v-if="processing" :size="24" class="animate-spin" />
                                <Star v-else :size="24" />
                                <span>{{ buttonText }}</span>
                            </button>

                            <!-- Status Messages -->
                            <div v-if="hasClaimed" class="text-yellow-400 text-sm text-center">
                                <CheckCircle :size="16" class="inline mr-1" />
                                You've already claimed this bonus!
                            </div>
                            <div v-else-if="!hasSufficientBalance" class="text-red-400 text-sm text-center">
                                <AlertCircle :size="16" class="inline mr-1" />
                                Insufficient balance to claim this bonus
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bonus Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- How It Works -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                            <HelpCircle :size="20" class="mr-2" />
                            How It Works
                        </h3>
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">KES {{ bonusInfo.claim_cost.toLocaleString() }} is deducted from your deposit balance</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">KES {{ bonusInfo.bonus_amount.toLocaleString() }} is added to your balance</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Net profit: KES {{ (bonusInfo.bonus_amount - bonusInfo.claim_cost).toLocaleString() }} added instantly</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                            <FileText :size="20" class="mr-2" />
                            Terms & Conditions
                        </h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start space-x-2">
                                <Info :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-green-100">One-time claim per bonus type</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <Info :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-green-100">Amount is deducted from deposit_balance</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <Info :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-green-100">Bonus can be withdrawn immediately</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Other Bonuses -->
                <div class="mt-8">
                    <h3 class="text-xl font-bold text-white mb-6">Explore Other Bonuses</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <router-link 
                            to="/reward-center/welcome-bonus"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20 hover:border-purple-500 transition-all duration-200 group"
                        >
                            <div class="text-center">
                                <Gift :size="32" class="mx-auto text-purple-400 mb-2" />
                                <h4 class="text-white font-bold group-hover:text-purple-300 transition-colors">Welcome Bonus</h4>
                                <p class="text-purple-400 text-sm">KES 5,000 for KES 1,000</p>
                            </div>
                        </router-link>

                        <router-link 
                            to="/reward-center/pro-bonus"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                        >
                            <div class="text-center">
                                <Award :size="32" class="mx-auto text-blue-400 mb-2" />
                                <h4 class="text-white font-bold group-hover:text-blue-300 transition-colors">Pro Bonus</h4>
                                <p class="text-blue-400 text-sm">KES 10,000 for KES 2,000</p>
                            </div>
                        </router-link>

                        <router-link 
                            to="/reward-center/mega-bonus"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-4 shadow-2xl shadow-orange-500/20 hover:border-orange-500 transition-all duration-200 group"
                        >
                            <div class="text-center">
                                <Crown :size="32" class="mx-auto text-orange-400 mb-2" />
                                <h4 class="text-white font-bold group-hover:text-orange-300 transition-colors">Mega Bonus</h4>
                                <p class="text-orange-400 text-sm">KES 50,000 for KES 4,000</p>
                            </div>
                        </router-link>
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
    Star, Loader2, CheckCircle, AlertCircle, 
    HelpCircle, FileText, Info, Gift, Award, Crown
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({
    bonusInfo: Object,
    hasClaimed: Boolean,
    userBalance: Number
});

const processing = ref(false);

// Computed properties
const hasSufficientBalance = computed(() => {
    return props.userBalance >= props.bonusInfo.claim_cost;
});

const buttonText = computed(() => {
    if (props.hasClaimed) return 'Already Claimed';
    if (!hasSufficientBalance.value) return 'Insufficient Balance';
    if (processing.value) return 'Claiming...';
    return `Claim Bonus`;
});

const claimBonus = async () => {
    if (props.hasClaimed || !hasSufficientBalance.value || processing.value) return;

    processing.value = true;

    try {
        await router.post(route('reward-center.purchase'), {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash.success) {
                    alert(page.props.flash.success);
                    router.reload();
                }
                if (page.props.flash.error) {
                    alert(page.props.flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error claiming bonus:', errors);
                if (errors.message) {
                    alert(errors.message);
                }
            }
        });
    } catch (error) {
        console.error('Error claiming bonus:', error);
        alert('An unexpected error occurred. Please try again.');
    } finally {
        processing.value = false;
    }
};
</script>