<template>
    <DashboardLayout title="Submit Views">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Eye :size="16" class="text-purple-400" />
            <span>Cash Flow</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Submit Views</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Submit Views Form -->
                <div class="lg:col-span-2 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                    <h2 class="text-2xl font-bold text-white mb-2">Submit WhatsApp Views</h2>
                    <p class="text-purple-300 mb-6">Submit your WhatsApp status/story views for verification and earning</p>

                    <form @submit.prevent="submitViews" class="space-y-6">
                        <!-- WhatsApp Number -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                WhatsApp Number <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Phone :size="20" class="text-purple-400" />
                                </div>
                                <input
                                    v-model="form.whatsapp_number"
                                    type="tel"
                                    required
                                    placeholder="254712345678"
                                    class="w-full bg-slate-800/50 border border-purple-500/30 rounded-xl pl-10 pr-4 py-3 text-white placeholder-purple-400/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                />
                            </div>
                            <p class="text-xs text-purple-400 mt-1">Format: 254712345678 (without +)</p>
                        </div>

                        <!-- Number of Views -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                Number of Views <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Eye :size="20" class="text-purple-400" />
                                </div>
                                <input
                                    v-model.number="form.views_count"
                                    type="number"
                                    min="1"
                                    max="10000"
                                    required
                                    placeholder="Enter number of views"
                                    class="w-full bg-slate-800/50 border border-purple-500/30 rounded-xl pl-10 pr-4 py-3 text-white placeholder-purple-400/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                />
                            </div>
                            <p class="text-xs text-purple-400 mt-1">Maximum 10,000 views per submission</p>
                        </div>

                     

                        <!-- Screenshot Upload -->
                        <div>
                            <label class="block text-purple-300 text-sm font-medium mb-2">
                                Screenshot Proof <span class="text-red-400">*</span>
                            </label>
                            <div
                                @click="triggerFileInput"
                                @drop="handleDrop"
                                @dragover.prevent
                                @dragenter.prevent
                                class="border-2 border-dashed border-purple-500/30 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 hover:border-purple-500 hover:bg-purple-500/10"
                                :class="form.screenshot ? 'border-green-500/50 bg-green-500/10' : ''"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileSelect"
                                    class="hidden"
                                />
                                
                                <div v-if="!form.screenshot">
                                    <Upload :size="48" class="mx-auto text-purple-400 mb-3" />
                                    <p class="text-white font-medium">Upload WhatsApp Screenshot</p>
                                    <p class="text-purple-400 text-sm mt-1">PNG, JPG up to 5MB</p>
                                    <p class="text-purple-300 text-xs mt-2">or drag and drop</p>
                                </div>
                                
                                <div v-else class="flex items-center justify-center space-x-4">
                                    <img :src="form.screenshotPreview" class="w-16 h-16 object-cover rounded-lg border border-purple-500/30" />
                                    <div class="text-left">
                                        <p class="text-white font-medium">Screenshot Selected</p>
                                        <p class="text-green-400 text-sm">{{ form.screenshot.name }}</p>
                                        <button
                                            type="button"
                                            @click.stop="removeScreenshot"
                                            class="text-red-400 hover:text-red-300 text-sm mt-1 transition-colors"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Earnings Preview -->
                        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 border border-purple-500/30">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-purple-300 text-sm">Estimated Earnings</p>
                                    <p class="text-2xl font-bold text-white">
                                        KES {{ calculateEarnings().toLocaleString() }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-purple-300 text-sm">Rate: KES 100/view</p>
                                    <p class="text-purple-400 text-xs">{{ form.views_count || 0 }} views</p>
                                    <p class="text-purple-300 text-xs mt-1">{{ form.view_type || 'Select view type' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="!formValid || processing"
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-medium py-4 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/50 flex items-center justify-center space-x-2"
                        >
                            <Loader2 v-if="processing" :size="20" class="animate-spin" />
                            <span>{{ processing ? 'Submitting...' : 'Submit WhatsApp Views' }}</span>
                        </button>
                    </form>
                </div>

                <!-- Stats & Information -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Your WhatsApp View Stats</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Total Submitted</span>
                                <span class="text-white font-bold">{{ stats.total_views.toLocaleString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Total Earned</span>
                                <span class="text-white font-bold">KES {{ stats.total_earned.toLocaleString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Pending</span>
                                <span class="text-yellow-400 font-bold">{{ stats.pending_views.toLocaleString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-green-200">Approved</span>
                                <span class="text-green-400 font-bold">{{ stats.approved_views.toLocaleString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <h3 class="text-lg font-bold text-white mb-4">Submission Guidelines</h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Clear screenshot showing WhatsApp view count</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">WhatsApp number must be active and verified</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Maximum 10,000 views per submission</span>
                            </li>
                            <li class="flex items-start space-x-2">
                                <CheckCircle :size="16" class="text-green-400 mt-0.5 flex-shrink-0" />
                                <span class="text-blue-100">Processing time: 24-48 hours</span>
                            </li>
                        </ul>
                    </div>

                    
                </div>
            </div>

            <!-- Recent Submissions Table -->
            <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-white">Recent WhatsApp Submissions</h3>
                    <button
                        @click="refreshSubmissions"
                        class="bg-purple-600 hover:bg-purple-500 px-4 py-2 rounded-xl text-white text-sm transition-all duration-200 flex items-center space-x-2"
                    >
                        <RefreshCw :size="16" />
                        <span>Refresh</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-purple-500/30">
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">ID</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Views</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Earned</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Phone</th>
                               
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Date</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Status</th>
                                <th class="text-left py-3 px-4 text-purple-300 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="submission in submissions" 
                                :key="submission.id"
                                class="border-b border-purple-500/10 hover:bg-purple-500/5 transition-colors"
                            >
                                <td class="py-3 px-4 text-white font-mono">#{{ submission.id.toString().padStart(6, '0') }}</td>
                                <td class="py-3 px-4 text-white">{{ submission.views_count.toLocaleString() }}</td>
                                <td class="py-3 px-4">
                                    <span class="text-green-400 font-bold">KES {{ submission.earned_amount.toLocaleString() }}</span>
                                </td>
                                <td class="py-3 px-4 text-purple-300">{{ submission.whatsapp_number }}</td>
                               
                                <td class="py-3 px-4 text-purple-300 text-sm">{{ formatDate(submission.created_at) }}</td>
                                <td class="py-3 px-4">
                                    <span 
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusClasses(submission.status)"
                                    >
                                        <span class="w-2 h-2 rounded-full mr-1.5" :class="getStatusDotClass(submission.status)"></span>
                                        {{ submission.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="viewSubmission(submission)"
                                        class="text-purple-400 hover:text-purple-300 transition-colors p-1"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Empty State -->
                    <div v-if="submissions.length === 0" class="text-center py-8">
                        <EyeOff :size="48" class="mx-auto text-purple-400 mb-4" />
                        <p class="text-purple-300 text-lg">No WhatsApp submissions yet</p>
                        <p class="text-purple-400">Start by submitting your first WhatsApp views above</p>
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
    Eye, Phone, Upload, CheckCircle, RefreshCw, Loader2, EyeOff,
    MessageCircle, Video, Image
} from 'lucide-vue-next';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import toastr from '@/plugins/toastr'; // Import toastr

const props = defineProps({
    submissions: Array,
    stats: Object
});

// Reactive data
const form = ref({
    whatsapp_number: '',
    views_count: '',
    screenshot: null,
    screenshotPreview: null
});

const processing = ref(false);
const fileInput = ref(null);

// Computed properties
const formValid = computed(() => {
    return form.value.whatsapp_number && 
           form.value.views_count > 0 && 
           form.value.screenshot;
});



const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file && file.type.startsWith('image/')) {
        form.value.screenshot = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            form.value.screenshotPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    const file = event.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
        form.value.screenshot = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            form.value.screenshotPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeScreenshot = () => {
    form.value.screenshot = null;
    form.value.screenshotPreview = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const calculateEarnings = () => {
    if (!form.value.views_count) return 0;
    return form.value.views_count * 100;
};

const submitViews = async () => {
    if (!formValid.value || processing.value) return;

    processing.value = true;
    
    try {
        const formData = new FormData();
        formData.append('whatsapp_number', form.value.whatsapp_number);
        formData.append('views_count', form.value.views_count);
        formData.append('screenshot', form.value.screenshot);

        // Get CSRF token from meta tag (if using Blade)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // OR get it from the cookies (alternative method)
        // const csrfToken = getCookie('XSRF-TOKEN');

        const response = await fetch('/cash-flow/submit-views', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                // Don't include Content-Type for FormData - browser sets it automatically with boundary
            },
            credentials: 'include', // Important for cookies/Session
            body: formData
        });

        const result = await response.json();

        console.log('Submission response:', result);

        if (response.ok) {
            // Reset form
            resetForm();
            
            // Show success message
            toastr.success(result.message);
            
            // Refresh data
            setTimeout(() => {
                router.reload({ only: ['submissions', 'stats'] });
            }, 1500);
            
        } else {
            // Handle validation errors
            if (result.errors) {
                const errorMessages = Object.values(result.errors).flat().join('\n');
                toastr.error(errorMessages);
            } else {
                toastr.error(result.message || 'Failed to submit WhatsApp views. Please try again.');
            }
        }
        
    } catch (error) {
        console.error('Error submitting views:', error);
        toastr.error('Network error. Please check your connection and try again.');
    } finally {
        processing.value = false;
    }
};

// Helper function to get cookie (if needed)
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

const resetForm = () => {
    form.value = {
        whatsapp_number: '',
        views_count: '',
        screenshot: null,
        screenshotPreview: null
    };
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const refreshSubmissions = () => {
    router.reload({ only: ['submissions', 'stats'] });
};

const viewSubmission = (submission) => {
    router.visit(`/cash-flow/submit-views/${submission.id}`);
};



const getStatusClasses = (status) => {
    const classes = {
        pending: 'bg-yellow-500/20 text-yellow-300',
        approved: 'bg-green-500/20 text-green-300',
        rejected: 'bg-red-500/20 text-red-300',
        processing: 'bg-blue-500/20 text-blue-300'
    };
    return classes[status.toLowerCase()] || 'bg-gray-500/20 text-gray-300';
};

const getStatusDotClass = (status) => {
    const classes = {
        pending: 'bg-yellow-400',
        approved: 'bg-green-400',
        rejected: 'bg-red-400',
        processing: 'bg-blue-400'
    };
    return classes[status.toLowerCase()] || 'bg-gray-400';
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<style scoped>
table {
    border-collapse: separate;
    border-spacing: 0;
}

th, td {
    border-bottom: 1px solid rgba(168, 85, 247, 0.1);
}

tr:last-child td {
    border-bottom: none;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>