<template>
    <header class="bg-slate-800/50 backdrop-blur-xl border-b border-purple-500/20 text-white px-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center space-x-4">
            <button
                @click="$emit('toggle-sidebar')"
                class="lg:hidden hover:bg-purple-500/20 p-2 rounded-lg transition-colors"
            >
                <Menu :size="24" />
            </button>
            <h1 class="text-xl lg:text-2xl font-bold bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">Dashboard</h1>
        </div>
        <div class="flex items-center space-x-4">
            <button class="hover:bg-purple-500/20 p-2 rounded-full transition-colors relative">
                <Bell :size="20" class="text-purple-400" />
                <span class="absolute top-1 right-1 w-2 h-2 bg-pink-500 rounded-full"></span>
            </button>
            <a href="#" class="hover:bg-purple-500/20 p-2 rounded-full transition-colors">
                <User :size="20" class="text-purple-400" />
            </a>
        </div>
    </header>
</template>

<script setup>
import { Menu, Bell, User } from 'lucide-vue-next';

defineEmits(['toggle-sidebar']);
</script>