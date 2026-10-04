<script setup lang="ts">
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import {
    CheckCircle,
    Clock,
    Download,
    Mail,
    MapPin,
    Package,
    Phone,
    Printer,
    RotateCcw,
    Truck,
    User,
    XCircle,
} from '@lucide/vue';
import OrderController from '@/actions/App/Http/Controllers/Admin/OrderController';
import OrderStatusForm from '@/components/admin/OrderStatusForm.vue';
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
import { Separator } from '@/components/ui/separator';
import { formatTaka } from '@/lib/shop/currency';
import { dashboard } from '@/routes';
import { index, show } from '@/routes/admin/orders';
import type {
    AdminOrder,
    AdminStatusOption,
    OrderStatus,
    OrderUpdateFormData,
    PaymentStatus,
} from '@/types/admin';

const props = defineProps<{
    order: AdminOrder;
    statusOptions: AdminStatusOption[];
    paymentStatusOptions: AdminStatusOption[];
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Orders', href: index() },
        { title: props.order.order_number, href: show(props.order.id) },
    ],
});

const form = useForm<OrderUpdateFormData>({
    status: props.order.status,
    payment_status: props.order.payment_status,
    note: '',
});

function submit(): void {
    form.put(OrderController.update.url(props.order.id), {
        preserveScroll: true,
        onSuccess: () => form.reset('note'),
    });
}

const ORDER_STEPS: OrderStatus[] = [
    'pending',
    'processing',
    'shipped',
    'delivered',
];

type StepColors = {
    done: string;
    active: string;
    upcoming: string;
    icon: typeof Package;
    label: string;
};

const STEP_CONFIG: Record<string, StepColors> = {
    pending: {
        done: 'bg-amber-500 border-amber-500 text-white',
        active: 'bg-amber-50 border-amber-500 text-amber-600',
        upcoming: 'bg-background border-border text-muted-foreground',
        icon: Clock,
        label: 'Pending',
    },
    processing: {
        done: 'bg-blue-500 border-blue-500 text-white',
        active: 'bg-blue-50 border-blue-500 text-blue-600',
        upcoming: 'bg-background border-border text-muted-foreground',
        icon: RotateCcw,
        label: 'Processing',
    },
    shipped: {
        done: 'bg-violet-500 border-violet-500 text-white',
        active: 'bg-violet-50 border-violet-500 text-violet-600',
        upcoming: 'bg-background border-border text-muted-foreground',
        icon: Truck,
        label: 'Shipped',
    },
    delivered: {
        done: 'bg-emerald-500 border-emerald-500 text-white',
        active: 'bg-emerald-50 border-emerald-500 text-emerald-600',
        upcoming: 'bg-background border-border text-muted-foreground',
        icon: CheckCircle,
        label: 'Delivered',
    },
};

const TIMELINE_ICON_COLORS: Record<string, string> = {
    pending: 'bg-amber-100 border-amber-400 text-amber-600',
    processing: 'bg-blue-100 border-blue-400 text-blue-600',
    shipped: 'bg-violet-100 border-violet-400 text-violet-600',
    delivered: 'bg-emerald-100 border-emerald-400 text-emerald-600',
    cancelled: 'bg-red-100 border-red-400 text-red-600',
};

const STATUS_BADGE_CLASSES: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 border border-amber-300',
    processing: 'bg-blue-100 text-blue-700 border border-blue-300',
    shipped: 'bg-violet-100 text-violet-700 border border-violet-300',
    delivered: 'bg-emerald-100 text-emerald-700 border border-emerald-300',
    cancelled: 'bg-red-100 text-red-700 border border-red-300',
};

const PAYMENT_BADGE_CLASSES: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-700 border border-amber-300',
    paid: 'bg-emerald-100 text-emerald-700 border border-emerald-300',
    failed: 'bg-red-100 text-red-700 border border-red-300',
    cancelled: 'bg-red-100 text-red-700 border border-red-300',
};

function stepState(step: OrderStatus): 'done' | 'active' | 'upcoming' {
    if (props.order.status === 'cancelled') {
        return 'upcoming';
    }

    const currentIndex = ORDER_STEPS.indexOf(props.order.status);
    const stepIndex = ORDER_STEPS.indexOf(step);

    if (stepIndex < currentIndex) {
        return 'done';
    }

    if (stepIndex === currentIndex) {
        return 'active';
    }

    return 'upcoming';
}

function stepClasses(step: OrderStatus): string {
    return STEP_CONFIG[step]?.[stepState(step)] ?? '';
}

function timelineIconClasses(status: string): string {
    return TIMELINE_ICON_COLORS[status] ?? 'bg-muted border-border text-muted-foreground';
}

function statusBadgeClasses(status: string): string {
    return STATUS_BADGE_CLASSES[status] ?? 'bg-muted text-foreground border';
}

function paymentBadgeClasses(status: string): string {
    return PAYMENT_BADGE_CLASSES[status] ?? 'bg-muted text-foreground border';
}

function paymentMethodLabel(method: AdminOrder['payment_method']): string {
    return method === 'cod' ? 'Cash on Delivery' : 'SSLCommerz';
}

function formatDate(value: string | null): string {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString('en-BD', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function printInvoice(): void {
    window.print();
}
</script>

<template>
    <Head :title="order.order_number" />

    <!-- ─── Screen view ─────────────────────────────────────────────────── -->
    <div
        id="order-screen"
        class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4"
    >
        <!-- Header bar -->
        <div class="flex flex-col gap-4 rounded-2xl bg-gradient-to-r from-violet-600 via-blue-600 to-indigo-600 p-5 text-white shadow-md sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="mb-1 text-xs font-semibold uppercase tracking-widest text-violet-200">
                    Order
                </p>
                <h1 class="text-2xl font-bold tracking-tight">
                    {{ order.order_number }}
                </h1>
                <p class="mt-0.5 text-sm text-violet-200">
                    Placed {{ formatDate(order.placed_at) }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                    :class="statusBadgeClasses(order.status)"
                    style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3)"
                >
                    {{ order.status }}
                </span>
                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                    style="background: rgba(255,255,255,0.15); color: white; border: 1px solid rgba(255,255,255,0.3)"
                >
                    {{ order.payment_status }}
                </span>
                <Separator orientation="vertical" class="mx-1 h-6 bg-white/30" />
                <Button
                    variant="secondary"
                    size="sm"
                    class="bg-white/20 text-white hover:bg-white/30 border-white/30"
                    @click="printInvoice"
                >
                    <Printer class="size-4" />
                    Print
                </Button>
                <Button
                    variant="secondary"
                    size="sm"
                    class="bg-white/20 text-white hover:bg-white/30 border-white/30"
                    @click="printInvoice"
                >
                    <Download class="size-4" />
                    Save PDF
                </Button>
            </div>
        </div>

        <!-- Fulfillment stepper -->
        <Card v-if="order.status !== 'cancelled'" class="border-none shadow-sm bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/40 dark:to-background">
            <CardContent class="pt-6 pb-5">
                <div class="relative flex items-start justify-between">
                    <!-- Background connector -->
                    <div
                        class="absolute top-5 right-0 left-0 mx-[calc(12.5%+20px)] h-0.5 bg-border"
                        aria-hidden="true"
                    />
                    <div
                        v-for="step in ORDER_STEPS"
                        :key="step"
                        class="relative flex flex-1 flex-col items-center gap-2"
                    >
                        <div
                            :class="[
                                'relative z-10 flex size-10 items-center justify-center rounded-full border-2 transition-all duration-300',
                                stepClasses(step),
                            ]"
                        >
                            <component :is="STEP_CONFIG[step].icon" class="size-4" />
                        </div>
                        <span
                            :class="[
                                'text-xs font-semibold capitalize',
                                stepState(step) === 'upcoming'
                                    ? 'text-muted-foreground'
                                    : 'text-foreground',
                            ]"
                        >
                            {{ STEP_CONFIG[step].label }}
                        </span>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Cancelled banner -->
        <div
            v-if="order.status === 'cancelled'"
            class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400"
        >
            <XCircle class="size-5 shrink-0 text-red-500" />
            <p>
                This order has been <strong>cancelled</strong>.
                Payment status: <strong class="capitalize">{{ order.payment_status }}</strong>.
            </p>
        </div>

        <!-- Main content grid -->
        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <!-- Left column -->
            <div class="grid auto-rows-min gap-6">
                <!-- Line items -->
                <Card class="overflow-hidden border-none shadow-sm">
                    <CardHeader class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950/30 dark:to-blue-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                                <Package class="size-4 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            Line items
                        </CardTitle>
                        <CardDescription>
                            {{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }} in this order
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <table class="w-full min-w-[540px] text-sm">
                            <thead class="bg-indigo-600 text-white">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-indigo-100 text-xs uppercase tracking-wide">Product</th>
                                    <th class="px-4 py-3 text-center font-semibold text-indigo-100 text-xs uppercase tracking-wide">Qty</th>
                                    <th class="px-4 py-3 text-right font-semibold text-indigo-100 text-xs uppercase tracking-wide">Unit price</th>
                                    <th class="px-5 py-3 text-right font-semibold text-indigo-100 text-xs uppercase tracking-wide">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(item, i) in order.items"
                                    :key="item.id"
                                    :class="['border-b last:border-b-0', i % 2 === 0 ? 'bg-white dark:bg-background' : 'bg-indigo-50/40 dark:bg-indigo-950/10']"
                                >
                                    <td class="px-5 py-4 font-medium">{{ item.product_name }}</td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300">
                                            {{ item.quantity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-muted-foreground">{{ formatTaka(item.unit_price) }}</td>
                                    <td class="px-5 py-4 text-right font-semibold">{{ formatTaka(item.line_total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t bg-slate-50 dark:bg-slate-900/30">
                                    <td colspan="3" class="px-5 py-2.5 text-right text-xs text-muted-foreground">Subtotal</td>
                                    <td class="px-5 py-2.5 text-right text-sm font-medium">{{ formatTaka(order.subtotal) }}</td>
                                </tr>
                                <tr class="bg-slate-50 dark:bg-slate-900/30">
                                    <td colspan="3" class="px-5 py-2.5 text-right text-xs text-muted-foreground">Delivery charge</td>
                                    <td class="px-5 py-2.5 text-right text-sm font-medium">{{ formatTaka(order.delivery_charge) }}</td>
                                </tr>
                                <tr class="bg-indigo-600">
                                    <td colspan="3" class="px-5 py-3.5 text-right text-sm font-bold text-indigo-100">Order total</td>
                                    <td class="px-5 py-3.5 text-right text-base font-extrabold text-white">{{ formatTaka(order.total) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </CardContent>
                </Card>

                <!-- Status timeline -->
                <Card class="border-none shadow-sm">
                    <CardHeader class="bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-violet-700 dark:text-violet-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/50">
                                <Clock class="size-4 text-violet-600 dark:text-violet-400" />
                            </div>
                            Status history
                        </CardTitle>
                        <CardDescription>Chronological log of all status changes</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div v-if="order.status_histories.length > 0" class="relative">
                            <div
                                class="absolute top-5 bottom-5 left-[19px] w-0.5 bg-gradient-to-b from-violet-300 via-blue-200 to-transparent"
                                aria-hidden="true"
                            />
                            <div
                                v-for="(history, i) in order.status_histories"
                                :key="history.id"
                                class="relative flex gap-4 pb-6 last:pb-0"
                            >
                                <div
                                    :class="[
                                        'relative z-10 mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-full border-2 transition-colors',
                                        i === 0 ? timelineIconClasses(history.status) : 'bg-muted/50 border-border text-muted-foreground',
                                    ]"
                                >
                                    <component :is="STEP_CONFIG[history.status]?.icon ?? Clock" class="size-4" />
                                </div>
                                <div class="flex flex-1 flex-col gap-1 pt-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', statusBadgeClasses(history.status)]"
                                        >
                                            {{ history.status }}
                                        </span>
                                        <span class="text-xs text-muted-foreground">
                                            {{ formatDate(history.created_at) }}
                                        </span>
                                    </div>
                                    <p v-if="history.note" class="text-sm leading-relaxed">{{ history.note }}</p>
                                    <p v-if="history.changed_by" class="text-xs text-muted-foreground">
                                        Updated by
                                        <span class="font-medium text-foreground">{{ history.changed_by.name }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No status history yet.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Right column -->
            <div class="grid auto-rows-min gap-6">
                <!-- Customer -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <CardHeader class="bg-gradient-to-r from-sky-50 to-cyan-50 dark:from-sky-950/30 dark:to-cyan-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-sky-700 dark:text-sky-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/50">
                                <User class="size-4 text-sky-600 dark:text-sky-400" />
                            </div>
                            Customer
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 pt-5 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-cyan-500 text-white shadow-sm">
                                <User class="size-4" />
                            </div>
                            <div>
                                <p class="font-semibold">{{ order.customer_name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.customer ? 'Registered account' : 'Guest checkout' }}
                                </p>
                            </div>
                        </div>
                        <Separator />
                        <div class="flex items-center gap-3">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-sky-100 dark:bg-sky-900/40">
                                <Phone class="size-3.5 text-sky-600 dark:text-sky-400" />
                            </div>
                            <span>{{ order.phone }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-sky-100 dark:bg-sky-900/40">
                                <Mail class="size-3.5 text-sky-600 dark:text-sky-400" />
                            </div>
                            <span class="break-all">{{ order.email }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Shipping -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <CardHeader class="bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-950/30 dark:to-amber-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-orange-700 dark:text-orange-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/50">
                                <MapPin class="size-4 text-orange-600 dark:text-orange-400" />
                            </div>
                            Shipping address
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 pt-5 text-sm">
                        <div class="rounded-xl bg-orange-50/60 p-4 dark:bg-orange-950/20">
                            <p class="font-semibold leading-relaxed">
                                {{ order.address }},<br />
                                {{ order.area }}, {{ order.district }}
                            </p>
                        </div>
                        <div
                            v-if="order.notes"
                            class="rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/40 dark:bg-amber-950/20"
                        >
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">
                                Delivery note
                            </p>
                            <p class="text-sm">{{ order.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Payment summary -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <CardHeader class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/50">
                                <Package class="size-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            Payment summary
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 pt-5 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Method</span>
                            <span class="font-medium">{{ paymentMethodLabel(order.payment_method) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Payment status</span>
                            <span
                                :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', paymentBadgeClasses(order.payment_status)]"
                            >
                                {{ order.payment_status }}
                            </span>
                        </div>
                        <Separator />
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ formatTaka(order.subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted-foreground">Delivery charge</span>
                            <span>{{ formatTaka(order.delivery_charge) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-emerald-600 px-4 py-3 text-white">
                            <span class="font-semibold">Total</span>
                            <span class="text-lg font-extrabold">{{ formatTaka(order.total) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-muted-foreground">
                            <div>
                                <p class="mb-0.5 font-medium">Placed</p>
                                <p>{{ formatDate(order.placed_at) }}</p>
                            </div>
                            <div>
                                <p class="mb-0.5 font-medium">Last updated</p>
                                <p>{{ formatDate(order.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Update order -->
                <Card class="border-none shadow-sm overflow-hidden">
                    <CardHeader class="bg-gradient-to-r from-violet-50 to-indigo-50 dark:from-violet-950/30 dark:to-indigo-950/30 border-b">
                        <CardTitle class="flex items-center gap-2 text-violet-700 dark:text-violet-400">
                            <div class="flex size-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/50">
                                <RotateCcw class="size-4 text-violet-600 dark:text-violet-400" />
                            </div>
                            Update order
                        </CardTitle>
                        <CardDescription>Change fulfillment or payment status</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-5">
                        <form @submit.prevent="submit">
                            <OrderStatusForm
                                :form="form"
                                :status-options="statusOptions"
                                :payment-status-options="paymentStatusOptions"
                            />
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

        <!-- ─── Printable Invoice (hidden on screen, shown on print) ───────── -->
    <div id="invoice-print">
        <!-- Header -->
        <header class="inv-header">
            <div class="inv-brand">
                <h1>ShopEase</h1>
                <p>Your trusted online store</p>
                <p>support@shopease.com.bd</p>
            </div>
            <div class="inv-title">
                <h2>Invoice</h2>
                <p class="inv-number">{{ order.order_number }}</p>
            </div>
        </header>

        <!-- Meta strip -->
        <section class="inv-meta">
            <div class="inv-meta-item">
                <span class="inv-label">Order date</span>
                <span class="inv-value">{{ formatDate(order.placed_at) }}</span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Payment method</span>
                <span class="inv-value">{{ paymentMethodLabel(order.payment_method) }}</span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Order status</span>
                <span class="inv-value inv-capitalize">{{ order.status }}</span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Payment status</span>
                <span :class="['inv-chip', `inv-chip--${order.payment_status}`]">
                    {{ order.payment_status }}
                </span>
            </div>
        </section>

        <!-- Parties -->
        <section class="inv-parties">
            <div class="inv-party">
                <h3>Billed to</h3>
                <p class="inv-party-name">{{ order.customer_name }}</p>
                <p>{{ order.phone }}</p>
                <p>{{ order.email }}</p>
            </div>
            <div class="inv-party">
                <h3>Ship to</h3>
                <p>{{ order.address }}</p>
                <p>{{ order.area }}, {{ order.district }}</p>
                <p v-if="order.notes" class="inv-note">Note: {{ order.notes }}</p>
            </div>
        </section>

        <!-- Items -->
        <table class="inv-items">
            <thead>
                <tr>
                    <th class="inv-col-no">#</th>
                    <th>Description</th>
                    <th class="inv-right">Unit price</th>
                    <th class="inv-center">Qty</th>
                    <th class="inv-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, idx) in order.items" :key="item.id">
                    <td class="inv-col-no">{{ idx + 1 }}</td>
                    <td class="inv-product">{{ item.product_name }}</td>
                    <td class="inv-right">{{ formatTaka(item.unit_price) }}</td>
                    <td class="inv-center">{{ item.quantity }}</td>
                    <td class="inv-right inv-strong">{{ formatTaka(item.line_total) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <section class="inv-totals-wrap">
            <div class="inv-thanks">
                <p class="inv-label">Thank you</p>
                <p>We appreciate your business and hope to serve you again soon.</p>
            </div>
            <table class="inv-totals">
                <tbody>
                    <tr>
                        <td>Subtotal</td>
                        <td class="inv-right">{{ formatTaka(order.subtotal) }}</td>
                    </tr>
                    <tr>
                        <td>Delivery charge</td>
                        <td class="inv-right">{{ formatTaka(order.delivery_charge) }}</td>
                    </tr>
                    <tr class="inv-grand">
                        <td>Grand total</td>
                        <td class="inv-right">{{ formatTaka(order.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Footer -->
        <footer class="inv-footer">
            <p>Thank you for shopping with <strong>ShopEase</strong>!</p>
            <p>Questions? Email us at <strong>support@shopease.com.bd</strong></p>
            <p class="inv-dev">Developed by <strong>Jabed Hosen</strong></p>
        </footer>
    </div>
</template>

<style scoped>
#invoice-print {
    display: none;
}

@media print {
    @page {
        size: A4;
        margin: 14mm;
    }

    #order-screen {
        display: none !important;
    }

    #invoice-print {
        display: block;
        font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        font-size: 12px;
        line-height: 1.5;
        color: #1f2937;
        max-width: 800px;
        margin: 0 auto;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #invoice-print p {
        margin: 0;
    }

    .inv-right { text-align: right; }
    .inv-center { text-align: center; }
    .inv-strong { font-weight: 600; color: #111827; }
    .inv-capitalize { text-transform: capitalize; }

    /* ── Header ─────────────────────────────────────────────── */
    .inv-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 18px;
        border-bottom: 3px solid #4338ca;
    }

    .inv-brand h1 {
        margin: 0 0 4px;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: #4338ca;
    }

    .inv-brand p {
        font-size: 11.5px;
        color: #6b7280;
    }

    .inv-title {
        text-align: right;
    }

    .inv-title h2 {
        margin: 0 0 6px;
        font-size: 26px;
        font-weight: 300;
        letter-spacing: 6px;
        text-transform: uppercase;
        color: #111827;
    }

    .inv-number {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: #4338ca;
    }

    /* ── Meta strip ─────────────────────────────────────────── */
    .inv-meta {
        display: flex;
        margin-top: 18px;
        background: #f5f5fb;
        border: 1px solid #e5e7f0;
        border-radius: 8px;
    }

    .inv-meta-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 12px 16px;
        border-right: 1px solid #e5e7f0;
    }

    .inv-meta-item:last-child {
        border-right: none;
    }

    .inv-label {
        font-size: 9.5px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #6b7280;
    }

    .inv-value {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
    }

    .inv-chip {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
        background: #e5e7eb;
        color: #374151;
    }

    .inv-chip--paid { background: #d1fae5; color: #065f46; }
    .inv-chip--pending { background: #fef3c7; color: #92400e; }
    .inv-chip--failed,
    .inv-chip--cancelled { background: #fee2e2; color: #991b1b; }

    /* ── Parties ────────────────────────────────────────────── */
    .inv-parties {
        display: flex;
        gap: 24px;
        margin: 22px 0;
    }

    .inv-party {
        flex: 1;
        padding-left: 12px;
        border-left: 3px solid #c7d2fe;
    }

    .inv-party:last-child {
        border-left-color: #fed7aa;
    }

    .inv-party h3 {
        margin: 0 0 6px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #4338ca;
    }

    .inv-party:last-child h3 {
        color: #c2410c;
    }

    .inv-party p {
        font-size: 12px;
        color: #374151;
    }

    .inv-party-name {
        font-size: 14px !important;
        font-weight: 700;
        color: #111827 !important;
    }

    .inv-note {
        margin-top: 6px !important;
        font-size: 11px !important;
        font-style: italic;
        color: #6b7280 !important;
    }

    /* ── Items table ────────────────────────────────────────── */
    .inv-items {
        width: 100%;
        border-collapse: collapse;
    }

    .inv-items thead th {
        padding: 10px 12px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        text-align: left;
        color: #ffffff;
        background: #4338ca;
    }

    .inv-items thead th.inv-right { text-align: right; }
    .inv-items thead th.inv-center { text-align: center; }

    .inv-items tbody td {
        padding: 11px 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }

    .inv-items tbody tr {
        page-break-inside: avoid;
    }

    .inv-items tbody tr:nth-child(even) td {
        background: #fafafe;
    }

    .inv-col-no {
        width: 32px;
        color: #9ca3af;
    }

    .inv-product {
        font-weight: 600;
        color: #111827;
    }

    /* ── Totals ─────────────────────────────────────────────── */
    .inv-totals-wrap {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 32px;
        margin-top: 18px;
        page-break-inside: avoid;
    }

    .inv-thanks {
        flex: 1;
        max-width: 300px;
        padding-top: 4px;
    }

    .inv-thanks p:last-child {
        margin-top: 4px;
        font-size: 11.5px;
        color: #6b7280;
    }

    .inv-totals {
        width: 280px;
        border-collapse: collapse;
    }

    .inv-totals td {
        padding: 7px 12px;
        font-size: 12px;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
    }

    .inv-totals .inv-grand td {
        padding: 12px;
        font-size: 15px;
        font-weight: 800;
        color: #ffffff;
        background: #4338ca;
        border-bottom: none;
    }

    /* ── Footer ─────────────────────────────────────────────── */
    .inv-footer {
        margin-top: 36px;
        padding-top: 14px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        font-size: 11px;
        line-height: 1.8;
        color: #6b7280;
        page-break-inside: avoid;
    }

    .inv-footer strong {
        color: #111827;
    }

    .inv-dev {
        margin-top: 4px !important;
        font-size: 10px;
        color: #9ca3af;
    }
}
</style>
