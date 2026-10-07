<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-sidebar-primary-foreground shadow-md shadow-blue-600/20"
    >
        <AppLogoIcon class="size-6 fill-current text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left">
        <span class="truncate text-base leading-tight font-bold tracking-tight">
            Shop<span class="text-blue-600 dark:text-blue-400">Ease</span>
        </span>
    </div>
</template>
