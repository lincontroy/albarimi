<template>
    <GuestLayout>
        <Head title="Log in" />
        
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8">
            <!-- Clean Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/5 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-500/5 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-md w-full bg-slate-800/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-8 shadow-2xl shadow-purple-500/20">
                <!-- Header - Clean and Simple -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Welcome Back
                    </h2>
                    <p class="mt-2 text-purple-300 text-sm">
                        Sign in to access your account
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-purple-300 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all placeholder-purple-500/50"
                                placeholder="you@example.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-2 text-red-400 text-sm">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-purple-300">
                                Password
                            </label>
                            <Link 
                                v-if="canResetPassword"
                                href="/forgot-password"
                                class="text-sm text-purple-400 hover:text-purple-300 transition-colors"
                            >
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                autocomplete="current-password"
                                class="w-full bg-black/30 border border-purple-500/30 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-purple-400 focus:ring-2 focus:ring-purple-500/50 transition-all placeholder-purple-500/50"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-purple-400 hover:text-purple-300 transition-colors"
                            >
                                <Eye :size="18" v-if="showPassword" />
                                <EyeOff :size="18" v-else />
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-red-400 text-sm">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-purple-500/30 rounded bg-black/30"
                        />
                        <label for="remember" class="ml-2 block text-sm text-purple-300">
                            Remember me
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-purple-500/50"
                        >
                            <Loader2 v-if="form.processing" :size="20" class="animate-spin mr-2" />
                            <span>{{ form.processing ? 'Signing in...' : 'Sign In' }}</span>
                        </button>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="hasErrors" class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                        <div class="flex items-center text-red-400">
                            <AlertCircle :size="18" class="mr-2 flex-shrink-0" />
                            <p class="text-sm">
                                <span v-if="errors.email">{{ errors.email }}</span>
                                <span v-else-if="errors.password">{{ errors.password }}</span>
                                <span v-else>Invalid login credentials</span>
                            </p>
                        </div>
                    </div>

                    <!-- Registration Link -->
                    <div class="text-center pt-4 border-t border-purple-500/20">
                        <p class="text-purple-300 text-sm">
                            Don't have an account?
                            <Link 
                                href="/register" 
                                class="text-purple-400 hover:text-purple-300 font-semibold ml-1"
                            >
                                Sign up
                            </Link>
                        </p>
                    </div>

                    <!-- Demo Credentials (for development) -->
                
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {
    Eye, EyeOff, AlertCircle, Loader2, Info
} from 'lucide-vue-next';

const props = defineProps({
    canResetPassword: Boolean,
    status: String,
    errors: Object,
});

const showPassword = ref(false);
const isDevelopment = ref(process.env.NODE_ENV === 'development');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.type = showPassword.value ? 'text' : 'password';
    }
};

const hasErrors = computed(() => {
    return Object.keys(props.errors).length > 0;
});
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