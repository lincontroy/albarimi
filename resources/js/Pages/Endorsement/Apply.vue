<template>
    <DashboardLayout title="Apply for Endorsement">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
            <Award :size="16" class="text-purple-400" />
            <span>Endorsements</span>
            <span class="text-purple-400">›</span>
            <span class="text-purple-300">Apply</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Apply for Endorsement</h1>
                    <p class="text-purple-300">Complete your application to get verified and endorsed</p>
                </div>

                <!-- Package Fee Alert -->
                <div v-if="!hasEnoughBalance" class="mb-6 bg-gradient-to-br from-red-900/30 to-red-800/30 backdrop-blur-xl border border-red-500/30 rounded-2xl p-6 shadow-2xl shadow-red-500/20">
                    <div class="flex items-center space-x-3 mb-3">
                        <AlertCircle :size="24" class="text-red-400" />
                        <h3 class="text-lg font-bold text-white">Insufficient Balance</h3>
                    </div>
                    <p class="text-red-300 mb-3">
                        You need at least KES {{ formatCurrency(packageFee) }} for the endorsement package. 
                        Your current balance is KES {{ formatCurrency(userBalance) }}.
                    </p>
                    <Link 
                        href="/wallet/deposit"
                        class="bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-200 inline-flex items-center space-x-2"
                    >
                        <PlusCircle :size="16" />
                        <span>Deposit Funds</span>
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-6 shadow-2xl shadow-purple-500/20">
                            <h3 class="text-xl font-bold text-white mb-6">Endorsement Application</h3>
                            
                            <form @submit.prevent="submitApplication">
                                <!-- Endorsement Type -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <Tag :size="16" class="inline mr-2" />
                                        Endorsement Type
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <button
                                            v-for="type in types"
                                            :key="type.id"
                                            type="button"
                                            @click="form.type = type.id"
                                            :class="form.type === type.id ? 'bg-purple-600 text-white border-purple-500' : 'bg-black/30 text-purple-300 border-purple-500/30'"
                                            class="border rounded-lg p-4 text-left transition-all duration-200"
                                        >
                                            <h4 class="font-bold mb-1">{{ type.name }}</h4>
                                            <p class="text-xs">{{ type.description }}</p>
                                        </button>
                                    </div>
                                </div>

                                <!-- Title -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <Edit :size="16" class="inline mr-2" />
                                        Endorsement Title
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        :disabled="!hasEnoughBalance"
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                        placeholder="e.g., Certified Web Developer, Professional Photographer"
                                    />
                                    <p class="text-xs text-purple-400 mt-1">Be clear and descriptive about what you want endorsed</p>
                                </div>

                                <!-- Description -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <FileText :size="16" class="inline mr-2" />
                                        Detailed Description
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="5"
                                        required
                                        :disabled="!hasEnoughBalance"
                                        class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                        placeholder="Describe your skills, experience, or what you want endorsed in detail..."
                                    ></textarea>
                                    <div class="flex justify-between mt-1">
                                        <span class="text-xs text-purple-400">Minimum 100 characters</span>
                                        <span class="text-xs" :class="form.description.length >= 100 ? 'text-green-400' : 'text-red-400'">
                                            {{ form.description.length }}/2000
                                        </span>
                                    </div>
                                </div>

                                <!-- Skills -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <Award :size="16" class="inline mr-2" />
                                        Skills & Expertise (Optional)
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <span 
                                            v-for="(skill, index) in form.skills"
                                            :key="index"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-600/20 text-purple-300"
                                        >
                                            {{ skill }}
                                            <button 
                                                type="button"
                                                @click="removeSkill(index)"
                                                class="ml-2 text-purple-400 hover:text-purple-300"
                                            >
                                                <X :size="12" />
                                            </button>
                                        </span>
                                    </div>
                                    <div class="flex">
                                        <input
                                            v-model="newSkill"
                                            type="text"
                                            :disabled="!hasEnoughBalance"
                                            @keydown.enter.prevent="addSkill"
                                            class="flex-1 bg-black/30 border border-purple-500/30 text-white rounded-l-xl px-4 py-3 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                            placeholder="Add a skill"
                                        />
                                        <button
                                            type="button"
                                            @click="addSkill"
                                            :disabled="!hasEnoughBalance"
                                            class="bg-purple-600 hover:bg-purple-500 text-white px-4 py-3 rounded-r-xl transition-all duration-200 disabled:opacity-50"
                                        >
                                            Add
                                        </button>
                                    </div>
                                </div>

                                <!-- Links -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-purple-300 mb-2">
                                        <LinkIcon :size="16" class="inline mr-2" />
                                        Supporting Links (Optional)
                                    </label>
                                    <div class="space-y-2 mb-2">
                                        <div 
                                            v-for="(link, index) in form.links"
                                            :key="index"
                                            class="flex items-center"
                                        >
                                            <input
                                                v-model="form.links[index]"
                                                type="url"
                                                :disabled="!hasEnoughBalance"
                                                class="flex-1 bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-purple-400 disabled:opacity-50"
                                                placeholder="https://example.com"
                                            />
                                            <button 
                                                type="button"
                                                @click="removeLink(index)"
                                                class="ml-2 text-red-400 hover:text-red-300"
                                            >
                                                <X :size="16" />
                                            </button>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addLink"
                                        :disabled="!hasEnoughBalance"
                                        class="text-purple-400 hover:text-purple-300 text-sm flex items-center space-x-1"
                                    >
                                        <Plus :size="14" />
                                        <span>Add another link</span>
                                    </button>
                                </div>

                                <!-- Terms and Conditions -->
                                <div class="mb-6">
                                    <div class="flex items-start space-x-2">
                                        <input
                                            v-model="form.agreeTerms"
                                            type="checkbox"
                                            id="terms"
                                            :disabled="!hasEnoughBalance"
                                            required
                                            class="mt-1"
                                        />
                                        <label for="terms" class="text-sm text-purple-300">
                                            I agree to the endorsement terms and conditions. I understand that a non-refundable package fee of KES {{ formatCurrency(packageFee) }} will be deducted from my balance for 12 months of endorsement.
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex space-x-3">
                                    <button
                                        type="submit"
                                        :disabled="!hasEnoughBalance || loading || form.description.length < 100"
                                        class="flex-1 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="loading" class="animate-spin" :size="20" />
                                        <FilePlus v-else :size="20" />
                                        <span>{{ loading ? 'Processing...' : 'Submit Application' }}</span>
                                    </button>
                                    
                                    <Link
                                        href="/endorsement"
                                        class="flex-1 border border-purple-500/30 text-purple-400 hover:bg-purple-500/20 py-3 px-6 rounded-lg text-center transition-all duration-200"
                                    >
                                        Cancel
                                    </Link>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="lg:col-span-1">
                        <!-- Package Summary -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">Package Summary</h3>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Endorsement Type</span>
                                    <span class="font-bold text-white">
                                        {{ selectedType?.name || 'Not selected' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Package Fee</span>
                                    <span class="font-bold text-purple-400">KES {{ formatCurrency(packageFee) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-blue-300">Validity Period</span>
                                    <span class="font-bold text-white">12 Months</span>
                                </div>
                                
                                <div class="pt-3 border-t border-blue-500/20">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-blue-300">Your Balance</span>
                                        <span class="font-bold text-white">KES {{ formatCurrency(userBalance) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-blue-300">After Payment</span>
                                        <span class="font-bold text-green-400">KES {{ formatCurrency(userBalance - packageFee) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Benefits -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20 mb-6">
                            <h3 class="text-lg font-bold text-white mb-4">What You Get</h3>
                            
                            <ul class="space-y-2">
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Verified badge on your profile</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Priority in search results</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Increased credibility</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>12 months validity</span>
                                </li>
                                <li class="flex items-start space-x-2 text-sm text-blue-200">
                                    <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                    <span>Professional review process</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Processing Time -->
                        <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                            <h3 class="text-lg font-bold text-white mb-4">Processing Time</h3>
                            
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <Clock :size="16" class="text-yellow-400" />
                                    <span class="text-sm text-yellow-300">Review: 3-5 business days</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <AlertCircle :size="16" class="text-yellow-400" />
                                    <span class="text-sm text-yellow-300">Fee is non-refundable</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Info :size="16" class="text-yellow-400" />
                                    <span class="text-sm text-yellow-300">You'll be notified via email</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Award, AlertCircle, PlusCircle, Tag, Edit,
    FileText, FilePlus, Loader2, CheckCircle, Clock,
    Info, X, Plus, Link as LinkIcon
} from 'lucide-vue-next';

const props = defineProps({
    packageFee: {
        type: Number,
        default: 3500
    },
    hasEnoughBalance: {
        type: Boolean,
        default: false
    },
    userBalance: {
        type: [Number, String],
        default: 0
    },
    types: {
        type: Array,
        default: () => []
    }
});

const loading = ref(false);
const newSkill = ref('');

const form = reactive({
    type: 'professional',
    title: '',
    description: '',
    skills: [],
    links: [],
    agreeTerms: false,
});

const selectedType = computed(() => {
    return props.types.find(t => t.id === form.type) || props.types[0];
});

// Utility functions
const formatCurrency = (amount) => {
    const num = parseFloat(amount) || 0;
    return num.toLocaleString('en-KE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
};

const addSkill = () => {
    if (newSkill.value.trim() && !form.skills.includes(newSkill.value.trim())) {
        form.skills.push(newSkill.value.trim());
        newSkill.value = '';
    }
};

const removeSkill = (index) => {
    form.skills.splice(index, 1);
};

const addLink = () => {
    form.links.push('');
};

const removeLink = (index) => {
    form.links.splice(index, 1);
};

const submitApplication = async () => {
    if (!props.hasEnoughBalance) {
        alert('Insufficient balance for endorsement package');
        return;
    }

    if (!form.type) {
        alert('Please select an endorsement type');
        return;
    }

    if (!form.title.trim()) {
        alert('Please enter an endorsement title');
        return;
    }

    if (form.description.length < 100) {
        alert('Description must be at least 100 characters');
        return;
    }

    if (!form.agreeTerms) {
        alert('You must agree to the terms and conditions');
        return;
    }

    const confirmMessage = `Are you sure you want to apply for ${selectedType.value.name} endorsement? KES ${formatCurrency(props.packageFee)} will be deducted from your balance.`;

    if (!confirm(confirmMessage)) {
        return;
    }

    loading.value = true;

    try {
        await router.post('/endorsement/apply', form, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by controller redirect
            },
            onError: (errors) => {
                console.error('Endorsement application error:', errors);
                alert('Failed to submit application. Please check the form and try again.');
                loading.value = false;
            }
        });
    } catch (error) {
        console.error('Endorsement application error:', error);
        alert('An unexpected error occurred. Please try again.');
        loading.value = false;
    }
};
</script>