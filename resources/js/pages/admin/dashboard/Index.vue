<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    CircleCheck,
    ClipboardList,
    CreditCard,
    Eye,
    Heart,
    Layers,
    Package,
    ShoppingCart,
    TrendingUp,
    Trophy,
    Users,
    Wallet,
} from '@lucide/vue';
import DashboardRevenueChart from '@/components/admin/DashboardRevenueChart.vue';
import DashboardStatCard from '@/components/admin/DashboardStatCard.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatTaka } from '@/lib/shop/currency';
import { dashboard } from '@/routes';
import { index as ordersIndex, show as orderShow } from '@/routes/admin/orders';
import { index as productsIndex } from '@/routes/admin/products';
import type {
    AdminDashboardOverview,
    AdminDashboardPaymentMethodBreakdown,
    AdminDashboardRecentOrder,
    AdminDashboardRevenuePoint,
    AdminDashboardStatusBreakdown,
    AdminDashboardTopCategory,
    AdminDashboardTopProduct,
} from '@/types/admin';

defineProps<{
    overview: AdminDashboardOverview;
    revenue_chart: AdminDashboardRevenuePoint[];
    orders_by_status: AdminDashboardStatusBreakdown[];
    payment_status_breakdown: AdminDashboardStatusBreakdown[];
    payment_method_breakdown: AdminDashboardPaymentMethodBreakdown[];
    top_products: AdminDashboardTopProduct[];
    top_categories: AdminDashboardTopCategory[];
    recent_orders: AdminDashboardRecentOrder[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

function orderStatusVariant(
    status: AdminDashboardRecentOrder['status'],
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
    status: AdminDashboardRecentOrder['payment_status'],
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

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('en-BD', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function breakdownPercent(count: number, total: number): number {
    if (total <= 0) {
        return 0;
    }

    return Math.round((count / total) * 100);
}

/* ---------- Presentation-only helpers (styling & colors) ---------- */

// Shared surface style for every card: soft border, rounded, gentle lift on hover.
const surface =
    'gap-0 overflow-hidden rounded-2xl border-border/60 py-0 shadow-sm transition-shadow hover:shadow-md';
const surfaceHeader = 'border-b border-border/60 bg-muted/30 px-5 py-4';
const surfaceBody = 'px-5 py-5';

// Solid color for bars, dots and stacked segments.
function statusBarClass(key: string): string {
    switch (String(key).toLowerCase()) {
        case 'pending':
        case 'unpaid':
            return 'bg-amber-500';
        case 'processing':
        case 'confirmed':
            return 'bg-blue-500';
        case 'shipped':
        case 'out_for_delivery':
            return 'bg-violet-500';
        case 'delivered':
        case 'paid':
        case 'completed':
            return 'bg-emerald-500';
        case 'cancelled':
        case 'failed':
            return 'bg-red-500';
        case 'refunded':
            return 'bg-pink-500';
        case 'cod':
        case 'cash_on_delivery':
            return 'bg-orange-500';
        case 'sslcommerz':
            return 'bg-cyan-500';
        default:
            return 'bg-slate-400';
    }
}

// Soft tinted pills layered on top of the existing Badge variants.
function statusPillClass(key: string): string {
    switch (String(key).toLowerCase()) {
        case 'pending':
        case 'unpaid':
            return 'border-transparent bg-amber-500/15 text-amber-700 dark:text-amber-400';
        case 'processing':
        case 'confirmed':
            return 'border-transparent bg-blue-500/15 text-blue-700 dark:text-blue-400';
        case 'shipped':
        case 'out_for_delivery':
            return 'border-transparent bg-violet-500/15 text-violet-700 dark:text-violet-400';
        case 'delivered':
        case 'paid':
        case 'completed':
            return 'border-transparent bg-emerald-500/15 text-emerald-700 dark:text-emerald-400';
        case 'cancelled':
        case 'failed':
            return 'border-transparent bg-red-500/15 text-red-700 dark:text-red-400';
        case 'refunded':
            return 'border-transparent bg-pink-500/15 text-pink-700 dark:text-pink-400';
        default:
            return 'border-transparent bg-slate-500/15 text-slate-700 dark:text-slate-300';
    }
}

// Gold / silver / bronze for the top three, neutral after that.
function rankClass(index: number): string {
    switch (index) {
        case 0:
            return 'bg-amber-400/20 text-amber-700 ring-1 ring-amber-400/50 dark:text-amber-300';
        case 1:
            return 'bg-slate-400/20 text-slate-600 ring-1 ring-slate-400/50 dark:text-slate-300';
        case 2:
            return 'bg-orange-500/15 text-orange-700 ring-1 ring-orange-500/40 dark:text-orange-300';
        default:
            return 'bg-muted text-muted-foreground';
    }
}

const categoryBarClasses = [
    'bg-blue-500',
    'bg-emerald-500',
    'bg-violet-500',
    'bg-amber-500',
    'bg-pink-500',
    'bg-cyan-500',
];

function categoryBarClass(index: number): string {
    return categoryBarClasses[index % categoryBarClasses.length];
}

function maxCategoryCount(categories: AdminDashboardTopCategory[]): number {
    return categories.reduce(
        (max, category) => Math.max(max, category.products_count),
        0,
    );
}
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full min-w-0 flex-1 flex-col gap-6 rounded-xl bg-gradient-to-b from-muted/40 to-transparent p-4 md:p-6"
    >
        <!-- Page header -->
        <div
            class="relative overflow-hidden rounded-2xl border border-border/60 bg-card px-5 py-5 shadow-sm md:px-7"
        >
            <div
                class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-blue-500 via-violet-500 to-pink-500"
            />
            <div
                class="pointer-events-none absolute -top-24 right-0 size-72 rounded-full bg-gradient-to-br from-violet-500/15 to-blue-500/10 blur-3xl"
            />
            <div class="relative pl-2">
                <Heading
                    title="Dashboard"
                    description="Store performance and operational overview"
                />
            </div>
        </div>

        <!-- KPI row -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <div
                class="rounded-2xl border-t-2 border-t-emerald-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Total revenue"
                    :value="formatTaka(overview.total_revenue)"
                    :change-percent="overview.revenue_change_percent"
                    :icon="TrendingUp"
                    icon-class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                />
            </div>
            <div
                class="rounded-2xl border-t-2 border-t-blue-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Total orders"
                    :value="overview.total_orders.toLocaleString()"
                    :change-percent="overview.orders_change_percent"
                    :icon="ShoppingCart"
                    icon-class="bg-blue-500/10 text-blue-600 dark:text-blue-400"
                />
            </div>
            <div
                class="rounded-2xl border-t-2 border-t-cyan-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Total products"
                    :value="overview.total_products.toLocaleString()"
                    :description="`${overview.active_products} active products`"
                    :icon="Package"
                    icon-class="bg-cyan-500/10 text-cyan-600 dark:text-cyan-400"
                />
            </div>
            <div
                class="rounded-2xl border-t-2 border-t-violet-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Total customers"
                    :value="overview.total_customers.toLocaleString()"
                    :description="`${overview.new_customers_this_month} new this month`"
                    :icon="Users"
                    icon-class="bg-violet-500/10 text-violet-600 dark:text-violet-400"
                />
            </div>
            <div
                class="rounded-2xl border-t-2 border-t-amber-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Pending orders"
                    :value="overview.pending_orders.toLocaleString()"
                    description="Awaiting fulfillment"
                    :icon="ClipboardList"
                    icon-class="bg-amber-500/10 text-amber-600 dark:text-amber-400"
                />
            </div>
            <div
                class="rounded-2xl border-t-2 border-t-emerald-500 transition-transform hover:-translate-y-0.5"
            >
                <DashboardStatCard
                    title="Completed orders"
                    :value="overview.completed_orders.toLocaleString()"
                    description="Successfully delivered"
                    :icon="CircleCheck"
                    icon-class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                />
            </div>
        </div>

        <!-- Revenue + quick stats -->
        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <DashboardRevenueChart :data="revenue_chart" />
            </div>

            <Card :class="[surface, 'h-full']">
                <CardHeader :class="surfaceHeader">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-pink-500 text-white shadow-sm"
                        >
                            <ClipboardList class="size-4.5" />
                        </div>
                        <div>
                            <CardTitle>Quick stats</CardTitle>
                            <CardDescription>Operational snapshot</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent :class="[surfaceBody, 'grid gap-3']">
                    <div
                        class="flex items-center justify-between gap-3 rounded-xl border border-emerald-500/20 bg-gradient-to-r from-emerald-500/10 to-transparent p-3 transition-colors hover:border-emerald-500/40"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400"
                            >
                                <Package class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Active products</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ overview.total_products }} total listed
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-2xl font-bold text-emerald-600 tabular-nums dark:text-emerald-400"
                            >{{ overview.active_products }}</span
                        >
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 rounded-xl border border-red-500/20 bg-gradient-to-r from-red-500/10 to-transparent p-3 transition-colors hover:border-red-500/40"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-red-500/15 text-red-600 dark:text-red-400"
                            >
                                <AlertTriangle class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Out of stock</p>
                                <p class="text-xs text-muted-foreground">
                                    Products needing restock
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-2xl font-bold text-red-600 tabular-nums dark:text-red-400"
                            >{{ overview.out_of_stock_products }}</span
                        >
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 rounded-xl border border-pink-500/20 bg-gradient-to-r from-pink-500/10 to-transparent p-3 transition-colors hover:border-pink-500/40"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-pink-500/15 text-pink-600 dark:text-pink-400"
                            >
                                <Heart class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Wishlist items</p>
                                <p class="text-xs text-muted-foreground">
                                    Customer demand signals
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-2xl font-bold text-pink-600 tabular-nums dark:text-pink-400"
                            >{{ overview.total_wishlists }}</span
                        >
                    </div>

                    <div
                        class="flex items-center justify-between gap-3 rounded-xl border border-violet-500/20 bg-gradient-to-r from-violet-500/10 to-transparent p-3 transition-colors hover:border-violet-500/40"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-violet-500/15 text-violet-600 dark:text-violet-400"
                            >
                                <Wallet class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Average order value</p>
                                <p class="text-xs text-muted-foreground">
                                    Based on paid orders
                                </p>
                            </div>
                        </div>
                        <span
                            class="text-lg font-bold text-violet-600 tabular-nums dark:text-violet-400"
                            >{{ formatTaka(overview.average_order_value) }}</span
                        >
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Breakdowns -->
        <div class="grid gap-4 lg:grid-cols-3">
            <Card :class="surface">
                <CardHeader :class="surfaceHeader">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400"
                        >
                            <Layers class="size-4.5" />
                        </div>
                        <div>
                            <CardTitle>Orders by status</CardTitle>
                            <CardDescription>Fulfillment pipeline</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent :class="[surfaceBody, 'grid gap-4']">
                    <div
                        class="flex h-3 w-full gap-0.5 overflow-hidden rounded-full bg-muted"
                    >
                        <div
                            v-for="item in orders_by_status"
                            :key="`seg-${item.status}`"
                            class="h-full first:rounded-l-full last:rounded-r-full"
                            :class="statusBarClass(item.status)"
                            :style="{
                                width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                            }"
                        />
                    </div>
                    <div
                        v-for="item in orders_by_status"
                        :key="item.status"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span
                                    class="size-2.5 rounded-full"
                                    :class="statusBarClass(item.status)"
                                />
                                {{ item.label }}
                            </span>
                            <span class="flex items-baseline gap-2">
                                <span class="font-semibold tabular-nums">{{
                                    item.count
                                }}</span>
                                <span
                                    class="w-9 text-right text-xs text-muted-foreground tabular-nums"
                                    >{{
                                        breakdownPercent(
                                            item.count,
                                            overview.total_orders,
                                        )
                                    }}%</span
                                >
                            </span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="statusBarClass(item.status)"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card :class="surface">
                <CardHeader :class="surfaceHeader">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400"
                        >
                            <Wallet class="size-4.5" />
                        </div>
                        <div>
                            <CardTitle>Payment status</CardTitle>
                            <CardDescription>Collection health</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent :class="[surfaceBody, 'grid gap-4']">
                    <div
                        class="flex h-3 w-full gap-0.5 overflow-hidden rounded-full bg-muted"
                    >
                        <div
                            v-for="item in payment_status_breakdown"
                            :key="`seg-${item.status}`"
                            class="h-full first:rounded-l-full last:rounded-r-full"
                            :class="statusBarClass(item.status)"
                            :style="{
                                width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                            }"
                        />
                    </div>
                    <div
                        v-for="item in payment_status_breakdown"
                        :key="item.status"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span
                                    class="size-2.5 rounded-full"
                                    :class="statusBarClass(item.status)"
                                />
                                {{ item.label }}
                            </span>
                            <span class="flex items-baseline gap-2">
                                <span class="font-semibold tabular-nums">{{
                                    item.count
                                }}</span>
                                <span
                                    class="w-9 text-right text-xs text-muted-foreground tabular-nums"
                                    >{{
                                        breakdownPercent(
                                            item.count,
                                            overview.total_orders,
                                        )
                                    }}%</span
                                >
                            </span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="statusBarClass(item.status)"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card :class="surface">
                <CardHeader :class="surfaceHeader">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-violet-500/15 text-violet-600 dark:text-violet-400"
                        >
                            <CreditCard class="size-4.5" />
                        </div>
                        <div>
                            <CardTitle>Payment methods</CardTitle>
                            <CardDescription>How customers pay</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent :class="[surfaceBody, 'grid gap-4']">
                    <div
                        class="flex h-3 w-full gap-0.5 overflow-hidden rounded-full bg-muted"
                    >
                        <div
                            v-for="item in payment_method_breakdown"
                            :key="`seg-${item.method}`"
                            class="h-full first:rounded-l-full last:rounded-r-full"
                            :class="statusBarClass(item.method)"
                            :style="{
                                width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                            }"
                        />
                    </div>
                    <div
                        v-for="item in payment_method_breakdown"
                        :key="item.method"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span
                                    class="size-2.5 rounded-full"
                                    :class="statusBarClass(item.method)"
                                />
                                {{ item.label }}
                            </span>
                            <span class="flex items-baseline gap-2">
                                <span class="font-semibold tabular-nums">{{
                                    item.count
                                }}</span>
                                <span
                                    class="w-9 text-right text-xs text-muted-foreground tabular-nums"
                                    >{{
                                        breakdownPercent(
                                            item.count,
                                            overview.total_orders,
                                        )
                                    }}%</span
                                >
                            </span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="statusBarClass(item.method)"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Recent orders + top lists -->
        <div class="grid gap-4 xl:grid-cols-2">
            <Card :class="surface">
                <CardHeader
                    :class="[
                        surfaceHeader,
                        'flex flex-row items-center justify-between gap-4',
                    ]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-violet-500 text-white shadow-sm"
                        >
                            <ShoppingCart class="size-4.5" />
                        </div>
                        <div>
                            <CardTitle>Recent orders</CardTitle>
                            <CardDescription>Latest customer activity</CardDescription>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="ordersIndex()">
                            View all
                            <ArrowUpRight class="size-3.5" />
                        </Link>
                    </Button>
                </CardHeader>
                <CardContent class="overflow-x-auto px-2 py-2">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="text-left text-xs text-muted-foreground">
                                <th class="px-3 py-3 font-medium">Order</th>
                                <th class="px-3 py-3 font-medium">Customer</th>
                                <th class="px-3 py-3 font-medium">Total</th>
                                <th class="px-3 py-3 font-medium">Status</th>
                                <th class="px-3 py-3 font-medium">Placed</th>
                                <th class="px-3 py-3 font-medium">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            <tr
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="transition-colors hover:bg-blue-500/5"
                            >
                                <td class="px-3 py-3.5">
                                    <span
                                        class="rounded-md bg-primary/10 px-2 py-1 text-xs font-semibold text-primary"
                                    >
                                        {{ order.order_number }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 font-medium">
                                    {{ order.customer_name }}
                                </td>
                                <td
                                    class="px-3 py-3.5 font-semibold tabular-nums"
                                >
                                    {{ formatTaka(order.total) }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            :variant="
                                                orderStatusVariant(order.status)
                                            "
                                            :class="statusPillClass(order.status)"
                                        >
                                            {{ order.status }}
                                        </Badge>
                                        <Badge
                                            :variant="
                                                paymentStatusVariant(
                                                    order.payment_status,
                                                )
                                            "
                                            :class="
                                                statusPillClass(
                                                    order.payment_status,
                                                )
                                            "
                                        >
                                            {{ order.payment_status }}
                                        </Badge>
                                    </div>
                                </td>
                                <td
                                    class="px-3 py-3.5 text-xs whitespace-nowrap text-muted-foreground"
                                >
                                    {{ formatDate(order.placed_at) }}
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        class="hover:bg-blue-500/10 hover:text-blue-600"
                                        as-child
                                    >
                                        <Link :href="orderShow(order.id)">
                                            <Eye class="size-4" />
                                            <span class="sr-only">View order</span>
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="recent_orders.length === 0">
                                <td
                                    colspan="6"
                                    class="py-12 text-center text-muted-foreground"
                                >
                                    No orders yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <div class="grid gap-4">
                <Card :class="surface">
                    <CardHeader
                        :class="[
                            surfaceHeader,
                            'flex flex-row items-center justify-between gap-4',
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm"
                            >
                                <Trophy class="size-4.5" />
                            </div>
                            <div>
                                <CardTitle>Top products</CardTitle>
                                <CardDescription>By units sold</CardDescription>
                            </div>
                        </div>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="productsIndex()">
                                View all
                                <ArrowUpRight class="size-3.5" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent :class="[surfaceBody, 'grid gap-1']">
                        <div
                            v-for="(product, index) in top_products"
                            :key="product.id"
                            class="flex items-center justify-between gap-3 rounded-xl p-2.5 transition-colors hover:bg-amber-500/5"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                    :class="rankClass(index)"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium">
                                        {{ product.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ product.category_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="inline-block rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                                >
                                    {{ product.sold_count }} sold
                                </p>
                                <p
                                    class="mt-1 text-xs text-muted-foreground tabular-nums"
                                >
                                    {{ formatTaka(product.price) }}
                                </p>
                            </div>
                        </div>
                        <p
                            v-if="top_products.length === 0"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            No product sales data yet.
                        </p>
                    </CardContent>
                </Card>

                <Card :class="surface">
                    <CardHeader :class="surfaceHeader">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-cyan-500 text-white shadow-sm"
                            >
                                <Layers class="size-4.5" />
                            </div>
                            <div>
                                <CardTitle>Top categories</CardTitle>
                                <CardDescription>By product count</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent :class="[surfaceBody, 'grid gap-4']">
                        <div
                            v-for="(category, index) in top_categories"
                            :key="category.id"
                            class="grid gap-1.5 text-sm"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <span class="flex items-center gap-2 font-medium">
                                    <span
                                        class="size-2.5 rounded-full"
                                        :class="categoryBarClass(index)"
                                    />
                                    {{ category.name }}
                                </span>
                                <span
                                    class="rounded-full bg-muted px-2.5 py-0.5 text-xs text-muted-foreground"
                                >
                                    {{ category.products_count }} products
                                </span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full transition-all duration-500"
                                    :class="categoryBarClass(index)"
                                    :style="{
                                        width: `${breakdownPercent(category.products_count, maxCategoryCount(top_categories))}%`,
                                    }"
                                />
                            </div>
                        </div>
                        <p
                            v-if="top_categories.length === 0"
                            class="py-4 text-center text-sm text-muted-foreground"
                        >
                            No categories yet.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>