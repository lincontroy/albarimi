<template>
    <DashboardLayout title="My Job Applications">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Briefcase :size="16" class="text-blue-400" />
            <span>Jobs</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">My Applications</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">My Job Applications</h1>
                    <p class="text-blue-300">Track and manage your job applications</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Applications -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <FileText :size="24" class="text-blue-400" />
                            <h3 class="text-lg font-bold text-white">Total Applications</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total }}</p>
                        <p class="text-sm text-blue-300">All time applications</p>
                    </div>

                    <!-- Pending Review -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Clock :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Pending Review</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.pending + stats.reviewing }}</p>
                        <p class="text-sm text-yellow-300">Awaiting employer response</p>
                    </div>

                    <!-- Shortlisted -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CheckCircle :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Shortlisted</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.shortlisted }}</p>
                        <p class="text-sm text-green-300">Advancing in process</p>
                    </div>

                    <!-- Total Spent -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <DollarSign :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Spent</h3>
                        </div>
                        <p class="text-3xl font-bold text-purple-400 mb-2">KES {{ formatCurrency(stats.total_spent) }}</p>
                        <p class="text-sm text-purple-300">Application fees total</p>
                    </div>
                </div>

                <!-- Applications Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-8">
                    <!-- Table Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white">All Applications</h2>
                        <div class="flex items-center space-x-2">
                            <Link 
                                href="/jobs"
                                class="text-blue-400 hover:text-blue-300 flex items-center space-x-1 text-sm"
                            >
                                <Plus :size="16" />
                                <span>Apply for More Jobs</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-12">
                        <Loader2 class="animate-spin mx-auto text-blue-400" :size="48" />
                        <p class="text-blue-300 mt-4">Loading applications...</p>
                    </div>

                    <!-- No Applications -->
                    <div v-else-if="applications.data.length === 0" class="text-center py-12">
                        <FileText :size="48" class="mx-auto text-blue-400 mb-3" />
                        <h4 class="text-lg font-bold text-white mb-2">No Applications Yet</h4>
                        <p class="text-blue-300 mb-4">You haven't applied for any jobs yet</p>
                        <Link 
                            href="/jobs"
                            class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                        >
                            <Briefcase :size="16" />
                            <span>Browse Jobs</span>
                        </Link>
                    </div>

                    <!-- Applications Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-blue-500/20">
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Job Details</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Date Applied</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Fee</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="application in applications.data" 
                                    :key="application.id"
                                    class="border-b border-blue-500/10 hover:bg-black/20 transition-colors"
                                >
                                    <!-- Job Details -->
                                    <td class="py-4 px-4">
                                        <div>
                                            <Link 
                                                :href="`/jobs/${application.job_id}`"
                                                class="text-white font-bold hover:text-blue-300 transition-colors"
                                            >
                                                {{ application.job_title }}
                                            </Link>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span 
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs"
                                                    :style="`background-color: ${getCategoryColor(application.job_category_color)}20; color: ${getCategoryColor(application.job_category_color)}`"
                                                >
                                                    {{ application.job_category }}
                                                </span>
                                                <span class="text-xs text-blue-300">{{ application.job_location }}</span>
                                                <span class="text-xs text-green-400">{{ application.job_salary }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${getStatusBgColor(application.status_color)}; color: ${application.status_color}`"
                                        >
                                            {{ application.status_text }}
                                        </span>
                                        <div v-if="application.days_since_applied > 0" class="text-xs text-blue-300 mt-1">
                                            Applied {{ application.days_since_applied }} day{{ application.days_since_applied !== 1 ? 's' : '' }} ago
                                        </div>
                                    </td>

                                    <!-- Date Applied -->
                                    <td class="py-4 px-4 text-blue-200 text-sm">
                                        {{ application.applied_at }}
                                        <div v-if="application.reviewed_at" class="text-xs text-green-300">
                                            Reviewed: {{ application.reviewed_at }}
                                        </div>
                                    </td>

                                    <!-- Fee -->
                                    <td class="py-4 px-4">
                                        <div class="text-green-400 font-bold">{{ application.formatted_fee }}</div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <Link 
                                                :href="`/jobs/${application.job_id}`"
                                                class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                            >
                                                <Eye :size="14" />
                                                <span>View Job</span>
                                            </Link>
                                            
                                            <button 
                                                v-if="application.status === 'pending'"
                                                @click="withdrawApplication(application.id)"
                                                class="text-red-400 hover:text-red-300 text-sm flex items-center space-x-1"
                                            >
                                                <X :size="14" />
                                                <span>Withdraw</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div v-if="applications.links && applications.links.length > 3" class="mt-6">
                            <div class="flex items-center justify-center space-x-2">
                                <template v-for="link in applications.links" :key="link.label">
                                    <Link 
                                        v-if="link.url"
                                        :href="link.url"
                                        :class="{
                                            'bg-blue-600 text-white': link.active,
                                            'bg-slate-800/50 text-slate-300 hover:bg-slate-700/50': !link.active
                                        }"
                                        class="px-4 py-2 rounded-lg transition-all duration-200"
                                        preserve-scroll
                                    >
                                        <span v-html="link.label"></span>
                                    </Link>
                                    
                                    <span 
                                        v-else
                                        class="bg-slate-800/30 text-slate-500 cursor-not-allowed px-4 py-2 rounded-lg"
                                    >
                                        <span v-html="link.label"></span>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Tips -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                        <Lightbulb :size="20" class="text-green-400" />
                        <span>Application Tips</span>
                    </h3>
                    <ul class="space-y-2 text-blue-200">
                        <li class="flex items-start space-x-2">
                            <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Applications are reviewed within 3-7 business days</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Application fees are non-refundable once processed</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <span>Keep your profile and resume updated for better chances</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                            <span>You can withdraw pending applications at any time</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Briefcase, FileText, Clock, CheckCircle, DollarSign,
    Plus, Loader2, Eye, X, Lightbulb
} from 'lucide-vue-next';

const props = defineProps({
    applications: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

const loading = ref(false);

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const getCategoryColor = (color) => {
    const colors = {
        blue: '#3b82f6',
        green: '#10b981',
        red: '#ef4444',
        purple: '#8b5cf6',
        orange: '#f97316',
        cyan: '#06b6d4',
        gray: '#6b7280',
        indigo: '#6366f1',
    };
    return colors[color] || '#3b82f6';
};

const getStatusBgColor = (color) => {
    const colors = {
        'bg-blue-500': 'rgba(59, 130, 246, 0.2)',
        'bg-yellow-500': 'rgba(250, 204, 21, 0.2)',
        'bg-green-500': 'rgba(34, 197, 94, 0.2)',
        'bg-red-500': 'rgba(239, 68, 68, 0.2)',
        'bg-gray-500': 'rgba(100, 116, 139, 0.2)',
    };
    return colors[color] || 'rgba(59, 130, 246, 0.2)';
};

const withdrawApplication = async (applicationId) => {
    if (!confirm('Are you sure you want to withdraw this application? This action cannot be undone.')) {
        return;
    }

    loading.value = true;

    try {
        await router.post(`/applications/${applicationId}/withdraw`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ preserveScroll: true });
            },
            onError: (errors) => {
                console.error('Error withdrawing application:', errors);
                alert('Failed to withdraw application. Please try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Error withdrawing application:', error);
        alert('An unexpected error occurred.');
        loading.value = false;
    }
};

onMounted(() => {
    // Any initialization code
});
</script>