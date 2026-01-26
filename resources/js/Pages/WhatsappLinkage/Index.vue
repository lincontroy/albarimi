<template>
    <DashboardLayout title="WhatsApp Linkage">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-green-500/20">
            <MessageCircle :size="16" class="text-green-400" />
            <span>WhatsApp Linkage</span>
            <span class="text-green-400">›</span>
            <span class="text-green-300">Dashboard</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-2xl lg:text-3xl font-bold text-white mb-2">Link Your WhatsApp</h1>
                    <p class="text-green-300">Connect your WhatsApp number for seamless communication</p>
                </div>

                <!-- Current Status -->
                <div v-if="linkage" class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">Current Status</h2>
                            <span 
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                :style="`background-color: ${linkage.status_badge.bgColor}; color: ${linkage.status_badge.color}`"
                            >
                                {{ linkage.status_badge.text }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Phone Info -->
                            <div>
                                <h3 class="font-bold text-white mb-3">Linked WhatsApp Number</h3>
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-12 h-12 bg-green-600/20 rounded-full flex items-center justify-center">
                                        <MessageCircle :size="24" class="text-green-400" />
                                    </div>
                                    <div>
                                        <div class="text-2xl font-bold text-white">{{ linkage.phone_number }}</div>
                                        <div class="text-sm text-green-300">International: {{ linkage.formatted_phone }}</div>
                                    </div>
                                </div>
                                
                                <div v-if="linkage.verified_at" class="p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <CheckCircle :size="16" class="text-green-400" />
                                        <span class="text-green-300">Verified on {{ linkage.verified_at }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div>
                                <h3 class="font-bold text-white mb-3">Quick Actions</h3>
                                <div class="space-y-3">
                                    <a 
                                        :href="linkage.whatsapp_chat_link"
                                        target="_blank"
                                        class="flex items-center justify-between p-3 bg-green-600/20 border border-green-500/30 rounded-lg hover:bg-green-500/20 transition-all duration-200 group"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <MessageCircle :size="20" class="text-green-400" />
                                            <span class="text-white group-hover:text-green-300">Open WhatsApp Chat</span>
                                        </div>
                                        <ExternalLink :size="16" class="text-green-400 group-hover:text-green-300" />
                                    </a>
                                    
                                    <button
                                        @click="unlinkWhatsApp"
                                        class="w-full flex items-center justify-between p-3 bg-red-600/20 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-all duration-200 group"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <Unlink :size="20" class="text-red-400" />
                                            <span class="text-white group-hover:text-red-300">Unlink WhatsApp</span>
                                        </div>
                                        <X :size="16" class="text-red-400 group-hover:text-red-300" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Section -->
                        <div v-if="linkage.status === 'pending'" class="mt-6 pt-6 border-t border-blue-500/20">
                            <h3 class="font-bold text-white mb-3">Verify Your Number</h3>
                            <p class="text-blue-300 mb-4">
                                We sent a 6-digit verification code to your WhatsApp number. 
                                Enter it below to complete verification.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="relative">
                                        <input
                                            v-model="verificationCode"
                                            type="text"
                                            maxlength="6"
                                            placeholder="Enter 6-digit code"
                                            class="w-full bg-black/30 border border-blue-500/30 text-white rounded-xl px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:border-blue-400"
                                        />
                                    </div>
                                    <div class="text-center mt-2">
                                        <button
                                            @click="verifyCode"
                                            :disabled="verificationCode.length !== 6 || verifying"
                                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-2 px-6 rounded-lg transition-all duration-200"
                                        >
                                            <span v-if="verifying">Verifying...</span>
                                            <span v-else>Verify Code</span>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-center justify-center">
                                    <button
                                        @click="resendVerification"
                                        :disabled="cooldown > 0 || !linkage.can_send_verification"
                                        class="text-green-400 hover:text-green-300 disabled:text-gray-500 disabled:cursor-not-allowed mb-2"
                                    >
                                        <span v-if="cooldown > 0">Resend in {{ formatTime(cooldown) }}</span>
                                        <span v-else>Resend Verification Code</span>
                                    </button>
                                    <div v-if="linkage.verification_attempts > 0" class="text-sm text-yellow-400">
                                        {{ linkage.verification_attempts }} verification attempts
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Link New WhatsApp -->
                <div v-else class="mb-8">
                    <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-green-500/30 rounded-2xl p-6 shadow-2xl shadow-green-500/20">
                        <h2 class="text-xl font-bold text-white mb-6">Link Your WhatsApp Number</h2>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Form -->
                            <div>
                                <form @submit.prevent="linkWhatsApp">
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-green-300 mb-2">
                                            WhatsApp Phone Number
                                        </label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-green-400">+254</span>
                                            <input
                                                v-model="phoneNumber"
                                                type="tel"
                                                required
                                                class="w-full bg-black/30 border border-green-500/30 text-white rounded-xl pl-20 pr-4 py-3 focus:outline-none focus:border-green-400"
                                                placeholder="7XXXXXXXX"
                                                pattern="[0-9]{9}"
                                                title="Enter 9 digits after 254"
                                            />
                                        </div>
                                        <p class="text-xs text-green-400 mt-1">Enter your phone number without the country code</p>
                                    </div>
                                    
                                    <button
                                        type="submit"
                                        :disabled="linking || !phoneNumber"
                                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 disabled:from-slate-600 disabled:to-slate-600 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2"
                                    >
                                        <Loader2 v-if="linking" class="animate-spin" :size="20" />
                                        <LinkIcon v-else :size="20" />
                                        <span>{{ linking ? 'Linking...' : 'Link WhatsApp' }}</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Benefits -->
                            <div>
                                <h3 class="font-bold text-white mb-3">Benefits of Linking</h3>
                                <ul class="space-y-2">
                                    <li class="flex items-start space-x-2 text-sm text-green-200">
                                        <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                        <span>Receive instant notifications for job applications</span>
                                    </li>
                                    <li class="flex items-start space-x-2 text-sm text-green-200">
                                        <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                        <span>Get loan approval updates directly on WhatsApp</span>
                                    </li>
                                    <li class="flex items-start space-x-2 text-sm text-green-200">
                                        <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                        <span>Quick customer support via WhatsApp</span>
                                    </li>
                                    <li class="flex items-start space-x-2 text-sm text-green-200">
                                        <CheckCircle :size="14" class="text-green-400 flex-shrink-0 mt-0.5" />
                                        <span>Secure verification for transactions</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-yellow-500/30 rounded-2xl p-6 shadow-2xl shadow-yellow-500/20">
                    <h3 class="text-lg font-bold text-white mb-4">How It Works</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-600/20 rounded-full mb-3">
                                <span class="text-yellow-400 font-bold">1</span>
                            </div>
                            <h4 class="font-bold text-white mb-2">Enter Your Number</h4>
                            <p class="text-sm text-yellow-300">Provide your WhatsApp phone number</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-600/20 rounded-full mb-3">
                                <span class="text-yellow-400 font-bold">2</span>
                            </div>
                            <h4 class="font-bold text-white mb-2">Receive Code</h4>
                            <p class="text-sm text-yellow-300">Get 6-digit verification code on WhatsApp</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 bg-yellow-600/20 rounded-full mb-3">
                                <span class="text-yellow-400 font-bold">3</span>
                            </div>
                            <h4 class="font-bold text-white mb-2">Verify & Enjoy</h4>
                            <p class="text-sm text-yellow-300">Enter code to complete linking</p>
                        </div>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="mt-8 bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <div class="flex items-start space-x-3">
                        <Shield :size="24" class="text-blue-400 flex-shrink-0" />
                        <div>
                            <h3 class="font-bold text-white mb-2">Security & Privacy</h3>
                            <p class="text-blue-300">
                                Your WhatsApp number is secure and will only be used for platform notifications 
                                and verification purposes. We never share your contact information with third parties.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    MessageCircle, CheckCircle, ExternalLink, Unlink, X,
    Link as LinkIcon, Loader2, Shield
} from 'lucide-vue-next';

const props = defineProps({
    linkage: {
        type: Object,
        default: null
    },
    cooldown: {
        type: Number,
        default: 0
    },
    user: {
        type: Object,
        default: () => ({})
    }
});

const phoneNumber = ref('');
const verificationCode = ref('');
const linking = ref(false);
const verifying = ref(false);
const resending = ref(false);
const countdown = ref(props.cooldown);

// Start countdown if there's a cooldown
onMounted(() => {
    if (countdown.value > 0) {
        startCountdown();
    }
});

// Watch for cooldown changes
watch(() => props.cooldown, (newCooldown) => {
    countdown.value = newCooldown;
    if (newCooldown > 0) {
        startCountdown();
    }
});

const startCountdown = () => {
    const interval = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(interval);
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const linkWhatsApp = async () => {
    if (!phoneNumber.value) {
        alert('Please enter your WhatsApp phone number');
        return;
    }

    // Format phone number
    let formattedPhone = phoneNumber.value.replace(/\D/g, '');
    if (formattedPhone.startsWith('0')) {
        formattedPhone = formattedPhone.substring(1);
    }
    
    if (formattedPhone.length !== 9) {
        alert('Please enter a valid 9-digit phone number (after 254)');
        return;
    }

    linking.value = true;

    try {
        const response = await fetch('/whatsapp-linkage/link', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                phone_number: formattedPhone
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('WhatsApp number linked successfully! Verification code sent to your WhatsApp.');
            router.reload({ preserveScroll: true });
        } else {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                alert(firstError);
            } else {
                alert(data.message || 'Failed to link WhatsApp number');
            }
        }
    } catch (error) {
        console.error('Link error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        linking.value = false;
    }
};

const verifyCode = async () => {
    if (verificationCode.value.length !== 6) {
        alert('Please enter a 6-digit verification code');
        return;
    }

    verifying.value = true;

    try {
        const response = await fetch('/whatsapp-linkage/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                verification_code: verificationCode.value
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('WhatsApp number verified successfully!');
            router.reload({ preserveScroll: true });
        } else {
            alert(data.message || 'Invalid verification code');
            verificationCode.value = '';
        }
    } catch (error) {
        console.error('Verify error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        verifying.value = false;
    }
};

const resendVerification = async () => {
    if (countdown.value > 0) {
        alert(`Please wait ${formatTime(countdown.value)} before requesting a new code`);
        return;
    }

    resending.value = true;

    try {
        const response = await fetch('/whatsapp-linkage/resend-verification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('New verification code sent!');
            countdown.value = data.cooldown || 120;
            startCountdown();
        } else {
            alert(data.message || 'Failed to send verification code');
            if (data.cooldown) {
                countdown.value = data.cooldown;
                startCountdown();
            }
        }
    } catch (error) {
        console.error('Resend error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        resending.value = false;
    }
};

const unlinkWhatsApp = async () => {
    if (!confirm('Are you sure you want to unlink your WhatsApp number? You will need to verify again to re-link.')) {
        return;
    }

    try {
        const response = await fetch('/whatsapp-linkage/unlink', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
            body: JSON.stringify({
                confirmation: true
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('WhatsApp number unlinked successfully!');
            router.reload({ preserveScroll: true });
        } else {
            alert(data.message || 'Failed to unlink WhatsApp number');
        }
    } catch (error) {
        console.error('Unlink error:', error);
        alert('An error occurred. Please try again.');
    }
};
</script>