<template>
    <DashboardLayout title="Endorsements">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Award :size="16" class="text-purple-400" />
            <span>Endorsements</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Dashboard</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Get Verified & Endorsed</h1>
                    <p class="text-purple-300">Build trust and credibility with verified endorsements</p>
                </div>

                <!-- Package Card -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-8 shadow-2xl shadow-purple-500/20">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                            <div class="mb-6 lg:mb-0">
                                <h2 class="text-lg font-medium text-purple-300 mb-2">Premium Endorsement Package</h2>
                                <div class="text-4xl lg:text-5xl font-bold text-white mb-2">KES {{ formatCurrency(packageFee) }}</div>
                                <p class="text-purple-300">One-time fee for 12 months validity</p>
                                <div class="flex items-center space-x-2 mt-3">
                                    <CheckCircle :size="16" class="text-green-400" />
                                    <span class="text-sm text-green-300">Includes verification badge</span>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <Link 
                                    href="/endorsement/apply"
                                    :class="hasPendingApplication ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 text-white font-bold py-3 px-8 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                >
                                    <FilePlus :size="20" />
                                    <span>{{ hasPendingApplication ? 'Application Pending' : 'Apply Now' }}</span>
                                </Link>
                                
                                <div v-if="!hasEnoughBalance" class="text-center">
                                    <p class="text-sm text-red-400">Need KES {{ formatCurrency(packageFee - userBalance) }} more</p>
                                </div>
                                
                                <Link 
                                    href="/endorsement/status"
                                    class="border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 py-3 px-8 rounded-lg text-center block transition-all duration-200"
                                >
                                    Check Status
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Active Endorsements -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Award :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Active Endorsements</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.active_endorsements || 0 }}</p>
                        <p class="text-sm text-green-300">Currently active</p>
                    </div>

                    <!-- Total Views -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Eye :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Total Views</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ formatCurrency(stats.total_views || 0) }}</p>
                        <p class="text-sm text-blue-300">Profile impressions</p>
                    </div>

                    <!-- Endorsements Count -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <ThumbsUp :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Endorsements</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ formatCurrency(stats.total_endorsements || 0) }}</p>
                        <p class="text-sm text-purple-300">Total endorsements</p>
                    </div>

                    <!-- Applications -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <FileText :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Applications</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total_applications || 0 }}</p>
                        <p class="text-sm text-yellow-300">Total applications</p>
                    </div>
                </div>

                <!-- Active Endorsements -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">Active Endorsements</h2>
                        <Link 
                            href="/endorsement/status"
                            class="text-purple-400 hover:text-purple-300 text-sm"
                        >
                            View All
                        </Link>
                    </div>

                    <div v-if="activeEndorsements.length === 0" class="text-center py-12 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl">
                        <Award :size="48" class="mx-auto text-purple-400 mb-3" />
                        <h4 class="text-lg font-bold text-white mb-2">No Active Endorsements</h4>
                        <p class="text-purple-300 mb-4">Get verified to build trust and credibility</p>
                        <Link 
                            v-if="!hasPendingApplication"
                            href="/endorsement/apply"
                            class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <FilePlus :size="16" />
                            <span>Apply for Endorsement</span>
                        </Link>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div 
                            v-for="endorsement in activeEndorsements" 
                            :key="endorsement.id"
                            class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <div 
                                        class="w-10 h-10 rounded-full flex items-center justify-center"
                                        :style="`background-color: ${endorsement.type_color}20`"
                                    >
                                        <component 
                                            :is="getTypeIcon(endorsement.type_icon)" 
                                            :size="20" 
                                            :style="`color: ${endorsement.type_color}`"
                                        />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white text-sm">{{ endorsement.type_text }}</h3>
                                        <p class="text-xs text-blue-300">{{ endorsement.application_id }}</p>
                                    </div>
                                </div>
                                <span 
                                    class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold"
                                    :style="`background-color: ${endorsement.status_badge.bgColor}; color: ${endorsement.status_badge.color}`"
                                >
                                    {{ endorsement.status_badge.text }}
                                </span>
                            </div>
                            
                            <h4 class="text-lg font-bold text-white mb-2 group-hover:text-blue-300">{{ endorsement.title }}</h4>
                            <p class="text-blue-200 text-sm mb-4 line-clamp-2">{{ endorsement.description }}</p>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-blue-300">
                                            <Eye :size="14" class="inline mr-1" />
                                            {{ endorsement.views }}
                                        </span>
                                        <span class="text-purple-300">
                                            <ThumbsUp :size="14" class="inline mr-1" />
                                            {{ endorsement.endorsements_count }}
                                        </span>
                                    </div>
                                    <span class="text-green-400 font-bold">{{ endorsement.package_fee }}</span>
                                </div>
                                
                                <div v-if="endorsement.days_remaining !== null" class="pt-3 border-t border-blue-500/20">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-blue-300">Expires in</span>
                                        <span :class="endorsement.days_remaining < 30 ? 'text-red-400' : 'text-green-400'">
                                            {{ endorsement.days_remaining }} days
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-700 rounded-full h-2">
                                        <div 
                                            :class="endorsement.days_remaining < 30 ? 'bg-red-500' : 'bg-blue-500'"
                                            class="h-2 rounded-full"
                                            :style="`width: ${Math.max(0, Math.min(100, 100 - (endorsement.days_remaining / 365 * 100)))}%`"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Benefits Section -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h3 class="text-lg font-bold text-white mb-6">Why Get Endorsed?</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-600/20 rounded-full mb-3">
                                <ShieldCheck :size="24" class="text-green-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">Build Trust</h4>
                            <p class="text-sm text-green-300">Verified badge increases credibility with clients and employers</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-600/20 rounded-full mb-3">
                                <TrendingUp :size="24" class="text-blue-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">More Visibility</h4>
                            <p class="text-sm text-blue-300">Endorsed profiles get priority placement in search results</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-purple-600/20 rounded-full mb-3">
                                <Star :size="24" class="text-purple-400" />
                            </div>
                            <h4 class="font-bold text-white mb-2">Professional Edge</h4>
                            <p class="text-sm text-purple-300">Stand out from competition with verified credentials</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Award, CheckCircle, FilePlus, Eye, ThumbsUp,
    FileText, ShieldCheck, TrendingUp, Star,
    Briefcase, Users, Package, Building
} from 'lucide-vue-next';

const props = defineProps({
    activeEndorsements: {
        type: Array,
        default: () => []
    },
    hasPendingApplication: {
        type: Boolean,
        default: false
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    userBalance: {
        type: [Number, String],
        default: 0
    },
    packageFee: {
        type: Number,
        default: 3500
    }
});

const hasEnoughBalance = parseFloat(props.userBalance) >= props.packageFee;

const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getTypeIcon = (iconName) => {
    const icons = {
        'briefcase': Briefcase,
        'award': Award,
        'users': Users,
        'package': Package,
        'building': Building,
    };
    return icons[iconName] || Briefcase;
};
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>