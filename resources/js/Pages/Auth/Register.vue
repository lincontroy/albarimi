<template>
    <GuestLayout>
        <Head title="Register" />
        
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8">
            <div class="relative max-w-md w-full bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-8 shadow-2xl shadow-purple-500/20">
                <!-- Referral Banner -->
                <div v-if="referrer" class="bg-gradient-to-r from-green-600/20 to-emerald-600/20 border border-green-500/30 rounded-xl p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <UserCheck :size="20" class="text-green-400" />
                        <div>
                            <p class="text-green-300 text-sm">Joining through referral</p>
                            <p class="text-white font-semibold text-sm">
                                Referred by: {{ referrer.name }}
                                <span v-if="referrer.is_agent" class="ml-2 bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full text-xs">
                                    Agent
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Create Account
                    </h2>
                    <p class="mt-2 text-purple-300 text-sm">
                        Join our platform
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-purple-300 mb-2">
                                Full Name
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all"
                                placeholder="John Doe"
                            />
                            <p v-if="form.errors.name" class="mt-2 text-red-400 text-sm">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-purple-300 mb-2">
                                Email Address
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all"
                                placeholder="john@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-2 text-red-400 text-sm">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Phone (Optional) -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-purple-300 mb-2">
                                Phone Number (Optional)
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all"
                                placeholder="+254 700 000000"
                            />
                            <p v-if="form.errors.phone" class="mt-2 text-red-400 text-sm">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-purple-300 mb-2">
                                Password
                            </label>
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all"
                                placeholder="••••••••"
                            />
                            <p v-if="form.errors.password" class="mt-2 text-red-400 text-sm">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-purple-300 mb-2">
                                Confirm Password
                            </label>
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all"
                                placeholder="••••••••"
                            />
                        </div>

                        <!-- Hidden Referral Code - FIXED -->
                        <input
                            v-if="referral_code"
                            type="hidden"
                            v-model="form.referral_code"
                        />
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-center">
                        <input
                            id="terms"
                            v-model="form.terms"
                            type="checkbox"
                            required
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-purple-500/30 rounded bg-black/30"
                        />
                        <label for="terms" class="ml-2 block text-sm text-purple-300">
                            I agree to the Terms & Privacy Policy
                        </label>
                    </div>
                    <p v-if="form.errors.terms" class="text-red-400 text-sm">
                        {{ form.errors.terms }}
                    </p>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-purple-500/50"
                        >
                            <Loader2 v-if="form.processing" :size="20" class="animate-spin mr-2" />
                            <span v-if="referrer">Join Team</span>
                            <span v-else>Create Account</span>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t border-purple-500/20">
                        <p class="text-purple-300 text-sm">
                            Already have an account?
                            <Link href="/login" class="text-purple-400 hover:text-purple-300 font-semibold ml-1">
                                Sign in
                            </Link>
                        </p>
                    </div>

                    <!-- Referral Info -->
                    <div v-if="!referral_code" class="mt-4 bg-black/20 border border-purple-500/20 rounded-xl p-3">
                        <div class="flex items-center space-x-2 text-purple-300 text-xs">
                            <Info :size="14" />
                            <p>Have a referral code? Add <code class="bg-black/30 px-1.5 py-0.5 rounded">?ref=CODE</code> to URL</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Loader2, UserCheck, Info } from 'lucide-vue-next';

const props = defineProps({
    referral_code: String,
    referrer: Object,
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    referral_code: props.referral_code || '', // Initialize with the prop value
    terms: false,
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Input focus glow */
input:focus {
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Button hover effect */
button:hover:not(:disabled) {
    transform: translateY(-1px);
}
</style>