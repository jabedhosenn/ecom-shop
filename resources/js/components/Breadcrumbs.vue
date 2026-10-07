<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

type Props = {
    breadcrumbs: BreadcrumbItemType[];
};

defineProps<Props>();
</script>

<template>
    <Breadcrumb>
        <BreadcrumbList class="min-w-0 gap-1.5 text-sm sm:gap-2">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <BreadcrumbItem>
                    <template v-if="index === breadcrumbs.length - 1">
                        <BreadcrumbPage
                            class="truncate rounded-lg border border-blue-100 bg-blue-50/80 px-2.5 py-1.5 text-xs font-semibold text-blue-800 sm:text-sm dark:border-blue-900/70 dark:bg-blue-950/50 dark:text-blue-200"
                        >
                            {{ item.title }}
                        </BreadcrumbPage>
                    </template>
                    <template v-else>
                        <BreadcrumbLink
                            as-child
                            class="truncate text-xs font-medium text-slate-500 hover:text-slate-800 sm:text-sm dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            <Link :href="item.href">{{ item.title }}</Link>
                        </BreadcrumbLink>
                    </template>
                </BreadcrumbItem>
                <BreadcrumbSeparator
                    v-if="index !== breadcrumbs.length - 1"
                    class="text-slate-300 dark:text-slate-600"
                />
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
