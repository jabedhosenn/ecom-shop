<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Heart, LayoutGrid, Package, ShoppingCart, Tags, Ticket } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as categoriesIndex } from '@/routes/admin/categories';
import { index as couponsIndex } from '@/routes/admin/coupons';
import { index as ordersIndex } from '@/routes/admin/orders';
import { index as productsIndex } from '@/routes/admin/products';
import { index as wishlistsIndex } from '@/routes/admin/wishlists';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Products',
        href: productsIndex(),
        icon: Package,
    },
    {
        title: 'Categories',
        href: categoriesIndex(),
        icon: Tags,
    },
    {
        title: 'Orders',
        href: ordersIndex(),
        icon: ShoppingCart,
    },
    {
        title: 'Coupons',
        href: couponsIndex(),
        icon: Ticket,
    },
    {
        title: 'Wishlists',
        href: wishlistsIndex(),
        icon: Heart,
    },
];
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-slate-200/80 bg-white shadow-[4px_0_24px_-18px_rgba(15,23,42,0.18)] dark:border-slate-800"
    >
        <SidebarHeader class="px-3 pt-4 pb-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        class="rounded-xl px-3"
                        as-child
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-2">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="border-t border-slate-100 p-2 dark:border-slate-800">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
