<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    items: NavItem[];
}>();

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-4">
        <SidebarGroupLabel
            class="mb-2 px-3 text-[11px] font-semibold tracking-[0.12em] text-slate-400 uppercase dark:text-slate-500"
        >
            Store management
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    class="h-10 rounded-lg px-3 font-medium text-slate-600 transition-colors hover:bg-blue-50 hover:text-blue-700 data-[active=true]:bg-blue-600 data-[active=true]:text-white data-[active=true]:shadow-sm data-[active=true]:shadow-blue-900/10 data-[active=true]:hover:bg-blue-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-blue-300 dark:data-[active=true]:bg-blue-500 dark:data-[active=true]:text-white dark:data-[active=true]:hover:bg-blue-600"
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
