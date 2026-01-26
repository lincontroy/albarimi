<template>
    <div>
        <Head :title="title" />
        <div class="flex h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 overflow-hidden">
            <Sidebar 
                :isSidebarOpen="isSidebarOpen" 
                @toggle-sidebar="isSidebarOpen = false"
            />
            
            <!-- Mobile overlay -->
            <div
                v-if="isSidebarOpen"
                class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-20"
                @click="isSidebarOpen = false"
            />

            <!-- Main Content -->
            <main class="flex-1 overflow-auto">
                <Header @toggle-sidebar="isSidebarOpen = true" />
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import Header from '@/Components/Header.vue';

defineProps({
    title: String,
});

const isSidebarOpen = ref(true);
</script>