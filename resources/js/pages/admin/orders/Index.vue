<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Banknote,
    CalendarClock,
    CheckCircle2,
    Clock3,
    CreditCard,
    Eye,
    Filter,
    PackageSearch,
    Phone,
    RotateCcw,
    Search,
    ShoppingBag,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatTaka } from '@/lib/shop/currency';
import { dashboard } from '@/routes';
import { index, show } from '@/routes/admin/orders';
import type {
    AdminOrderFilters,
    AdminOrderListItem,
    AdminStatusOption,
} from '@/types/admin';

const props = defineProps<{
    orders: AdminOrderListItem[];
    filters: AdminOrderFilters;
    statusOptions: AdminStatusOption[];
    paymentStatusOptions: AdminStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Orders',
                href: index(),
            },
        ],
    },
});

const selectClass =
    'border-input bg-background ring-offset-background focus-visible:ring-ring flex h-10 w-full rounded-lg border px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]';

function orderStatusVariant(
    status: AdminOrderListItem['status'],
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'delivered':
            return 'default';
        case 'cancelled':
            return 'destructive';
        case 'pending':
            return 'secondary';
        default:
            return 'outline';
    }
}

function paymentStatusVariant(
    status: AdminOrderListItem['payment_status'],
): 'default' | 'secondary' | 'destructive' | 'outline' {
    switch (status) {
        case 'paid':
            return 'default';
        case 'failed':
        case 'cancelled':
            return 'destructive';
        default:
            return 'secondary';
    }
}

function paymentMethodLabel(method: AdminOrderListItem['payment_method']): string {
    return method === 'cod' ? 'Cash on Delivery' : 'SSLCommerz';
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
}

/* ---- Visual-only helpers ---- */
function orderStatusClass(status: string): string {
    switch (status) {
        case 'delivered':
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'cancelled':
            return 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400';
        case 'pending':
            return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400';
        case 'processing':
            return 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400';
        case 'shipped':
            return 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-400';
        case 'confirmed':
            return 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-400';
        default:
            return 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-500/30 dark:bg-slate-500/10 dark:text-slate-300';
    }
}

function orderDotClass(status: string): string {
    switch (status) {
        case 'delivered':
            return 'bg-emerald-500';
        case 'cancelled':
            return 'bg-red-500';
        case 'pending':
            return 'bg-amber-500';
        case 'processing':
            return 'bg-blue-500';
        case 'shipped':
            return 'bg-violet-500';
        case 'confirmed':
            return 'bg-sky-500';
        default:
            return 'bg-slate-400';
    }
}

function orderAccentClass(status: string): string {
    switch (status) {
        case 'delivered':
            return 'border-l-emerald-500';
        case 'cancelled':
            return 'border-l-red-500';
        case 'pending':
            return 'border-l-amber-500';
        case 'processing':
            return 'border-l-blue-500';
        case 'shipped':
            return 'border-l-violet-500';
        case 'confirmed':
            return 'border-l-sky-500';
        default:
            return 'border-l-slate-300';
    }
}

function paymentStatusClass(status: string): string {
    switch (status) {
        case 'paid':
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'failed':
        case 'cancelled':
            return 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400';
        case 'refunded':
            return 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-500/30 dark:bg-purple-500/10 dark:text-purple-400';
        case 'unpaid':
        case 'pending':
            return 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-500/30 dark:bg-orange-500/10 dark:text-orange-400';
        default:
            return 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-500/30 dark:bg-slate-500/10 dark:text-slate-300';
    }
}

function initials(name: string): string {
    return (name || '?')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

/* Summary cards (calculated from the orders shown on this page) */
const summary = computed(() => {
    const list = props.orders;

    return {
        total: list.length,
        pending: list.filter((order) => order.status === 'pending').length,
        delivered: list.filter((order) => order.status === 'delivered').length,
        revenue: list.reduce((sum, order) => sum + Number(order.total || 0), 0),
    };
});

const statCards = computed(() => [
    {
        key: 'total',
        label: 'Total orders',
        value: String(summary.value.total),
        icon: ShoppingBag,
        iconClass:
            'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        barClass: 'from-indigo-500 to-blue-500',
        glowClass: 'bg-indigo-500/10',
    },
    {
        key: 'pending',
        label: 'Pending',
        value: String(summary.value.pending),
        icon: Clock3,
        iconClass:
            'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
        barClass: 'from-amber-400 to-orange-500',
        glowClass: 'bg-amber-500/10',
    },
    {
        key: 'delivered',
        label: 'Delivered',
        value: String(summary.value.delivered),
        icon: CheckCircle2,
        iconClass:
            'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
        barClass: 'from-emerald-400 to-teal-500',
        glowClass: 'bg-emerald-500/10',
    },
    {
        key: 'revenue',
        label: 'Order value',
        value: formatTaka(summary.value.revenue),
        icon: Wallet,
        iconClass:
            'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        barClass: 'from-rose-400 to-pink-500',
        glowClass: 'bg-rose-500/10',
    },
]);
</script>

<template>
    <Head title="Orders" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
    >
        <!-- Banner header -->
        <div
            class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50 via-white to-violet-50 p-5 shadow-sm dark:border-indigo-500/20 dark:from-indigo-500/10 dark:via-transparent dark:to-violet-500/10 md:p-6"
        >
            <div
                class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-indigo-400/10 blur-2xl"
            />
            <div
                class="relative flex flex-wrap items-center justify-between gap-4"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="hidden size-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md sm:flex"
                    >
                        <ShoppingBag class="size-6" />
                    </div>
                    <Heading
                        title="Orders"
                        description="View and manage customer orders"
                    />
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm backdrop-blur dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300"
                >
                    <PackageSearch class="size-4" />
                    {{ orders.length }}
                    {{ orders.length === 1 ? 'order' : 'orders' }}
                </div>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                v-for="card in statCards"
                :key="card.key"
                class="group relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm transition-shadow hover:shadow-md dark:border-sidebar-border"
            >
                <div
                    class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r"
                    :class="card.barClass"
                />
                <div
                    class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full blur-xl"
                    :class="card.glowClass"
                />
                <div class="relative flex items-center gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-xl"
                        :class="card.iconClass"
                    >
                        <component :is="card.icon" class="size-5" />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                        >
                            {{ card.label }}
                        </p>
                        <p class="truncate text-2xl font-bold">
                            {{ card.value }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form
            :action="index()"
            method="get"
            class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border lg:grid-cols-[1fr_200px_200px_auto]"
        >
            <div class="grid gap-2">
                <Label for="search" class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Search</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-indigo-500"
                    />
                    <Input
                        id="search"
                        name="search"
                        class="h-10 rounded-lg pl-9"
                        :default-value="props.filters.search"
                        placeholder="Order number, customer, phone, or email"
                    />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="status" class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Order status</Label>
                <select
                    id="status"
                    name="status"
                    :class="selectClass"
                    :value="props.filters.status"
                >
                    <option value="">All statuses</option>
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="grid gap-2">
                <Label for="payment_status" class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Payment status</Label>
                <select
                    id="payment_status"
                    name="payment_status"
                    :class="selectClass"
                    :value="props.filters.payment_status"
                >
                    <option value="">All payments</option>
                    <option
                        v-for="option in paymentStatusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <Button
                    type="submit"
                    class="h-10 gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-sm hover:from-indigo-700 hover:to-violet-700"
                >
                    <Filter class="size-4" />
                    Filter
                </Button>
                <Button as-child variant="outline" class="h-10 gap-2">
                    <Link :href="index()">
                        <RotateCcw class="size-4" />
                        Reset
                    </Link>
                </Button>
            </div>
        </form>

        <!-- Desktop table -->
        <div
            class="hidden overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm md:block dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead
                        class="border-b bg-gradient-to-r from-indigo-600 to-violet-600 text-left text-white"
                    >
                        <tr class="text-xs uppercase tracking-wide">
                            <th class="px-4 py-3.5 font-semibold">Order</th>
                            <th class="px-4 py-3.5 font-semibold">Customer</th>
                            <th class="px-4 py-3.5 font-semibold">Items</th>
                            <th class="px-4 py-3.5 font-semibold">Total</th>
                            <th class="px-4 py-3.5 font-semibold">Payment</th>
                            <th class="px-4 py-3.5 font-semibold">Status</th>
                            <th class="px-4 py-3.5 font-semibold">Placed</th>
                            <th class="px-4 py-3.5 text-right font-semibold">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="border-l-4 transition-colors even:bg-muted/30 hover:bg-indigo-50/60 dark:hover:bg-indigo-500/10"
                            :class="orderAccentClass(order.status)"
                        >
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-indigo-700 dark:text-indigo-300">
                                    {{ order.order_number }}
                                </p>
                                <p
                                    class="mt-1 inline-flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Banknote
                                        v-if="order.payment_method === 'cod'"
                                        class="size-3.5 text-emerald-600"
                                    />
                                    <CreditCard
                                        v-else
                                        class="size-3.5 text-blue-600"
                                    />
                                    {{ paymentMethodLabel(order.payment_method) }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-semibold text-white ring-2 ring-white dark:ring-transparent"
                                    >
                                        {{ initials(order.customer_name) }}
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{ order.customer_name }}
                                        </p>
                                        <p
                                            class="inline-flex items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <Phone class="size-3" />
                                            {{ order.phone }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span
                                    class="inline-flex min-w-8 items-center justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-500/20 dark:text-slate-200"
                                >
                                    {{ order.items_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-emerald-700 dark:text-emerald-400">
                                {{ formatTaka(order.total) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <Badge
                                    variant="outline"
                                    class="capitalize"
                                    :class="paymentStatusClass(order.payment_status)"
                                >
                                    {{ order.payment_status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3.5">
                                <Badge
                                    variant="outline"
                                    class="gap-1.5 capitalize"
                                    :class="orderStatusClass(order.status)"
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="orderDotClass(order.status)"
                                    />
                                    {{ order.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    <CalendarClock class="size-3.5" />
                                    {{ formatDate(order.placed_at) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="gap-1.5 border-indigo-200 text-indigo-700 hover:bg-indigo-600 hover:text-white dark:border-indigo-500/30 dark:text-indigo-300 dark:hover:bg-indigo-500"
                                    >
                                        <Link :href="show(order.id)">
                                            <Eye class="size-4" />
                                            View
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="orders.length === 0">
                            <td colspan="8" class="px-4 py-14 text-center">
                                <div
                                    class="mx-auto flex size-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"
                                >
                                    <PackageSearch class="size-7" />
                                </div>
                                <p class="mt-3 font-medium">No orders found.</p>
                                <p class="text-xs text-muted-foreground">
                                    Try changing or resetting your filters.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="orders.length > 0"
                class="flex items-center justify-between border-t bg-muted/30 px-4 py-3 text-xs text-muted-foreground"
            >
                <span>
                    Showing
                    <span class="font-semibold text-foreground">{{ orders.length }}</span>
                    {{ orders.length === 1 ? 'order' : 'orders' }}
                </span>
                <span>
                    Total value
                    <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ formatTaka(summary.revenue) }}</span>
                </span>
            </div>
        </div>

        <!-- Mobile cards -->
        <div class="grid gap-3 md:hidden">
            <div
                v-for="order in orders"
                :key="`m-${order.id}`"
                class="rounded-xl border border-l-4 border-sidebar-border/70 bg-card p-4 shadow-sm dark:border-sidebar-border"
                :class="orderAccentClass(order.status)"
            >
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-semibold text-indigo-700 dark:text-indigo-300">
                            {{ order.order_number }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ formatDate(order.placed_at) }}
                        </p>
                    </div>
                    <Badge
                        variant="outline"
                        class="gap-1.5 capitalize"
                        :class="orderStatusClass(order.status)"
                    >
                        <span
                            class="size-1.5 rounded-full"
                            :class="orderDotClass(order.status)"
                        />
                        {{ order.status }}
                    </Badge>
                </div>

                <div class="mt-3 flex items-center gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 text-xs font-semibold text-white"
                    >
                        {{ initials(order.customer_name) }}
                    </div>
                    <div>
                        <p class="font-medium">{{ order.customer_name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ order.phone }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <Badge
                        variant="outline"
                        class="capitalize"
                        :class="paymentStatusClass(order.payment_status)"
                    >
                        {{ order.payment_status }}
                    </Badge>
                    <span class="text-xs text-muted-foreground">
                        {{ paymentMethodLabel(order.payment_method) }} ·
                        {{ order.items_count }} items
                    </span>
                </div>

                <div class="mt-4 flex items-center justify-between border-t pt-3">
                    <p class="font-semibold text-emerald-700 dark:text-emerald-400">
                        {{ formatTaka(order.total) }}
                    </p>
                    <Button
                        as-child
                        variant="outline"
                        size="sm"
                        class="gap-1.5 border-indigo-200 text-indigo-700 hover:bg-indigo-50 dark:border-indigo-500/30 dark:text-indigo-300"
                    >
                        <Link :href="show(order.id)">
                            <Eye class="size-4" />
                            View
                        </Link>
                    </Button>
                </div>
            </div>

            <div
                v-if="orders.length === 0"
                class="rounded-xl border bg-card px-4 py-12 text-center"
            >
                <div
                    class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"
                >
                    <PackageSearch class="size-6" />
                </div>
                <p class="mt-3 font-medium">No orders found.</p>
                <p class="text-xs text-muted-foreground">
                    Try changing or resetting your filters.
                </p>
            </div>
        </div>
    </div>
</template>