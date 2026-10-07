<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <header
        data-print-hidden
        class="sticky top-0 z-30 flex h-16 shrink-0 items-center border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl transition-colors sm:px-6 lg:px-8 dark:border-slate-800 dark:bg-slate-950/90"
    >
        <div class="flex min-w-0 items-center gap-3 sm:gap-4">
            <SidebarTrigger
                class="size-9 shrink-0 rounded-lg text-slate-600 hover:bg-blue-50 hover:text-blue-700 focus-visible:ring-2 focus-visible:ring-blue-500/40 dark:text-slate-300 dark:hover:bg-blue-950/60 dark:hover:text-blue-300"
            />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
