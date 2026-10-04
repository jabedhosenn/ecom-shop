<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart, Search } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatTaka } from '@/lib/shop/currency';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/wishlists';
import type {
    AdminWishlistFilters,
    AdminWishlistListItem,
} from '@/types/admin';

const props = defineProps<{
    wishlists: AdminWishlistListItem[];
    filters: AdminWishlistFilters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Wishlists',
                href: index(),
            },
        ],
    },
});

function stockStatusVariant(
    status: AdminWishlistListItem['product']['stock_status'],
): 'default' | 'secondary' | 'destructive' | 'outline' {
    return status === 'in_stock' ? 'default' : 'destructive';
}

function formatDate(value: string): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
}
</script>

<template>
    <Head title="Wishlists" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
    >
        <!-- Header -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Wishlists"
                description="View customer wishlist items across the store"
            />
            <div
                class="inline-flex w-fit items-center gap-2 rounded-full border border-rose-200 bg-rose-50 px-4 py-1.5 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300"
            >
                <Heart class="size-4 fill-current" />
                {{ wishlists.length }} items
            </div>
        </div>

        <!-- Filters -->
        <form
            :action="index()"
            method="get"
            class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border lg:grid-cols-[1fr_auto]"
        >
            <div class="grid gap-2">
                <Label
                    for="search"
                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                >
                    Search
                </Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="search"
                        name="search"
                        class="pl-9"
                        :default-value="props.filters.search"
                        placeholder="Customer name, email, or product name"
                    />
                </div>
            </div>

            <div class="flex items-end gap-2">
                <Button
                    type="submit"
                    class="bg-indigo-600 text-white hover:bg-indigo-700"
                >
                    Filter
                </Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Reset</Link>
                </Button>
            </div>
        </form>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead
                        class="border-b bg-gradient-to-r from-indigo-50 to-rose-50 text-left dark:from-indigo-500/10 dark:to-rose-500/10"
                    >
                        <tr>
                            <th
                                class="px-4 py-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Customer
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Product
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Price
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Stock
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                            >
                                Added
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="wishlist in wishlists"
                            :key="wishlist.id"
                            class="border-b transition-colors last:border-b-0 hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 uppercase dark:bg-indigo-500/20 dark:text-indigo-300"
                                    >
                                        {{ wishlist.user.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{ wishlist.user.name }}
                                        </p>
                                        <p class="text-muted-foreground">
                                            {{ wishlist.user.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-12 shrink-0 overflow-hidden rounded-lg border bg-muted shadow-sm"
                                    >
                                        <img
                                            v-if="wishlist.product.image"
                                            :src="wishlist.product.image"
                                            :alt="wishlist.product.name"
                                            class="size-full object-cover"
                                        />
                                    </div>
                                    <div class="grid gap-1">
                                        <p class="font-medium">
                                            {{ wishlist.product.name }}
                                        </p>
                                        <span
                                            v-if="!wishlist.product.is_active"
                                            class="inline-flex w-fit items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-500/15 dark:text-amber-300"
                                        >
                                            Inactive product
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-4 py-3 font-semibold text-emerald-700 dark:text-emerald-400"
                            >
                                {{ formatTaka(wishlist.product.price) }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        stockStatusVariant(
                                            wishlist.product.stock_status,
                                        )
                                    "
                                    class="border-transparent capitalize"
                                    :class="
                                        wishlist.product.stock_status ===
                                        'in_stock'
                                            ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'bg-rose-100 text-rose-700 hover:bg-rose-100 dark:bg-rose-500/15 dark:text-rose-300'
                                    "
                                >
                                    {{
                                        wishlist.product.stock_status.replace(
                                            '_',
                                            ' ',
                                        )
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ formatDate(wishlist.created_at) }}
                            </td>
                        </tr>
                        <tr v-if="wishlists.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-14 text-center text-muted-foreground"
                            >
                                <div
                                    class="mx-auto mb-3 flex size-14 items-center justify-center rounded-full bg-rose-50 dark:bg-rose-500/10"
                                >
                                    <Heart class="size-7 text-rose-400" />
                                </div>
                                No wishlist items found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>