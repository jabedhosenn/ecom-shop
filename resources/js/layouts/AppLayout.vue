<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();
const isAdminPanel = computed(() => page.component.startsWith('admin/'));
</script>

<template>
    <div
        class="min-h-svh"
        :data-admin-panel="isAdminPanel ? '' : undefined"
    >
        <AppLayout :breadcrumbs="breadcrumbs">
            <slot />
        </AppLayout>
    </div>
</template>
