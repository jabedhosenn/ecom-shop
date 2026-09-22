<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Eye,
    Heart,
    Package,
    ShoppingCart,
    TrendingUp,
    Users,
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
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading
            title="Dashboard"
            description="Store performance and operational overview"
        />

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <DashboardStatCard
                title="Total revenue"
                :value="formatTaka(overview.total_revenue)"
                :change-percent="overview.revenue_change_percent"
                :icon="TrendingUp"
                icon-class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
            />
            <DashboardStatCard
                title="Total orders"
                :value="overview.total_orders.toLocaleString()"
                :change-percent="overview.orders_change_percent"
                :icon="ShoppingCart"
                icon-class="bg-blue-500/10 text-blue-600 dark:text-blue-400"
            />
            <DashboardStatCard
                title="Average order value"
                :value="formatTaka(overview.average_order_value)"
                description="Based on paid orders"
                :icon="TrendingUp"
                icon-class="bg-violet-500/10 text-violet-600 dark:text-violet-400"
            />
            <DashboardStatCard
                title="Customers"
                :value="overview.total_customers.toLocaleString()"
                :description="`${overview.new_customers_this_month} new this month`"
                :icon="Users"
                icon-class="bg-amber-500/10 text-amber-600 dark:text-amber-400"
            />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <DashboardRevenueChart :data="revenue_chart" />
            </div>

            <Card class="h-full">
                <CardHeader>
                    <CardTitle>Quick stats</CardTitle>
                    <CardDescription>Operational snapshot</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400"
                            >
                                <ShoppingCart class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Pending orders</p>
                                <p class="text-xs text-muted-foreground">
                                    Awaiting fulfillment
                                </p>
                            </div>
                        </div>
                        <span class="text-lg font-bold">{{
                            overview.pending_orders
                        }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Package class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Active products</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ overview.total_products }} total listed
                                </p>
                            </div>
                        </div>
                        <span class="text-lg font-bold">{{
                            overview.active_products
                        }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-red-500/10 text-red-600 dark:text-red-400"
                            >
                                <AlertTriangle class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Out of stock</p>
                                <p class="text-xs text-muted-foreground">
                                    Products needing restock
                                </p>
                            </div>
                        </div>
                        <span class="text-lg font-bold">{{
                            overview.out_of_stock_products
                        }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-pink-500/10 text-pink-600 dark:text-pink-400"
                            >
                                <Heart class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">Wishlist items</p>
                                <p class="text-xs text-muted-foreground">
                                    Customer demand signals
                                </p>
                            </div>
                        </div>
                        <span class="text-lg font-bold">{{
                            overview.total_wishlists
                        }}</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <Card>
                <CardHeader>
                    <CardTitle>Orders by status</CardTitle>
                    <CardDescription>Fulfillment pipeline</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div
                        v-for="item in orders_by_status"
                        :key="item.status"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ item.label }}</span>
                            <span class="font-medium">{{ item.count }}</span>
                        </div>
                        <div
                            class="h-2 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary transition-all"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Payment status</CardTitle>
                    <CardDescription>Collection health</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div
                        v-for="item in payment_status_breakdown"
                        :key="item.status"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ item.label }}</span>
                            <span class="font-medium">{{ item.count }}</span>
                        </div>
                        <div
                            class="h-2 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-emerald-500 transition-all"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Payment methods</CardTitle>
                    <CardDescription>How customers pay</CardDescription>
                </CardHeader>
                <CardContent class="grid gap-3">
                    <div
                        v-for="item in payment_method_breakdown"
                        :key="item.method"
                        class="grid gap-1.5"
                    >
                        <div class="flex items-center justify-between text-sm">
                            <span>{{ item.label }}</span>
                            <span class="font-medium">{{ item.count }}</span>
                        </div>
                        <div
                            class="h-2 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-violet-500 transition-all"
                                :style="{
                                    width: `${breakdownPercent(item.count, overview.total_orders)}%`,
                                }"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between gap-4"
                >
                    <div>
                        <CardTitle>Recent orders</CardTitle>
                        <CardDescription>Latest customer activity</CardDescription>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="ordersIndex()">View all</Link>
                    </Button>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead>
                            <tr class="border-b text-left text-muted-foreground">
                                <th class="pb-3 pr-4 font-medium">Order</th>
                                <th class="pb-3 pr-4 font-medium">Customer</th>
                                <th class="pb-3 pr-4 font-medium">Total</th>
                                <th class="pb-3 pr-4 font-medium">Status</th>
                                <th class="pb-3 font-medium">Placed</th>
                                <th class="pb-3 pl-4 font-medium">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="order in recent_orders"
                                :key="order.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-3 pr-4 font-medium">
                                    {{ order.order_number }}
                                </td>
                                <td class="py-3 pr-4">
                                    {{ order.customer_name }}
                                </td>
                                <td class="py-3 pr-4">
                                    {{ formatTaka(order.total) }}
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <Badge
                                            :variant="
                                                orderStatusVariant(order.status)
                                            "
                                        >
                                            {{ order.status }}
                                        </Badge>
                                        <Badge
                                            :variant="
                                                paymentStatusVariant(
                                                    order.payment_status,
                                                )
                                            "
                                        >
                                            {{ order.payment_status }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="py-3 text-muted-foreground">
                                    {{ formatDate(order.placed_at) }}
                                </td>
                                <td class="py-3 pl-4 text-right">
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
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
                                    class="py-8 text-center text-muted-foreground"
                                >
                                    No orders yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <div class="grid gap-4">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between gap-4"
                    >
                        <div>
                            <CardTitle>Top products</CardTitle>
                            <CardDescription>By units sold</CardDescription>
                        </div>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="productsIndex()">View all</Link>
                        </Button>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div
                            v-for="(product, index) in top_products"
                            :key="product.id"
                            class="flex items-center justify-between gap-3"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold"
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
                                <p class="text-sm font-semibold">
                                    {{ product.sold_count }} sold
                                </p>
                                <p class="text-xs text-muted-foreground">
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

                <Card>
                    <CardHeader>
                        <CardTitle>Top categories</CardTitle>
                        <CardDescription>By product count</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-3">
                        <div
                            v-for="category in top_categories"
                            :key="category.id"
                            class="flex items-center justify-between gap-3 text-sm"
                        >
                            <span class="font-medium">{{ category.name }}</span>
                            <span class="text-muted-foreground">
                                {{ category.products_count }} products
                            </span>
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
