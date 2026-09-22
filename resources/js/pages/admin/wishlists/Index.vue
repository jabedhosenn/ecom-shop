<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart } from '@lucide/vue';
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
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading
            title="Wishlists"
            description="View customer wishlist items across the store"
        />

        <form
            :action="index()"
            method="get"
            class="grid gap-4 rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border lg:grid-cols-[1fr_auto]"
        >
            <div class="grid gap-2">
                <Label for="search">Search</Label>
                <Input
                    id="search"
                    name="search"
                    :default-value="props.filters.search"
                    placeholder="Customer name, email, or product name"
                />
            </div>

            <div class="flex items-end gap-2">
                <Button type="submit">Filter</Button>
                <Button as-child variant="outline">
                    <Link :href="index()">Reset</Link>
                </Button>
            </div>
        </form>

        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-sm">
                    <thead class="border-b bg-muted/40 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Stock</th>
                            <th class="px-4 py-3 font-medium">Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="wishlist in wishlists"
                            :key="wishlist.id"
                            class="border-b last:border-b-0"
                        >
                            <td class="px-4 py-3">
                                <p class="font-medium">
                                    {{ wishlist.user.name }}
                                </p>
                                <p class="text-muted-foreground">
                                    {{ wishlist.user.email }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="size-12 shrink-0 overflow-hidden rounded-md border bg-muted"
                                    >
                                        <img
                                            v-if="wishlist.product.image"
                                            :src="wishlist.product.image"
                                            :alt="wishlist.product.name"
                                            class="size-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{ wishlist.product.name }}
                                        </p>
                                        <p
                                            v-if="!wishlist.product.is_active"
                                            class="text-muted-foreground"
                                        >
                                            Inactive product
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ formatTaka(wishlist.product.price) }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        stockStatusVariant(
                                            wishlist.product.stock_status,
                                        )
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
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                <Heart
                                    class="mx-auto mb-2 size-8 opacity-40"
                                />
                                No wishlist items found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
