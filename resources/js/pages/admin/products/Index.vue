<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, Package, Pencil, Plus, Trash2 } from '@lucide/vue';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { formatTaka } from '@/lib/shop/currency';
import { dashboard } from '@/routes';
import { create, edit, index, show } from '@/routes/admin/products';
import type { AdminProductListItem } from '@/types/admin';

defineProps<{
    products: AdminProductListItem[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Products',
                href: index(),
            },
        ],
    },
});

// Shared color styles for badges
const badgeColors = {
    green: 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-400',
    red: 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-400',
    gray: 'border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400',
    amber: 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-400',
    violet: 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900 dark:bg-violet-950/50 dark:text-violet-400',
    orange: 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-900 dark:bg-orange-950/50 dark:text-orange-400',
    blue: 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-400',
};
</script>

<template>
    <Head title="Products" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
        <!-- Page header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <Heading
                    title="Products"
                    description="Manage your store catalog"
                />
                <Badge
                    variant="outline"
                    class="mt-1 tabular-nums"
                    :class="badgeColors.blue"
                >
                    {{ products.length }}
                    {{ products.length === 1 ? 'item' : 'items' }}
                </Badge>
            </div>

            <Button as-child class="shadow-sm">
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add product
                </Link>
            </Button>
        </div>

        <!-- Table card -->
        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr
                            class="border-b bg-muted/50 text-left text-xs tracking-wide text-muted-foreground uppercase"
                        >
                            <th class="px-4 py-3 font-semibold">Image</th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Category</th>
                            <th class="px-4 py-3 font-semibold">Price</th>
                            <th class="px-4 py-3 font-semibold">Stock</th>
                            <th class="px-4 py-3 font-semibold">Sold</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <!-- Image -->
                            <td class="px-4 py-3">
                                <div
                                    class="flex size-14 items-center justify-center overflow-hidden rounded-lg border bg-muted shadow-xs"
                                >
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="product.name"
                                        class="size-full object-cover"
                                    />
                                    <Package
                                        v-else
                                        class="size-5 text-muted-foreground/60"
                                    />
                                </div>
                            </td>

                            <!-- Name -->
                            <td class="max-w-[260px] px-4 py-3">
                                <div
                                    class="truncate font-medium text-foreground"
                                >
                                    {{ product.name }}
                                </div>
                                <div
                                    class="mt-0.5 truncate font-mono text-xs text-muted-foreground"
                                >
                                    {{ product.slug }}
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-foreground/90">{{
                                        product.category.name
                                    }}</span>
                                    <Badge
                                        v-if="product.category.is_deleted"
                                        variant="outline"
                                        :class="badgeColors.orange"
                                    >
                                        Deleted
                                    </Badge>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold tabular-nums">
                                    {{ formatTaka(product.price) }}
                                </div>
                                <div
                                    v-if="product.compare_at_price"
                                    class="text-xs text-muted-foreground tabular-nums line-through"
                                >
                                    {{ formatTaka(product.compare_at_price) }}
                                </div>
                            </td>

                            <!-- Stock -->
                            <td class="px-4 py-3">
                                <Badge
                                    variant="outline"
                                    class="gap-1.5"
                                    :class="
                                        product.stock_status === 'in_stock'
                                            ? badgeColors.green
                                            : badgeColors.red
                                    "
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            product.stock_status === 'in_stock'
                                                ? 'bg-emerald-500'
                                                : 'bg-red-500'
                                        "
                                    />
                                    {{
                                        product.stock_status === 'in_stock'
                                            ? 'In stock'
                                            : 'Out of stock'
                                    }}
                                </Badge>
                            </td>

                            <!-- Sold -->
                            <td class="px-4 py-3 font-medium tabular-nums">
                                {{ product.sold_count }}
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        variant="outline"
                                        class="gap-1.5"
                                        :class="
                                            product.is_active
                                                ? badgeColors.green
                                                : badgeColors.gray
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                product.is_active
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-400'
                                            "
                                        />
                                        {{
                                            product.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        }}
                                    </Badge>
                                    <Badge
                                        v-if="product.is_featured"
                                        variant="outline"
                                        :class="badgeColors.amber"
                                    >
                                        Featured
                                    </Badge>
                                    <Badge
                                        v-if="product.is_best_seller"
                                        variant="outline"
                                        :class="badgeColors.violet"
                                    >
                                        Best seller
                                    </Badge>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="text-blue-600 hover:bg-blue-50 hover:text-blue-700 dark:text-blue-400 dark:hover:bg-blue-950/50 dark:hover:text-blue-300"
                                    >
                                        <Link :href="show(product.id)">
                                            <Eye class="size-4" />
                                            View
                                        </Link>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="text-amber-600 hover:bg-amber-50 hover:text-amber-700 dark:text-amber-400 dark:hover:bg-amber-950/50 dark:hover:text-amber-300"
                                    >
                                        <Link :href="edit(product.id)">
                                            <Pencil class="size-4" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="border-red-200 text-red-600 hover:bg-red-50 hover:text-red-700 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/50 dark:hover:text-red-300"
                                            >
                                                <Trash2 class="size-4" />
                                                Delete
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <Form
                                                v-bind="
                                                    ProductController.destroy.form(
                                                        product.id,
                                                    )
                                                "
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                v-slot="{ processing }"
                                            >
                                                <DialogHeader class="space-y-3">
                                                    <DialogTitle>
                                                        Delete product?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        This will remove
                                                        <strong>{{
                                                            product.name
                                                        }}</strong>
                                                        from your catalog.
                                                    </DialogDescription>
                                                </DialogHeader>

                                                <DialogFooter class="mt-4 gap-2">
                                                    <DialogClose as-child>
                                                        <Button
                                                            type="button"
                                                            variant="secondary"
                                                        >
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        :disabled="processing"
                                                    >
                                                        Delete
                                                    </Button>
                                                </DialogFooter>
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="products.length === 0">
                            <td colspan="8" class="px-4 py-16">
                                <div
                                    class="flex flex-col items-center gap-3 text-center"
                                >
                                    <div
                                        class="flex size-12 items-center justify-center rounded-full bg-muted"
                                    >
                                        <Package
                                            class="size-6 text-muted-foreground"
                                        />
                                    </div>
                                    <p class="text-muted-foreground">
                                        No products yet. Create your first
                                        product.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>