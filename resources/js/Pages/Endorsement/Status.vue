<template>
    <DashboardLayout title="Endorsement Status">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Award :size="16" class="text-purple-400" />
            <span>Endorsements</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Application Status</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Application Status</h1>
                    <p class="text-purple-300">Track your endorsement applications</p>
                </div>

                <!-- Latest Application Status -->
                <div v-if="latestApplication" class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">Latest Application</h2>
                            <div class="text-blue-300 text-sm">
                                ID: {{ latestApplication.application_id }}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Application Details -->
                            <div>
                                <h3 class="font-bold text-white mb-2">{{ latestApplication.title }}</h3>
                                <div class="flex items-center space-x-2 mb-3">
                                    <span 
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        :style="`background-color: ${latestApplication.type_color}20; color: ${latestApplication.type_color}`"
                                    >
                                        {{ latestApplication.type_text }}
                                    </span>
                                    <span 
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        :style="`background-color: ${latestApplication.status_badge.bgColor}; color: ${latestApplication.status_badge.color}`"
                                    >
                                        {{ latestApplication.status_badge.text }}
                                    </span>
                                </div>
                                <div class="text-sm text-blue-300">
                                    Submitted: {{ latestApplication.submitted_at }}
                                </div>
                                <div v-if="latestApplication.reviewed_at" class="text-sm text-green-300">
                                    Reviewed: {{ latestApplication.reviewed_at }}
                                </div>
                            </div>

                            <!-- Status Timeline -->
                            <div>
                                <h4 class="font-bold text-white mb-3">Application Progress</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                            <CheckCircle :size="16" class="text-white" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm">Submitted</div>
                                            <div class="text-xs text-blue-300">{{ latestApplication.submitted_at }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full" :class="latestApplication.status === 'pending' ? 'bg-yellow-500' : 'bg-blue-500'">
                                            <div class="flex items-center justify-center h-full">
                                                <Clock v-if="latestApplication.status === 'pending'" :size="16" class="text-white" />
                                                <CheckCircle v-else :size="16" class="text-white" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm">Under Review</div>
                                            <div v-if="latestApplication.reviewed_at" class="text-xs text-green-300">
                                                {{ latestApplication.reviewed_at }}
                                            </div>
                                            <div v-else class="text-xs text-yellow-300">
                                                3-5 business days
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full" :class="['approved', 'active'].includes(latestApplication.status) ? 'bg-green-500' : 'bg-gray-500'">
                                            <div class="flex items-center justify-center h-full">
                                                <CheckCircle v-if="['approved', 'active'].includes(latestApplication.status)" :size="16" class="text-white" />
                                                <Clock v-else :size="16" class="text-white" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm">Final Decision</div>
                                            <div v-if="latestApplication.status === 'approved'" class="text-xs text-green-300">
                                                Approved ✓
                                            </div>
                                            <div v-else-if="latestApplication.status === 'rejected'" class="text-xs text-red-300">
                                                Rejected ✗
                                            </div>
                                            <div v-else class="text-xs text-blue-300">
                                                Awaiting decision
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Review Notes -->
                            <div v-if="latestApplication.review_notes">
                                <h4 class="font-bold text-white mb-3">Review Notes</h4>
                                <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                                    <p class="text-sm text-blue-200">{{ latestApplication.review_notes }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="latestApplication.status === 'pending'" class="mt-6 pt-6 border-t border-blue-500/20">
                            <div class="flex space-x-3">
                                <Link 
                                    href="/endorsement/apply"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200"
                                >
                                    Edit Application
                                </Link>
                                <button 
                                    @click="cancelApplication(latestApplication.id)"
                                    class="border border-red-500/30 text-red-400 hover:bg-red-500/20 py-2 px-6 rounded-lg transition-all duration-200"
                                >
                                    Cancel Application
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Application -->
                <div v-else class="mb-8 text-center py-12 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl">
                    <Award :size="48" class="mx-auto text-purple-400 mb-3" />
                    <h4 class="text-lg font-bold text-white mb-2">No Applications Yet</h4>
                    <p class="text-purple-300 mb-4">You haven't applied for any endorsements yet</p>
                    <Link 
                        href="/endorsement/apply"
                        class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-6 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                    >
                        <FilePlus :size="16" />
                        <span>Apply for Endorsement</span>
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Applications -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <FileText :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Total Applications</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.total || 0 }}</p>
                        <p class="text-sm text-purple-300">All applications</p>
                    </div>

                    <!-- Pending Review -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Clock :size="24" class="text-yellow-400" />
                            <h3 class="text-lg font-bold text-white">Pending Review</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.pending + stats.under_review || 0 }}</p>
                        <p class="text-sm text-yellow-300">Awaiting review</p>
                    </div>

                    <!-- Approved -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <CheckCircle :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Approved</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.approved + stats.active || 0 }}</p>
                        <p class="text-sm text-green-300">Successful applications</p>
                    </div>

                    <!-- Rejected -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-red-500/30 rounded-2xl p-6 shadow-2xl shadow-red-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <XCircle :size="24" class="text-red-400" />
                            <h3 class="text-lg font-bold text-white">Rejected</h3>
                        </div>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.rejected || 0 }}</p>
                        <p class="text-sm text-red-300">Unsuccessful applications</p>
                    </div>
                </div>

                <!-- All Applications -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <h2 class="text-xl font-bold text-white mb-6">All Applications</h2>

                    <div v-if="applications.length === 0" class="text-center py-8">
                        <FileText :size="48" class="mx-auto text-blue-400 mb-3" />
                        <p class="text-blue-300">No applications found</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-blue-500/20">
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Application Details</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Type</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Dates</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Fee</th>
                                    <th class="text-left py-3 px-4 text-blue-300 font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="application in applications" 
                                    :key="application.id"
                                    class="border-b border-blue-500/10 hover:bg-black/20 transition-colors"
                                >
                                    <!-- Application Details -->
                                    <td class="py-4 px-4">
                                        <div>
                                            <div class="text-white font-bold text-sm mb-1">
                                                {{ application.title }}
                                            </div>
                                            <div class="text-xs text-blue-300">
                                                ID: {{ application.application_id }}
                                            </div>
                                            <div class="text-xs text-blue-300">
                                                {{ application.description.substring(0, 50) }}...
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Type -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${application.type_color}20; color: ${application.type_color}`"
                                        >
                                            {{ application.type_text }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-4 px-4">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${application.status_badge.bgColor}; color: ${application.status_badge.color}`"
                                        >
                                            {{ application.status_badge.text }}
                                        </span>
                                        <div v-if="application.review_notes" class="text-xs text-blue-300 mt-1">
                                            {{ application.review_notes.substring(0, 30) }}...
                                        </div>
                                    </td>

                                    <!-- Dates -->
                                    <td class="py-4 px-4 text-blue-200 text-sm">
                                        <div>Submitted: {{ application.submitted_at }}</div>
                                        <div v-if="application.reviewed_at" class="text-green-300">
                                            Reviewed: {{ application.reviewed_at }}
                                        </div>
                                        <div v-if="application.expires_at" class="text-yellow-300">
                                            Expires: {{ application.expires_at }}
                                        </div>
                                    </td>

                                    <!-- Fee -->
                                    <td class="py-4 px-4">
                                        <div class="text-green-400 font-bold">{{ application.package_fee }}</div>
                                        <div v-if="application.days_remaining !== null" class="text-xs text-blue-300">
                                            {{ application.days_remaining }} days left
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col space-y-2">
                                            <button 
                                                @click="viewApplication(application.id)"
                                                class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                            >
                                                <Eye :size="14" />
                                                <span>View Details</span>
                                            </button>
                                            
                                            <Link 
                                                v-if="application.status === 'active'"
                                                href="/endorsement"
                                                class="text-green-400 hover:text-green-300 text-sm flex items-center space-x-1"
                                            >
                                                <ExternalLink :size="14" />
                                                <span>View Public</span>
                                            </Link>
                                            
                                            <button 
                                                v-if="application.status === 'pending'"
                                                @click="cancelApplication(application.id)"
                                                class="text-red-400 hover:text-red-300 text-sm flex items-center space-x-1"
                                            >
                                                <X :size="14" />
                                                <span>Cancel</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Balance Info -->
                <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between">
                        <div class="mb-4 lg:mb-0">
                            <h3 class="text-lg font-bold text-white mb-2">Need to Apply?</h3>
                            <p class="text-green-300">Ensure you have enough balance for the KES {{ formatCurrency(packageFee) }} package fee</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <Link 
                                href="/endorsement/apply"
                                class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-center text-sm"
                            >
                                Apply Now
                            </Link>
                            <Link 
                                href="/wallet/deposit"
                                class="border border-green-500/30 text-green-400 hover:bg-green-500/20 py-2 px-4 rounded-lg text-center text-sm transition-all duration-200"
                            >
                                Add Funds
                            </Link>
                        </div>
                    </div>
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
    Award, FileText, Clock, CheckCircle, XCircle,
    FilePlus, Eye, ExternalLink, X
} from 'lucide-vue-next';

const props = defineProps({
    latestApplication: {
        type: Object,
        default: null
    },
    applications: {
        type: Array,
        default: () => []
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

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const viewApplication = (applicationId) => {
    // In a real app, this would open a modal or navigate to details page
    alert(`View application ${applicationId} details`);
};

const cancelApplication = async (applicationId) => {
    if (!confirm('Are you sure you want to cancel this application? The package fee is non-refundable.')) {
        return;
    }

    try {
        // In a real app, you would have an API endpoint to cancel applications
        alert('Application cancellation would be processed here');
    } catch (error) {
        console.error('Cancel application error:', error);
        alert('Failed to cancel application');
    }
};
</script>