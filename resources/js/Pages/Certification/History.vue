<template>
    <DashboardLayout title="Certification - History">
        <!-- Breadcrumb -->
        <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-blue-500/20">
            <History :size="16" class="text-blue-400" />
            <span>Certification</span>
            <span class="text-blue-400">›</span>
            <span class="text-blue-300">Purchase History</span>
        </div>
        
        <!-- Main Content -->
        <div class="p-4 lg:p-8">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-2xl font-bold text-white">Certification Purchase History</h1>
                    <Link 
                        href="/certification"
                        class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-2 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50"
                    >
                        Back to Packages
                    </Link>
                </div>

                <!-- History Table -->
                <div class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-6 shadow-2xl shadow-blue-500/20">
                    <!-- Desktop Table -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-blue-500/20">
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Package</th>
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Amount</th>
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Purchase Date</th>
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Status</th>
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Expires</th>
                                    <th class="text-left py-3 px-4 text-blue-300 text-sm font-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="purchase in purchases.data" 
                                    :key="purchase.id"
                                    class="border-b border-blue-500/10 hover:bg-blue-500/5 transition-colors"
                                >
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-3">
                                            <component :is="getPackageIcon(purchase.package_type)" :size="20" :class="getIconColor(purchase.package_type)" />
                                            <div>
                                                <p class="text-white font-semibold">{{ purchase.package_name }}</p>
                                                <p class="text-blue-300 text-xs">{{ formatPackageType(purchase.package_type) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white font-semibold">KES {{ purchase.amount.toLocaleString() }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ purchase.created_at }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span 
                                            :class="getStatusColor(purchase.status)"
                                            class="px-3 py-1 rounded-full text-xs font-semibold"
                                        >
                                            {{ purchase.status }}
                                        </span>
                                        <p v-if="purchase.is_active" class="text-green-400 text-xs mt-1">
                                            {{ purchase.days_remaining }} days remaining
                                        </p>
                                        <p v-if="purchase.is_expired" class="text-red-400 text-xs mt-1">
                                            Expired
                                        </p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <p class="text-white">{{ purchase.expires_at || 'N/A' }}</p>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center space-x-2">
                                            <Link 
                                                :href="'/certification/' + purchase.id"
                                                class="text-blue-400 hover:text-blue-300 transition-colors p-2 hover:bg-blue-500/10 rounded-lg"
                                                title="View Details"
                                            >
                                                <Eye :size="16" />
                                            </Link>
                                            <button 
                                                v-if="purchase.status === 'pending'"
                                                @click="activatePackage(purchase.id)"
                                                class="text-green-400 hover:text-green-300 transition-colors p-2 hover:bg-green-500/10 rounded-lg"
                                                title="Activate Package"
                                            >
                                                <Play :size="16" />
                                            </button>
                                            <button 
                                                v-if="purchase.codes.certification || purchase.codes.access || purchase.codes.verification"
                                                @click="copyPackageCode(purchase)"
                                                class="text-purple-400 hover:text-purple-300 transition-colors p-2 hover:bg-purple-500/10 rounded-lg"
                                                title="Copy Code"
                                            >
                                                <Copy :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-4">
                        <div 
                            v-for="purchase in purchases.data" 
                            :key="purchase.id"
                            :class="getPackageColor(purchase.package_type)"
                            class="border rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <component :is="getPackageIcon(purchase.package_type)" :size="20" />
                                    <div>
                                        <p class="text-white font-semibold">{{ purchase.package_name }}</p>
                                        <p class="opacity-75 text-xs">{{ formatPackageType(purchase.package_type) }}</p>
                                    </div>
                                </div>
                                <span 
                                    :class="getStatusColor(purchase.status)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold"
                                >
                                    {{ purchase.status }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="opacity-75">Amount:</span>
                                    <span class="font-semibold">KES {{ purchase.amount.toLocaleString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Purchased:</span>
                                    <span>{{ purchase.created_at }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-75">Expires:</span>
                                    <span>{{ purchase.expires_at || 'N/A' }}</span>
                                </div>
                                <div v-if="purchase.is_active" class="flex justify-between text-green-400">
                                    <span>Days Remaining:</span>
                                    <span class="font-semibold">{{ purchase.days_remaining }} days</span>
                                </div>
                                <div v-if="purchase.is_expired" class="flex justify-between text-red-400">
                                    <span>Status:</span>
                                    <span class="font-semibold">Expired</span>
                                </div>
                            </div>
                            
                            <!-- Code Display for Mobile -->
                            <div v-if="purchase.codes.certification || purchase.codes.access || purchase.codes.verification" class="mt-3 pt-3 border-t border-white/10">
                                <p class="text-xs opacity-75 mb-1">Package Code:</p>
                                <div class="flex items-center justify-between bg-black/30 rounded-lg p-2">
                                    <code class="text-white font-mono text-xs truncate">
                                        {{ purchase.codes.certification || purchase.codes.access || purchase.codes.verification }}
                                    </code>
                                    <button @click="copyPackageCode(purchase)" class="text-purple-400 hover:text-purple-300 ml-2">
                                        <Copy :size="14" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/10">
                                <Link 
                                    :href="'/certification/' + purchase.id"
                                    class="text-blue-400 hover:text-blue-300 text-sm flex items-center space-x-1"
                                >
                                    <Eye :size="14" />
                                    <span>Details</span>
                                </Link>
                                <button 
                                    v-if="purchase.status === 'pending'"
                                    @click="activatePackage(purchase.id)"
                                    class="text-green-400 hover:text-green-300 text-sm flex items-center space-x-1"
                                >
                                    <Play :size="14" />
                                    <span>Activate</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="purchases.data.length === 0" class="text-center py-12">
                        <Package :size="64" class="mx-auto text-blue-400 mb-4 opacity-50" />
                        <p class="text-blue-300 text-lg mb-2">No certification purchases yet</p>
                        <p class="text-blue-400 text-sm mb-6">Purchase your first certification package to get started</p>
                        <Link 
                            href="/certification"
                            class="bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-500 hover:to-cyan-500 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg shadow-blue-500/50 inline-flex items-center space-x-2"
                        >
                            <Award :size="18" />
                            <span>View Packages</span>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="purchases.total > 0" class="flex items-center justify-between mt-6 pt-6 border-t border-blue-500/20">
                        <p class="text-blue-300 text-sm">
                            Showing {{ purchases.from }} to {{ purchases.to }} of {{ purchases.total }} purchases
                        </p>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="previousPage"
                                :disabled="!purchases.prev_page_url"
                                class="bg-black/30 border border-blue-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-blue-500 transition-colors"
                            >
                                <ChevronLeft :size="16" />
                            </button>
                            <span class="text-white text-sm">
                                Page {{ purchases.current_page }} of {{ purchases.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="!purchases.next_page_url"
                                class="bg-black/30 border border-blue-500/30 text-white px-3 py-2 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed hover:border-blue-500 transition-colors"
                            >
                                <ChevronRight :size="16" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Summary -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gradient-to-br from-blue-600/40 to-cyan-600/40 backdrop-blur-xl border border-blue-500/30 rounded-2xl p-4 shadow-2xl shadow-blue-500/20">
                        <div class="text-center">
                            <p class="text-blue-300 text-sm mb-1">Total Purchases</p>
                            <p class="text-2xl font-bold text-white">{{ purchases.total }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-600/40 to-emerald-600/40 backdrop-blur-xl border border-green-500/30 rounded-2xl p-4 shadow-2xl shadow-green-500/20">
                        <div class="text-center">
                            <p class="text-green-300 text-sm mb-1">Active Packages</p>
                            <p class="text-2xl font-bold text-white">{{ activePackagesCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-600/40 to-pink-600/40 backdrop-blur-xl border border-purple-500/30 rounded-2xl p-4 shadow-2xl shadow-purple-500/20">
                        <div class="text-center">
                            <p class="text-purple-300 text-sm mb-1">Pending Activation</p>
                            <p class="text-2xl font-bold text-white">{{ pendingPackagesCount }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-orange-600/40 to-yellow-600/40 backdrop-blur-xl border border-orange-500/30 rounded-2xl p-4 shadow-2xl shadow-orange-500/20">
                        <div class="text-center">
                            <p class="text-orange-300 text-sm mb-1">Expired</p>
                            <p class="text-2xl font-bold text-white">{{ expiredPackagesCount }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import {
    Award, Key, ShieldCheck, Eye, Play,
    Package, ChevronLeft, ChevronRight, History,
    Copy
} from 'lucide-vue-next';

const props = defineProps({
    purchases: Object,
});

// Compute stats from purchases
const activePackagesCount = computed(() => {
    return props.purchases.data.filter(p => p.is_active).length;
});

const pendingPackagesCount = computed(() => {
    return props.purchases.data.filter(p => p.status === 'pending').length;
});

const expiredPackagesCount = computed(() => {
    return props.purchases.data.filter(p => p.is_expired).length;
});

const getPackageColor = (type) => {
    switch(type) {
        case 'certification': return 'bg-gradient-to-br from-blue-600/40 to-cyan-600/40 border-blue-500/30';
        case 'access_code': return 'bg-gradient-to-br from-green-600/40 to-emerald-600/40 border-green-500/30';
        case 'verification': return 'bg-gradient-to-br from-purple-600/40 to-pink-600/40 border-purple-500/30';
        default: return 'bg-gradient-to-br from-slate-600/40 to-slate-700/40 border-slate-500/30';
    }
};

const getPackageIcon = (type) => {
    switch(type) {
        case 'certification': return Award;
        case 'access_code': return Key;
        case 'verification': return ShieldCheck;
        default: return Package;
    }
};

const getIconColor = (type) => {
    switch(type) {
        case 'certification': return 'text-blue-400';
        case 'access_code': return 'text-green-400';
        case 'verification': return 'text-purple-400';
        default: return 'text-slate-400';
    }
};

const formatPackageType = (type) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getStatusColor = (status) => {
    switch(status) {
        case 'active': return 'bg-green-500/20 text-green-400';
        case 'pending': return 'bg-yellow-500/20 text-yellow-400';
        case 'expired': return 'bg-red-500/20 text-red-400';
        case 'cancelled': return 'bg-gray-500/20 text-gray-400';
        default: return 'bg-blue-500/20 text-blue-400';
    }
};

const copyPackageCode = (purchase) => {
    const code = purchase.codes.certification || purchase.codes.access || purchase.codes.verification;
    if (code) {
        navigator.clipboard.writeText(code)
            .then(() => alert('Code copied to clipboard!'))
            .catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy code to clipboard.');
            });
    }
};

const activatePackage = async (id) => {
    if (!confirm('Are you sure you want to activate this package?')) return;

    try {
        // Changed from /dollar-zone/certification/{id}/activate to /certification/{id}/activate
        await router.post(`/certification/${id}/activate`, {}, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page?.props?.flash || {};
                if (flash.success) {
                    alert(flash.success);
                    router.reload();
                }
            },
            onError: (errors) => {
                console.error('Error activating package:', errors);
                alert('Failed to activate package.');
            }
        });
    } catch (error) {
        console.error('Error activating package:', error);
        alert('An unexpected error occurred.');
    }
};

const previousPage = () => {
    if (props.purchases.prev_page_url) {
        router.visit(props.purchases.prev_page_url);
    }
};

const nextPage = () => {
    if (props.purchases.next_page_url) {
        router.visit(props.purchases.next_page_url);
    }
};
</script>

<style scoped>
/* Smooth transitions */
* {
    transition: all 0.2s ease-in-out;
}

/* Hover effects */
button:hover:not(:disabled) {
    transform: translateY(-1px);
}

/* Custom scrollbar for table */
.hidden.lg\:block::-webkit-scrollbar {
    height: 6px;
}

.hidden.lg\:block::-webkit-scrollbar-track {
    background: rgba(30, 41, 59, 0.3);
    border-radius: 3px;
}

.hidden.lg\:block::-webkit-scrollbar-thumb {
    background: linear-gradient(to right, #3b82f6, #06b6d4);
    border-radius: 3px;
}

.hidden.lg\:block::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to right, #2563eb, #0891b2);
}
</style>