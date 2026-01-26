<template>
    <DashboardLayout :title="job.title">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Briefcase :size="16" class="text-blue-400" />
            <Link href="/jobs" class="hover:text-blue-300">Jobs</Link>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">{{ job.title }}</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Job Header -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 lg:p-8 shadow-2xl shadow-blue-500/20 mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
                        <!-- Job Info -->
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ job.title }}</h1>
                                
                                <!-- Status Badges -->
                                <div class="flex flex-wrap gap-2">
                                    <span 
                                        v-if="job.is_featured"
                                        class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold flex items-center"
                                    >
                                        <Star :size="10" class="mr-1" />
                                        Featured
                                    </span>
                                    
                                    <span 
                                        v-if="job.is_remote"
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-3 py-1 rounded-full text-xs font-bold flex items-center"
                                    >
                                        <Home :size="10" class="mr-1" />
                                        Remote
                                    </span>
                                    
                                    <span 
                                        class="px-3 py-1 rounded-full text-xs font-bold"
                                        :style="`background-color: ${getCategoryColor(job.category.color)}20; color: ${getCategoryColor(job.category.color)}`"
                                    >
                                        <component 
                                            :is="getCategoryIcon(job.category.icon)" 
                                            :size="10" 
                                            class="mr-1 inline"
                                        />
                                        {{ job.category.name }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Job Meta -->
                            <div class="flex flex-wrap items-center gap-4 text-blue-300 mb-6">
                                <span class="flex items-center">
                                    <MapPin :size="16" class="mr-2" />
                                    {{ job.location }}
                                </span>
                                <span class="flex items-center">
                                    <Clock :size="16" class="mr-2" />
                                    {{ job.job_type }}
                                </span>
                                <span class="flex items-center">
                                    <TrendingUp :size="16" class="mr-2" />
                                    {{ job.experience_level }}
                                </span>
                                <span class="flex items-center">
                                    <Users :size="16" class="mr-2" />
                                    {{ job.vacancies }} vacancy{{ job.vacancies !== 1 ? 'ies' : '' }}
                                </span>
                            </div>
                            
                            <!-- Salary & Deadline -->
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <div class="text-3xl font-bold text-green-400 mb-1">{{ job.salary }}</div>
                                    <p class="text-sm text-blue-300">per {{ job.salary_type }}</p>
                                </div>
                                
                                <div class="text-right">
                                    <div class="text-lg font-bold text-white mb-1">
                                        <span v-if="job.days_remaining > 0">
                                            {{ job.days_remaining }} day{{ job.days_remaining !== 1 ? 's' : '' }} left
                                        </span>
                                        <span v-else class="text-red-400">
                                            Closed
                                        </span>
                                    </div>
                                    <p class="text-sm text-blue-300">Deadline: {{ job.application_deadline }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Apply Button -->
                        <div class="lg:w-64 flex-shrink-0">
                            <div class="bg-slate-900/50 border border-blue-500/30 rounded-xl p-4 mb-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-blue-300">Application Fee</span>
                                    <span class="text-lg font-bold text-green-400">KES 1,000</span>
                                </div>
                                
                                <div class="space-y-3">
                                    <div 
                                        v-if="job.has_applied"
                                        class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold py-3 px-4 rounded-lg text-center"
                                    >
                                        <div class="flex items-center justify-center space-x-2">
                                            <CheckCircle :size="20" />
                                            <span>Applied</span>
                                        </div>
                                        <p class="text-sm mt-1">Status: <span :style="`color: ${job.application_color}`">{{ job.application_status }}</span></p>
                                    </div>
                                    
                                    <button
                                        v-else-if="job.can_apply"
                                        @click="applyForJob"
                                        :disabled="parseFloat(userBalance) < 1000"
                                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-4 rounded-lg w-full transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <CheckCircle :size="20" />
                                        <span>Apply Now</span>
                                    </button>
                                    
                                    <div 
                                        v-else
                                        class="bg-gradient-to-r from-red-600 to-pink-600 text-white font-bold py-3 px-4 rounded-lg text-center"
                                    >
                                        <div class="flex items-center justify-center space-x-2">
                                            <X :size="20" />
                                            <span>Not Available</span>
                                        </div>
                                        <p class="text-sm mt-1">No vacancies left</p>
                                    </div>
                                </div>
                                
                                <!-- Balance Warning -->
                                <div v-if="!job.has_applied && parseFloat(userBalance) < 1000" class="mt-3 p-2 bg-red-600/20 border border-red-500/30 rounded text-sm text-red-300">
                                    <AlertCircle :size="14" class="inline mr-1" />
                                    You need at least KES 1,000 to apply
                                    <Link href="/wallet/deposit" class="text-red-400 hover:text-red-300 block mt-1">
                                        Add funds →
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="bg-slate-900/30 border border-blue-500/20 rounded p-2 text-center">
                                    <div class="text-xl font-bold text-white">{{ job.view_count }}</div>
                                    <div class="text-blue-300">Views</div>
                                </div>
                                <div class="bg-slate-900/30 border border-blue-500/20 rounded p-2 text-center">
                                    <div class="text-xl font-bold text-white">{{ job.application_count }}</div>
                                    <div class="text-blue-300">Applications</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Job Details -->
                    <div class="lg:col-span-2">
                        <!-- Description -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-xl font-bold text-white mb-4 flex items-center space-x-2">
                                <AlignLeft :size="20" class="text-blue-400" />
                                <span>Job Description</span>
                            </h3>
                            <div class="prose prose-invert max-w-none text-blue-200">
                                <p class="whitespace-pre-line">{{ job.description }}</p>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div v-if="job.requirements && job.requirements.length > 0" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-xl font-bold text-white mb-4 flex items-center space-x-2">
                                <ListChecks :size="20" class="text-blue-400" />
                                <span>Requirements</span>
                            </h3>
                            <ul class="space-y-2 text-blue-200">
                                <li v-for="(requirement, index) in job.requirements" :key="index" class="flex items-start space-x-2">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>{{ requirement }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Benefits -->
                        <div v-if="job.benefits && job.benefits.length > 0" class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-xl font-bold text-white mb-4 flex items-center space-x-2">
                                <Gift :size="20" class="text-green-400" />
                                <span>Benefits</span>
                            </h3>
                            <ul class="space-y-2 text-blue-200">
                                <li v-for="(benefit, index) in job.benefits" :key="index" class="flex items-start space-x-2">
                                    <CheckCircle :size="16" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>{{ benefit }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right Column - Sidebar -->
                    <div class="lg:col-span-1">
                        <!-- About Company -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center space-x-2">
                                <Building :size="18" class="text-purple-400" />
                                <span>Job Details</span>
                            </h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-300">Experience Level</span>
                                    <span class="font-bold text-white">{{ job.experience_level }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-300">Education Level</span>
                                    <span class="font-bold text-white">{{ job.education_level || 'Not Specified' }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-300">Job Type</span>
                                    <span class="font-bold text-white">{{ job.job_type }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-300">Remote Work</span>
                                    <span class="font-bold text-white">{{ job.is_remote ? 'Yes' : 'No' }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-purple-300">Posted On</span>
                                    <span class="font-bold text-white">{{ job.created_at }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Related Jobs -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Related Jobs</h3>
                            
                            <div v-if="relatedJobs.length === 0" class="text-center py-4">
                                <p class="text-blue-300">No related jobs found</p>
                            </div>
                            
                            <div v-else class="space-y-3">
                                <div 
                                    v-for="relatedJob in relatedJobs" 
                                    :key="relatedJob.id"
                                    class="border border-blue-500/20 rounded-lg p-3 hover:border-blue-500 transition-all duration-200 group"
                                >
                                    <Link :href="`/jobs/${relatedJob.id}`" class="block">
                                        <h4 class="font-bold text-white text-sm mb-1 group-hover:text-blue-300">{{ relatedJob.title }}</h4>
                                        <p class="text-xs text-blue-300 mb-1">{{ relatedJob.category_name }}</p>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-green-400 font-bold">{{ relatedJob.salary }}</span>
                                            <span class="text-blue-400 group-hover:text-blue-300">View →</span>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Share Job -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Share This Job</h3>
                            
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="shareJob('facebook')"
                                    class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded-lg transition-all duration-200 text-sm"
                                >
                                    Facebook
                                </button>
                                <button
                                    @click="shareJob('twitter')"
                                    class="flex-1 bg-sky-600 hover:bg-sky-500 text-white font-bold py-2 rounded-lg transition-all duration-200 text-sm"
                                >
                                    Twitter
                                </button>
                                <button
                                    @click="shareJob('linkedin')"
                                    class="flex-1 bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 rounded-lg transition-all duration-200 text-sm"
                                >
                                    LinkedIn
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
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Briefcase, MapPin, Clock, TrendingUp, Users, Star,
    Home, CheckCircle, X, AlertCircle, AlignLeft, ListChecks,
    Gift, Building
} from 'lucide-vue-next';

const props = defineProps({
    job: {
        type: Object,
        required: true
    },
    relatedJobs: {
        type: Array,
        default: () => []
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

// Utility functions
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

const getCategoryIcon = (iconName) => {
    // You'll need to import these icons or use a dynamic icon component
    return Briefcase; // Placeholder
};

const applyForJob = async () => {
    if (parseFloat(props.userBalance) < 1000) {
        alert('Insufficient balance. You need at least KES 1,000 to apply for this job.');
        return;
    }

    if (!confirm(`Are you sure you want to apply for "${props.job.title}"? KES 1,000 will be deducted from your balance.`)) {
        return;
    }

    try {
        await router.post(`/jobs/${props.job.id}/apply`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.visit('/jobs/my-jobs');
                } else if (flash.error) {
                    alert(flash.error);
                }
            },
            onError: (errors) => {
                console.error('Error applying for job:', errors);
                alert('Failed to apply for job. Please try again.');
            }
        });
    } catch (error) {
        console.error('Error applying for job:', error);
        alert('An unexpected error occurred.');
    }
};

const shareJob = (platform) => {
    const url = window.location.href;
    const title = props.job.title;
    const text = `Check out this job opportunity: ${props.job.title}`;
    
    const shareUrls = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
        twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`,
    };
    
    if (shareUrls[platform]) {
        window.open(shareUrls[platform], '_blank', 'width=600,height=400');
    }
};
</script>