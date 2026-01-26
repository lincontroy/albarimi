<template>
    <DashboardLayout title="Jobs">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Briefcase :size="16" class="text-blue-400" />
            <span>Jobs</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Find Jobs</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Find Your Dream Job</h1>
                    <p class="text-blue-300">Browse through 100+ job opportunities across various industries</p>
                </div>

                <!-- User Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Balance Card -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Wallet :size="24" class="text-purple-400" />
                            <h3 class="text-lg font-bold text-white">Your Balance</h3>
                        </div>
                        <p class="text-3xl font-bold text-purple-400 mb-2">KES {{ formatCurrency(userBalance) }}</p>
                        <p class="text-sm text-purple-300">KES 1,000 required per application</p>
                    </div>
                    
                    <!-- My Applications Card -->
                    <Link 
                        href="/jobs/my-jobs"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <div class="flex items-center space-x-3 mb-3">
                            <FileText :size="24" class="text-blue-400 group-hover:text-blue-300" />
                            <h3 class="text-lg font-bold text-white group-hover:text-blue-300">My Applications</h3>
                        </div>
                        <p class="text-2xl font-bold text-white mb-2">{{ hasAppliedJobs ? 'View Applications' : 'No Applications' }}</p>
                        <p class="text-sm text-blue-400 group-hover:text-blue-300">Track your job applications</p>
                    </Link>
                    
                    <!-- Featured Jobs Card -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Star :size="24" class="text-green-400" />
                            <h3 class="text-lg font-bold text-white">Featured Jobs</h3>
                        </div>
                        <p class="text-2xl font-bold text-white mb-2">{{ featuredJobs.length }}</p>
                        <p class="text-sm text-green-300">Premium job opportunities</p>
                    </div>
                    
                    <!-- Remote Jobs Card -->
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-6 shadow-2xl shadow-orange-500/20">
                        <div class="flex items-center space-x-3 mb-3">
                            <Home :size="24" class="text-orange-400" />
                            <h3 class="text-lg font-bold text-white">Remote Jobs</h3>
                        </div>
                        <p class="text-2xl font-bold text-white mb-2">{{ jobs.data.filter(j => j.is_remote).length }}</p>
                        <p class="text-sm text-orange-300">Work from anywhere</p>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <!-- Search Bar -->
                        <div class="mb-6">
                            <div class="relative">
                                <Search :size="20" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-400" />
                                <input
                                    v-model="search"
                                    @input="debouncedFilter"
                                    placeholder="Search jobs by title, description, or location..."
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl pl-12 pr-4 py-3 focus:outline-none focus:border-blue-400"
                                />
                            </div>
                        </div>

                        <!-- Filter Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <!-- Category Filter -->
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    <Tag :size="16" class="inline mr-2" />
                                    Category
                                </label>
                                <select 
                                    v-model="category"
                                    @change="filterJobs"
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }} ({{ cat.job_count }})
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Job Type Filter -->
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    <Briefcase :size="16" class="inline mr-2" />
                                    Job Type
                                </label>
                                <select 
                                    v-model="jobType"
                                    @change="filterJobs"
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="(text, key) in jobTypes" :key="key" :value="key">
                                        {{ text }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Experience Filter -->
                            <div>
                                <label class="block text-sm font-medium text-blue-300 mb-2">
                                    <TrendingUp :size="16" class="inline mr-2" />
                                    Experience
                                </label>
                                <select 
                                    v-model="experience"
                                    @change="filterJobs"
                                    class="w-full bg-black/30 border border-blue-500/30 text-white rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400"
                                >
                                    <option value="">All Levels</option>
                                    <option v-for="(text, key) in experienceLevels" :key="key" :value="key">
                                        {{ text }}
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Filter Buttons -->
                            <div class="flex items-end space-x-2">
                                <button
                                    @click="toggleRemote"
                                    :class="remote ? 'bg-blue-600 text-white' : 'bg-black/30 text-blue-300'"
                                    class="flex-1 border border-blue-500/30 rounded-lg px-3 py-2 text-sm transition-colors"
                                >
                                    <Home :size="14" class="inline mr-1" />
                                    Remote
                                </button>
                                <button
                                    @click="toggleFeatured"
                                    :class="featured ? 'bg-yellow-600 text-white' : 'bg-black/30 text-yellow-300'"
                                    class="flex-1 border border-yellow-500/30 rounded-lg px-3 py-2 text-sm transition-colors"
                                >
                                    <Star :size="14" class="inline mr-1" />
                                    Featured
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="flex-1 border border-red-500/30 text-red-300 hover:bg-red-500/20 rounded-lg px-3 py-2 text-sm transition-colors"
                                >
                                    <X :size="14" class="inline mr-1" />
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Jobs List -->
                    <div class="lg:w-2/3">
                        <!-- Results Header -->
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">
                                {{ jobs.total }} Job{{ jobs.total !== 1 ? 's' : '' }} Found
                            </h2>
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="loadJobs"
                                    class="text-blue-400 hover:text-blue-300 flex items-center space-x-1"
                                >
                                    <RefreshCw :size="16" />
                                    <span class="text-sm">Refresh</span>
                                </button>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-12">
                            <Loader2 class="animate-spin mx-auto text-blue-400" :size="48" />
                            <p class="text-blue-300 mt-4">Loading jobs...</p>
                        </div>

                        <!-- No Results -->
                        <div v-else-if="jobs.data.length === 0" class="text-center py-12 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl">
                            <Search :size="48" class="mx-auto text-blue-400 mb-3" />
                            <h4 class="text-lg font-bold text-white mb-2">No Jobs Found</h4>
                            <p class="text-blue-300 mb-4">Try adjusting your filters or search terms</p>
                            <button @click="clearFilters" class="text-blue-400 hover:text-blue-300 text-sm">
                                Clear all filters
                            </button>
                        </div>

                        <!-- Jobs Grid -->
                        <div v-else class="space-y-4">
                            <div 
                                v-for="job in jobs.data" 
                                :key="job.id"
                                class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                            >
                                <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                                    <!-- Job Category Badge -->
                                    <div class="flex-shrink-0">
                                        <span 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                            :style="`background-color: ${getCategoryColor(job.category.color)}20; color: ${getCategoryColor(job.category.color)}`"
                                        >
                                            <component 
                                                :is="getCategoryIcon(job.category.icon)" 
                                                :size="12" 
                                                class="mr-1"
                                            />
                                            {{ job.category.name }}
                                        </span>
                                    </div>
                                    
                                    <!-- Job Details -->
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <h3 class="text-lg font-bold text-white mb-1 group-hover:text-blue-300">
                                                    <Link :href="`/jobs/${job.id}`" class="hover:underline">
                                                        {{ job.title }}
                                                    </Link>
                                                </h3>
                                                <div class="flex flex-wrap items-center gap-2 text-sm text-blue-300">
                                                    <span class="flex items-center">
                                                        <MapPin :size="12" class="mr-1" />
                                                        {{ job.location }}
                                                    </span>
                                                    <span>•</span>
                                                    <span>{{ job.job_type }}</span>
                                                    <span v-if="job.is_remote" class="flex items-center text-green-400">
                                                        <Home :size="12" class="mr-1" />
                                                        Remote
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Salary -->
                                            <div class="text-right">
                                                <p class="text-lg font-bold text-green-400">{{ job.salary }}</p>
                                                <p class="text-sm text-blue-300">{{ job.experience_level }}</p>
                                            </div>
                                        </div>
                                        
                                        <p class="text-blue-200 text-sm mb-4 line-clamp-2">
                                            {{ job.description.substring(0, 150) }}...
                                        </p>
                                        
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <!-- Status & Info -->
                                            <div class="flex flex-wrap items-center gap-3">
                                                <span 
                                                    class="px-2 py-1 rounded text-xs font-semibold"
                                                    :style="`background-color: ${getStatusBgColor(job.status_badge.color)}; color: ${getStatusTextColor(job.status_badge.color)}`"
                                                >
                                                    {{ job.status_badge.text }}
                                                </span>
                                                
                                                <div class="flex items-center space-x-4 text-sm text-blue-300">
                                                    <span class="flex items-center">
                                                        <Eye :size="12" class="mr-1" />
                                                        {{ job.view_count }} views
                                                    </span>
                                                    <span class="flex items-center">
                                                        <Users :size="12" class="mr-1" />
                                                        {{ job.application_count }} applications
                                                    </span>
                                                    <span v-if="job.days_remaining !== null" class="flex items-center">
                                                        <Clock :size="12" class="mr-1" />
                                                        {{ job.days_remaining }} days left
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center space-x-2">
                                                <Link 
                                                    :href="`/jobs/${job.id}`"
                                                    class="text-blue-400 hover:text-blue-300 flex items-center space-x-1 text-sm"
                                                >
                                                    <Eye :size="14" />
                                                    <span>View Details</span>
                                                </Link>
                                                
                                                <button
                                                    v-if="!job.has_applied && job.can_apply"
                                                    @click="applyForJob(job.id)"
                                                    :disabled="parseFloat(userBalance) < 1000"
                                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-sm flex items-center space-x-1"
                                                >
                                                    <CheckCircle :size="14" />
                                                    <span>Apply Now</span>
                                                </button>
                                                
                                                <span 
                                                    v-else-if="job.has_applied"
                                                    class="px-3 py-2 rounded-lg bg-blue-600/20 text-blue-400 text-sm"
                                                >
                                                    Applied
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Featured Badge -->
                                <div v-if="job.is_featured" class="absolute top-4 right-4">
                                    <span class="bg-gradient-to-r from-yellow-600 to-orange-600 text-white px-3 py-1 rounded-full text-xs font-bold flex items-center">
                                        <Star :size="10" class="mr-1" />
                                        Featured
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="jobs.links && jobs.links.length > 3" class="mt-8">
                            <div class="flex items-center justify-center space-x-2">
                                <template v-for="link in jobs.links" :key="link.label">
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
                    
                    <!-- Sidebar -->
                    <div class="lg:w-1/3">
                        <!-- Featured Jobs -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                                    <Star :size="18" class="text-yellow-400" />
                                    <span>Featured Jobs</span>
                                </h3>
                                <Link 
                                    href="/jobs/available"
                                    class="text-blue-400 hover:text-blue-300 text-sm"
                                >
                                    View All
                                </Link>
                            </div>
                            
                            <div class="space-y-3">
                                <div 
                                    v-for="featuredJob in featuredJobs" 
                                    :key="featuredJob.id"
                                    class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-xl p-4 hover:border-yellow-500 transition-all duration-200"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <h4 class="font-bold text-white text-sm mb-1">
                                                <Link :href="`/jobs/${featuredJob.id}`" class="hover:text-yellow-300">
                                                    {{ featuredJob.title }}
                                                </Link>
                                            </h4>
                                            <p class="text-xs text-yellow-300">{{ featuredJob.category_name }}</p>
                                        </div>
                                        <span class="text-green-400 text-sm font-bold">{{ featuredJob.salary }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-blue-300">
                                        <span>{{ featuredJob.location }}</span>
                                        <Link 
                                            :href="`/jobs/${featuredJob.id}`"
                                            class="text-yellow-400 hover:text-yellow-300"
                                        >
                                            View →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Browse Categories</h3>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <Link
                                    v-for="cat in categories"
                                    :key="cat.id"
                                    :href="`/jobs?category=${cat.id}`"
                                    class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border rounded-xl p-3 hover:border-opacity-100 transition-all duration-200 group"
                                    :style="`border-color: ${getCategoryColor(cat.color)}30`"
                                >
                                    <div class="flex items-center space-x-2">
                                        <component 
                                            :is="getCategoryIcon(cat.icon)" 
                                            :size="16" 
                                            :style="`color: ${getCategoryColor(cat.color)}`"
                                        />
                                        <div>
                                            <h4 class="text-sm font-bold text-white group-hover:text-blue-300">{{ cat.name }}</h4>
                                            <p class="text-xs" :style="`color: ${getCategoryColor(cat.color)}`">{{ cat.job_count }} jobs</p>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Job Statistics</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-purple-300">Total Jobs Listed</span>
                                    <span class="font-bold text-white">{{ jobs.total }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-purple-300">Remote Jobs</span>
                                    <span class="font-bold text-white">{{ jobs.data.filter(j => j.is_remote).length }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-purple-300">Featured Jobs</span>
                                    <span class="font-bold text-white">{{ jobs.data.filter(j => j.is_featured).length }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-purple-300">Application Fee</span>
                                    <span class="font-bold text-green-400">KES 1,000</span>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-purple-500/20">
                                <p class="text-sm text-purple-300 mb-2">Need more balance to apply?</p>
                                <Link 
                                    href="/wallet/deposit"
                                    class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 text-sm w-full text-center block"
                                >
                                    Deposit Funds
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Briefcase, Wallet, FileText, Star, Home, Search,
    Tag, TrendingUp, X, RefreshCw, Loader2, MapPin,
    Eye, Users, Clock, CheckCircle
} from 'lucide-vue-next';

// Import category icons dynamically
import {
    Code, TrendingUp as TrendingUpIcon, DollarSign, Heart,
    GraduationCap, Headphones, Cog, Palette, Users as UsersIcon
} from 'lucide-vue-next';

const props = defineProps({
    jobs: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    categories: {
        type: Array,
        default: () => []
    },
    featuredJobs: {
        type: Array,
        default: () => []
    },
    jobTypes: {
        type: Object,
        default: () => ({})
    },
    experienceLevels: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    userBalance: {
        type: [Number, String],
        default: 0
    },
    hasAppliedJobs: {
        type: Boolean,
        default: false
    }
});

// State
const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');
const jobType = ref(props.filters.job_type || '');
const experience = ref(props.filters.experience || '');
const remote = ref(props.filters.remote || false);
const featured = ref(props.filters.featured || false);
const loading = ref(false);
let debounceTimer = null;

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
        emerald: '#10b981',
        red: '#ef4444',
        purple: '#8b5cf6',
        orange: '#f97316',
        cyan: '#06b6d4',
        pink: '#ec4899',
        gray: '#6b7280',
        indigo: '#6366f1',
    };
    return colors[color] || '#3b82f6';
};

const getCategoryIcon = (iconName) => {
    const icons = {
        Code,
        TrendingUp: TrendingUpIcon,
        DollarSign,
        Heart,
        GraduationCap,
        Headphones,
        Cog,
        Palette,
        Users: UsersIcon,
        Briefcase,
    };
    return icons[iconName] || Briefcase;
};

const getStatusBgColor = (color) => {
    const colors = {
        green: 'rgba(34, 197, 94, 0.2)',
        yellow: 'rgba(250, 204, 21, 0.2)',
        red: 'rgba(239, 68, 68, 0.2)',
        blue: 'rgba(59, 130, 246, 0.2)',
        gray: 'rgba(100, 116, 139, 0.2)',
    };
    return colors[color] || colors.gray;
};

const getStatusTextColor = (color) => {
    const colors = {
        green: '#4ade80',
        yellow: '#fbbf24',
        red: '#f87171',
        blue: '#60a5fa',
        gray: '#94a3b8',
    };
    return colors[color] || colors.gray;
};

// Filter functions
const debouncedFilter = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        filterJobs();
    }, 500);
};

const filterJobs = () => {
    loading.value = true;
    
    const params = {};
    if (search.value) params.search = search.value;
    if (category.value) params.category = category.value;
    if (jobType.value) params.job_type = jobType.value;
    if (experience.value) params.experience = experience.value;
    if (remote.value) params.remote = remote.value;
    if (featured.value) params.featured = featured.value;
    
    router.get('/jobs', params, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        }
    });
};

const toggleRemote = () => {
    remote.value = !remote.value;
    filterJobs();
};

const toggleFeatured = () => {
    featured.value = !featured.value;
    filterJobs();
};

const clearFilters = () => {
    search.value = '';
    category.value = '';
    jobType.value = '';
    experience.value = '';
    remote.value = false;
    featured.value = false;
    filterJobs();
};

const loadJobs = () => {
    router.reload({ preserveScroll: true });
};

const applyForJob = async (jobId) => {
    if (parseFloat(props.userBalance) < 1000) {
        alert('Insufficient balance. You need at least KES 1,000 to apply for a job.');
        return;
    }

    if (!confirm('Are you sure you want to apply for this job? KES 1,000 will be deducted from your balance.')) {
        return;
    }

    loading.value = true;

    try {
        await router.post(`/jobs/${jobId}/apply`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.visit('/jobs/my-jobs');
                } else if (flash.error) {
                    alert(flash.error);
                    loading.value = false;
                }
            },
            onError: (errors) => {
                console.error('Error applying for job:', errors);
                alert('Failed to apply for job. Please try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Error applying for job:', error);
        alert('An unexpected error occurred.');
        loading.value = false;
    }
};

// Initialize filters from props
onMounted(() => {
    search.value = props.filters.search || '';
    category.value = props.filters.category || '';
    jobType.value = props.filters.job_type || '';
    experience.value = props.filters.experience || '';
    remote.value = props.filters.remote || false;
    featured.value = props.filters.featured || false;
});
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>