<template>
    <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 backdrop-blur-sm text-white px-4 lg:px-8 py-3 flex items-center space-x-2 text-sm border-b border-purple-500/20">
        <User :size="16" class="text-purple-400" />
        <span>Account</span>
        <span class="text-purple-400">›</span>
        <span class="text-purple-300">Dashboard</span>
    </div>
</template>

<script setup>
import { User } from 'lucide-vue-next';
</script>