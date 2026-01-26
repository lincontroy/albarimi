<template>
    <DashboardLayout title="Available Jobs">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <Briefcase :size="16" class="text-blue-400" />
            <span>Jobs</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Available Jobs</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Available Jobs</h1>
                    <p class="text-blue-300">Browse all available job opportunities</p>
                </div>

                <!-- Jobs Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="job in jobs.data" 
                        :key="job.id"
                        class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 hover:border-blue-500 transition-all duration-200 group"
                    >
                        <!-- Job Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <div class="flex items-center space-x-2 mb-2">
                                    <span 
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                                        :style="`background-color: ${getCategoryColor(job.category.color)}20; color: ${getCategoryColor(job.category.color)}`"
                                    >
                                        {{ job.category.name }}
                                    </span>
                                    <span v-if="job.is_remote" class="text-xs text-green-400 bg-green-400/10 px-2 py-0.5 rounded">
                                        Remote
                                    </span>
                                </div>
                                <h3 class="text-lg font-bold text-white mb-1 group-hover:text-blue-300">
                                    <Link :href="`/jobs/${job.id}`" class="hover:underline">
                                        {{ job.title }}
                                    </Link>
                                </h3>
                            </div>
                        </div>

                        <!-- Job Details -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm text-blue-300">
                                <MapPin :size="14" class="mr-2 flex-shrink-0" />
                                <span class="truncate">{{ job.location }}</span>
                            </div>
                            <div class="flex items-center text-sm text-blue-300">
                                <Clock :size="14" class="mr-2 flex-shrink-0" />
                                <span>{{ job.job_type }}</span>
                            </div>
                            <div class="flex items-center text-sm text-blue-300">
                                <DollarSign :size="14" class="mr-2 flex-shrink-0" />
                                <span class="font-bold text-green-400">{{ job.salary }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-blue-200 text-sm mb-4 line-clamp-2">
                            {{ job.description.substring(0, 120) }}...
                        </p>

                        <!-- Footer -->
                        <div class="flex items-center justify-between pt-4 border-t border-blue-500/20">
                            <div class="text-xs text-blue-300">
                                {{ job.days_remaining > 0 ? `${job.days_remaining} days left` : 'Closing soon' }}
                            </div>
                            
                            <div>
                                <Link 
                                    :href="`/jobs/${job.id}`"
                                    class="text-blue-400 hover:text-blue-300 text-sm font-semibold"
                                >
                                    View Details →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Jobs -->
                <div v-if="jobs.data.length === 0" class="text-center py-12">
                    <Briefcase :size="48" class="mx-auto text-blue-400 mb-3" />
                    <h4 class="text-lg font-bold text-white mb-2">No Jobs Available</h4>
                    <p class="text-blue-300">Check back later for new opportunities</p>
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
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Briefcase, MapPin, Clock, DollarSign } from 'lucide-vue-next';

const props = defineProps({
    jobs: {
        type: Object,
        default: () => ({ data: [], links: [] })
    },
    userBalance: {
        type: [Number, String],
        default: 0
    }
});

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
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>