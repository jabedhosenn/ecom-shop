<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    Boxes,
    CircleCheck,
    CircleX,
    Eye,
    Package,
    Pencil,
    Plus,
    ShoppingCart,
    Trash2,
} from '@lucide/vue';
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
    green: 'border-transparent bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-400/20',
    red: 'border-transparent bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-400/20',
    gray: 'border-transparent bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/20 dark:bg-slate-500/10 dark:text-slate-400 dark:ring-slate-400/20',
    amber: 'border-transparent bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-400/20',
    violet: 'border-transparent bg-violet-50 text-violet-700 ring-1 ring-inset ring-violet-600/20 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-400/20',
    orange: 'border-transparent bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-600/20 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-400/20',
    blue: 'border-transparent bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-400/20',
};
</script>

<template>
    <Head title="Products" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-8">
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
                    class="mt-1 rounded-full px-2.5 tabular-nums"
                    :class="badgeColors.blue"
                >
                    {{ products.length }}
                    {{ products.length === 1 ? 'item' : 'items' }}
                </Badge>
            </div>

            <Button
                as-child
                class="rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-600/25 transition-all hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg hover:shadow-blue-600/30"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add product
                </Link>
            </Button>
        </div>

        <!-- Summary cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- Total -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-blue-900/50 dark:from-blue-950/40 dark:to-transparent"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div
                            class="text-xs font-medium tracking-wide text-blue-700/80 uppercase dark:text-blue-300/80"
                        >
                            Total products
                        </div>
                        <div
                            class="mt-1 text-3xl font-bold text-blue-950 tabular-nums dark:text-blue-50"
                        >
                            {{ products.length }}
                        </div>
                    </div>
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md shadow-blue-500/30"
                    >
                        <Boxes class="size-5" />
                    </div>
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-blue-500 to-blue-300"
                />
            </div>

            <!-- In stock -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-emerald-900/50 dark:from-emerald-950/40 dark:to-transparent"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div
                            class="text-xs font-medium tracking-wide text-emerald-700/80 uppercase dark:text-emerald-300/80"
                        >
                            In stock
                        </div>
                        <div
                            class="mt-1 text-3xl font-bold text-emerald-950 tabular-nums dark:text-emerald-50"
                        >
                            {{
                                products.filter(
                                    (p) => p.stock_status === 'in_stock',
                                ).length
                            }}
                        </div>
                    </div>
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-md shadow-emerald-500/30"
                    >
                        <CircleCheck class="size-5" />
                    </div>
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-300"
                />
            </div>

            <!-- Out of stock -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-red-100 bg-gradient-to-br from-red-50 to-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-red-900/50 dark:from-red-950/40 dark:to-transparent"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div
                            class="text-xs font-medium tracking-wide text-red-700/80 uppercase dark:text-red-300/80"
                        >
                            Out of stock
                        </div>
                        <div
                            class="mt-1 text-3xl font-bold text-red-950 tabular-nums dark:text-red-50"
                        >
                            {{
                                products.filter(
                                    (p) => p.stock_status !== 'in_stock',
                                ).length
                            }}
                        </div>
                    </div>
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-red-500 to-red-600 text-white shadow-md shadow-red-500/30"
                    >
                        <CircleX class="size-5" />
                    </div>
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-red-500 to-red-300"
                />
            </div>

            <!-- Total sold -->
            <div
                class="group relative overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-violet-900/50 dark:from-violet-950/40 dark:to-transparent"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <div
                            class="text-xs font-medium tracking-wide text-violet-700/80 uppercase dark:text-violet-300/80"
                        >
                            Total sold
                        </div>
                        <div
                            class="mt-1 text-3xl font-bold text-violet-950 tabular-nums dark:text-violet-50"
                        >
                            {{
                                products.reduce(
                                    (sum, p) => sum + Number(p.sold_count),
                                    0,
                                )
                            }}
                        </div>
                    </div>
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-violet-600 text-white shadow-md shadow-violet-500/30"
                    >
                        <ShoppingCart class="size-5" />
                    </div>
                </div>
                <div
                    class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-violet-500 to-violet-300"
                />
            </div>
        </div>

        <!-- Table card -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200 bg-card shadow-sm dark:border-slate-800"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-slate-100/60 text-left text-[11px] tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:from-slate-900/60 dark:to-slate-900/30 dark:text-slate-400"
                        >
                            <th class="px-5 py-3.5 font-semibold">Image</th>
                            <th class="px-4 py-3.5 font-semibold">Name</th>
                            <th class="px-4 py-3.5 font-semibold">Category</th>
                            <th class="px-4 py-3.5 font-semibold">Price</th>
                            <th class="px-4 py-3.5 font-semibold">Stock</th>
                            <th class="px-4 py-3.5 font-semibold">Sold</th>
                            <th class="px-4 py-3.5 font-semibold">Status</th>
                            <th
                                class="px-5 py-3.5 text-right font-semibold"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800/70"
                    >
                        <tr
                            v-for="product in products"
                            :key="product.id"
                            class="group transition-colors hover:bg-blue-50/40 dark:hover:bg-blue-950/20"
                        >
                            <!-- Image (with stock accent bar) -->
                            <td
                                class="border-l-4 px-5 py-4"
                                :class="
                                    product.stock_status === 'in_stock'
                                        ? 'border-l-emerald-500'
                                        : 'border-l-red-400'
                                "
                            >
                                <div
                                    class="flex size-14 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm ring-2 ring-white transition-transform group-hover:scale-105 dark:border-slate-700 dark:bg-slate-800 dark:ring-slate-900"
                                >
                                    <img
                                        v-if="product.image"
                                        :src="product.image"
                                        :alt="product.name"
                                        class="size-full object-cover"
                                    />
                                    <Package
                                        v-else
                                        class="size-5 text-slate-400"
                                    />
                                </div>
                            </td>

                            <!-- Name -->
                            <td class="max-w-[260px] px-4 py-4">
                                <div
                                    class="truncate font-semibold text-slate-900 dark:text-slate-100"
                                >
                                    {{ product.name }}
                                </div>
                                <div
                                    class="mt-1 inline-block max-w-full truncate rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                >
                                    {{ product.slug }}
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 ring-1 ring-sky-600/20 ring-inset dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-400/20"
                                    >
                                        {{ product.category.name }}
                                    </span>
                                    <Badge
                                        v-if="product.category.is_deleted"
                                        variant="outline"
                                        class="rounded-full"
                                        :class="badgeColors.orange"
                                    >
                                        Deleted
                                    </Badge>
                                </div>
                            </td>

                            <!-- Price -->
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div
                                    class="text-base font-bold text-emerald-600 tabular-nums dark:text-emerald-400"
                                >
                                    {{ formatTaka(product.price) }}
                                </div>
                                <div
                                    v-if="product.compare_at_price"
                                    class="mt-0.5 text-xs text-rose-400 tabular-nums line-through decoration-rose-300 dark:text-rose-400/70"
                                >
                                    {{ formatTaka(product.compare_at_price) }}
                                </div>
                            </td>

                            <!-- Stock -->
                            <td class="px-4 py-4">
                                <Badge
                                    variant="outline"
                                    class="gap-1.5 rounded-full px-2.5"
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
                            <td class="px-4 py-4">
                                <span
                                    class="inline-flex min-w-9 items-center justify-center rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700 ring-1 ring-violet-600/20 tabular-nums ring-inset dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-400/20"
                                >
                                    {{ product.sold_count }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <Badge
                                        variant="outline"
                                        class="gap-1.5 rounded-full px-2.5"
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
                                        class="rounded-full px-2.5"
                                        :class="badgeColors.amber"
                                    >
                                        Featured
                                    </Badge>
                                    <Badge
                                        v-if="product.is_best_seller"
                                        variant="outline"
                                        class="rounded-full px-2.5"
                                        :class="badgeColors.violet"
                                    >
                                        Best seller
                                    </Badge>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <div
                                    class="flex items-center justify-end gap-1.5"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="rounded-lg border-blue-200 text-blue-600 transition-colors hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-900 dark:text-blue-400 dark:hover:border-blue-500 dark:hover:bg-blue-500 dark:hover:text-white"
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
                                        class="rounded-lg border-amber-200 text-amber-600 transition-colors hover:border-amber-500 hover:bg-amber-500 hover:text-white dark:border-amber-900 dark:text-amber-400 dark:hover:border-amber-500 dark:hover:bg-amber-500 dark:hover:text-white"
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
                                                class="rounded-lg border-red-200 text-red-600 transition-colors hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-red-900 dark:text-red-400 dark:hover:border-red-500 dark:hover:bg-red-500 dark:hover:text-white"
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
                            <td colspan="8" class="px-4 py-20">
                                <div
                                    class="flex flex-col items-center gap-4 text-center"
                                >
                                    <div
                                        class="flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-100 ring-1 ring-blue-200 dark:from-blue-950/50 dark:to-indigo-950/50 dark:ring-blue-900"
                                    >
                                        <Package
                                            class="size-7 text-blue-500"
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