<script setup lang="ts">
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import {
    Banknote,
    CalendarClock,
    CheckCircle,
    Clock,
    CreditCard,
    Download,
    Mail,
    MapPin,
    Package,
    Phone,
    Printer,
    RotateCcw,
    Truck,
    User,
    Wallet,
    XCircle,
} from '@lucide/vue';
import OrderController from '@/actions/App/Http/Controllers/Admin/OrderController';
import OrderStatusForm from '@/components/admin/OrderStatusForm.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
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
    AdminInvoiceStore,
    AdminStatusOption,
    OrderStatus,
    OrderUpdateFormData,
} from '@/types/admin';

const props = defineProps<{
    order: AdminOrder;
    invoiceStore: AdminInvoiceStore;
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
        class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6"
    >
        <!-- Hero card -->
        <div
            class="overflow-hidden rounded-2xl border bg-card shadow-sm"
        >
            <div class="h-1.5 bg-gradient-to-r from-teal-400 via-emerald-500 to-cyan-500" />

            <div class="flex flex-col gap-5 p-5 md:p-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600 ring-1 ring-teal-100 dark:bg-teal-500/10 dark:text-teal-300 dark:ring-teal-500/20"
                    >
                        <Package class="size-7" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                            Order
                        </p>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">
                            {{ order.order_number }}
                        </h1>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <span
                                :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', statusBadgeClasses(order.status)]"
                            >
                                <Truck class="size-3" />
                                {{ order.status }}
                            </span>
                            <span
                                :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize', paymentBadgeClasses(order.payment_status)]"
                            >
                                <Wallet class="size-3" />
                                {{ order.payment_status }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                <CalendarClock class="size-3.5" />
                                {{ formatDate(order.placed_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        size="sm"
                        class="gap-2 bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200"
                        @click="printInvoice"
                    >
                        <Printer class="size-4" />
                        Print
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        class="gap-2 border-teal-200 text-teal-700 hover:bg-teal-50 hover:text-teal-800 dark:border-teal-500/30 dark:text-teal-300 dark:hover:bg-teal-500/10"
                        @click="printInvoice"
                    >
                        <Download class="size-4" />
                        Save PDF
                    </Button>
                </div>
            </div>

            <!-- Progress tracker -->
            <div
                v-if="order.status !== 'cancelled'"
                class="border-t bg-muted/20 px-5 py-6 md:px-8"
            >
                <div class="relative flex items-start justify-between">
                    <div
                        class="absolute left-[12.5%] right-[12.5%] top-[18px] h-1 rounded-full bg-border"
                        aria-hidden="true"
                    >
                        <div
                            class="h-full rounded-full bg-gradient-to-r from-teal-400 to-emerald-500 transition-all duration-500"
                            :style="{
                                width: `${(Math.max(ORDER_STEPS.indexOf(order.status), 0) / (ORDER_STEPS.length - 1)) * 100}%`,
                            }"
                        />
                    </div>
                    <div
                        v-for="step in ORDER_STEPS"
                        :key="step"
                        class="relative flex flex-1 flex-col items-center gap-2 text-center"
                    >
                        <div
                            :class="[
                                'relative z-10 flex size-9 items-center justify-center rounded-full border-2 transition-all duration-300',
                                stepClasses(step),
                                stepState(step) === 'active'
                                    ? 'ring-4 ring-teal-500/15'
                                    : '',
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
            </div>

            <!-- Cancelled banner -->
            <div
                v-else
                class="flex items-center gap-3 border-t border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-400"
            >
                <XCircle class="size-5 shrink-0 text-red-500" />
                <p>
                    This order has been <strong>cancelled</strong>.
                    Payment status: <strong class="capitalize">{{ order.payment_status }}</strong>.
                </p>
            </div>
        </div>

        <!-- Main content grid -->
        <div class="grid items-start gap-6 xl:grid-cols-[1fr_380px]">
            <!-- Left column -->
            <div class="grid auto-rows-min gap-6">
                <!-- Line items -->
                <Card class="overflow-hidden rounded-2xl shadow-sm">
                    <CardHeader class="border-b">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <CardTitle class="flex items-center gap-2.5">
                                    <span class="flex size-8 items-center justify-center rounded-lg bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300">
                                        <Package class="size-4" />
                                    </span>
                                    Line items
                                </CardTitle>
                                <CardDescription class="mt-1">
                                    {{ order.items_count }} {{ order.items_count === 1 ? 'item' : 'items' }} in this order
                                </CardDescription>
                            </div>
                            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-teal-700 dark:bg-teal-500/10 dark:text-teal-300">
                                {{ formatTaka(order.total) }}
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <ul class="divide-y">
                            <li
                                v-for="(item, i) in order.items"
                                :key="item.id"
                                class="flex items-center gap-4 px-5 py-4 transition-colors hover:bg-muted/30"
                            >
                                <div
                                    class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 text-sm font-bold text-white shadow-sm"
                                >
                                    {{ i + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold">{{ item.product_name }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                        {{ formatTaka(item.unit_price) }} each
                                    </p>
                                </div>
                                <span
                                    class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-slate-500/20 dark:text-slate-200"
                                >
                                    × {{ item.quantity }}
                                </span>
                                <p class="w-28 text-right font-bold text-emerald-700 dark:text-emerald-400">
                                    {{ formatTaka(item.line_total) }}
                                </p>
                            </li>
                        </ul>

                        <div class="grid gap-2 border-t bg-muted/20 px-5 py-4 text-sm">
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span>Subtotal</span>
                                <span class="font-medium text-foreground">{{ formatTaka(order.subtotal) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-muted-foreground">
                                <span>Delivery charge</span>
                                <span class="font-medium text-foreground">{{ formatTaka(order.delivery_charge) }}</span>
                            </div>
                            <Separator class="my-1" />
                            <div class="flex items-center justify-between">
                                <span class="font-semibold">Order total</span>
                                <span class="text-xl font-extrabold text-teal-700 dark:text-teal-300">{{ formatTaka(order.total) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Status timeline -->
                <Card class="overflow-hidden rounded-2xl shadow-sm">
                    <CardHeader class="border-b">
                        <CardTitle class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                                <Clock class="size-4" />
                            </span>
                            Status history
                        </CardTitle>
                        <CardDescription>Chronological log of all status changes</CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div v-if="order.status_histories.length > 0" class="relative">
                            <div
                                class="absolute bottom-5 left-[19px] top-5 w-px bg-border"
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

            <!-- Right column (sticky on large screens) -->
            <div class="grid auto-rows-min gap-6 xl:sticky xl:top-4">
                <!-- Update order -->
                <Card class="overflow-hidden rounded-2xl border-teal-200 shadow-sm dark:border-teal-500/30">
                    <CardHeader class="border-b bg-teal-50/60 dark:bg-teal-500/5">
                        <CardTitle class="flex items-center gap-2.5 text-teal-800 dark:text-teal-300">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300">
                                <RotateCcw class="size-4" />
                            </span>
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

                <!-- Customer -->
                <Card class="overflow-hidden rounded-2xl shadow-sm">
                    <CardHeader class="border-b">
                        <CardTitle class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                                <User class="size-4" />
                            </span>
                            Customer
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4 pt-5 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-blue-500 text-white shadow-sm">
                                <User class="size-5" />
                            </div>
                            <div>
                                <p class="font-semibold">{{ order.customer_name }}</p>
                                <p
                                    class="mt-0.5 inline-block rounded-full px-2 py-0.5 text-[11px] font-medium"
                                    :class="order.customer
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-300'"
                                >
                                    {{ order.customer ? 'Registered account' : 'Guest checkout' }}
                                </p>
                            </div>
                        </div>
                        <div class="grid gap-2">
                            <div class="flex items-center gap-3 rounded-lg border bg-muted/20 px-3 py-2.5">
                                <Phone class="size-4 text-sky-500" />
                                <span class="font-medium">{{ order.phone }}</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-lg border bg-muted/20 px-3 py-2.5">
                                <Mail class="size-4 text-sky-500" />
                                <span class="break-all font-medium">{{ order.email }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Shipping -->
                <Card class="overflow-hidden rounded-2xl shadow-sm">
                    <CardHeader class="border-b">
                        <CardTitle class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                                <MapPin class="size-4" />
                            </span>
                            Shipping address
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 pt-5 text-sm">
                        <div class="flex gap-3 rounded-xl border-l-4 border-orange-400 bg-orange-50/60 p-4 dark:bg-orange-500/5">
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
                <Card class="overflow-hidden rounded-2xl shadow-sm">
                    <CardHeader class="border-b">
                        <CardTitle class="flex items-center gap-2.5">
                            <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                                <Banknote
                                    v-if="order.payment_method === 'cod'"
                                    class="size-4"
                                />
                                <CreditCard v-else class="size-4" />
                            </span>
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
                        <div class="flex items-center justify-between rounded-xl bg-slate-900 px-4 py-3 text-white dark:bg-white dark:text-slate-900">
                            <span class="font-semibold">Total</span>
                            <span class="text-lg font-extrabold">{{ formatTaka(order.total) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-muted-foreground">
                            <div class="rounded-lg border bg-muted/20 p-2.5">
                                <p class="mb-0.5 font-medium text-foreground">Placed</p>
                                <p>{{ formatDate(order.placed_at) }}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/20 p-2.5">
                                <p class="mb-0.5 font-medium text-foreground">Last updated</p>
                                <p>{{ formatDate(order.updated_at) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>

    <!-- ─── Printable Invoice (hidden on screen, shown on print) ───────── -->
    <div id="invoice-print">
        <header class="inv-header">
            <div class="inv-brand">
                <div class="inv-brand-mark">
                    <AppLogoIcon class="inv-logo" aria-hidden="true" />
                    <h1>{{ invoiceStore.name }}</h1>
                </div>
                <p v-if="invoiceStore.email">{{ invoiceStore.email }}</p>
            </div>
            <div class="inv-title">
                <h2>Invoice</h2>
                <p class="inv-number">#{{ order.order_number }}</p>
                <p class="inv-date">Issued {{ formatDate(order.created_at) }}</p>
            </div>
        </header>

        <section class="inv-meta">
            <div class="inv-meta-item">
                <span class="inv-label">Order ID</span>
                <span class="inv-value">#{{ order.id }}</span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Order date</span>
                <span class="inv-value">
                    {{ formatDate(order.placed_at ?? order.created_at) }}
                </span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Payment method</span>
                <span class="inv-value">
                    {{ paymentMethodLabel(order.payment_method) }}
                </span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Payment status</span>
                <span :class="['inv-chip', `inv-chip--${order.payment_status}`]">
                    {{ order.payment_status }}
                </span>
            </div>
            <div class="inv-meta-item">
                <span class="inv-label">Order status</span>
                <span class="inv-value inv-capitalize">{{ order.status }}</span>
            </div>
        </section>

        <section class="inv-parties">
            <div class="inv-party">
                <h3>Billed to</h3>
                <p class="inv-party-name">{{ order.customer_name }}</p>
                <p>{{ order.email }}</p>
                <p>{{ order.phone }}</p>
            </div>
            <div class="inv-party">
                <h3>Billing / shipping address</h3>
                <p class="inv-party-name">{{ order.customer_name }}</p>
                <p>{{ order.address }}</p>
                <p>{{ order.area }}, {{ order.district }}</p>
                <p v-if="order.notes" class="inv-note">Note: {{ order.notes }}</p>
            </div>
        </section>

        <table class="inv-items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="inv-center">Qty</th>
                    <th class="inv-right">Unit price</th>
                    <th class="inv-right">Discount</th>
                    <th class="inv-right">Amount</th>
                </tr>
            </thead>
            <tbody v-if="order.items.length > 0">
                <tr v-for="item in order.items" :key="item.id">
                    <td class="inv-product">{{ item.product_name }}</td>
                    <td class="inv-center">{{ item.quantity }}</td>
                    <td class="inv-right">{{ formatTaka(item.unit_price) }}</td>
                    <td class="inv-right">
                        {{
                            item.discount_amount > 0
                                ? `−${formatTaka(item.discount_amount)}`
                                : '—'
                        }}
                    </td>
                    <td class="inv-right inv-strong">{{ formatTaka(item.line_total) }}</td>
                </tr>
            </tbody>
        </table>

        <section class="inv-totals-wrap">
            <div class="inv-thanks">
                <p class="inv-thanks-title">Thank you for your order</p>
                <p>
                    We appreciate your business and hope to serve you again
                    soon.
                </p>
            </div>
            <table class="inv-totals">
                <tbody>
                    <tr>
                        <td>Subtotal</td>
                        <td class="inv-right">{{ formatTaka(order.subtotal) }}</td>
                    </tr>
                    <tr v-if="order.coupon_code">
                        <td>Coupon ({{ order.coupon_code }})</td>
                        <td class="inv-right inv-discount">
                            −{{ formatTaka(order.discount_amount) }}
                        </td>
                    </tr>
                    <tr v-else-if="order.discount_amount > 0">
                        <td>Discount</td>
                        <td class="inv-right inv-discount">
                            −{{ formatTaka(order.discount_amount) }}
                        </td>
                    </tr>
                    <tr>
                        <td>Tax</td>
                        <td class="inv-right">Not charged</td>
                    </tr>
                    <tr>
                        <td>Shipping</td>
                        <td class="inv-right">{{ formatTaka(order.delivery_charge) }}</td>
                    </tr>
                    <tr class="inv-grand">
                        <td>Final total</td>
                        <td class="inv-right">{{ formatTaka(order.total) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <footer class="inv-footer">
            <p>
                Thank you for shopping with
                <strong>{{ invoiceStore.name }}</strong>.
            </p>
            <p v-if="invoiceStore.email">
                For assistance, contact
                <strong>{{ invoiceStore.email }}</strong>.
            </p>
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
        margin: 0;
    }

    :global([data-print-hidden]) {
        display: none !important;
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
        box-sizing: border-box;
        padding: 14mm;
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

    .inv-brand-mark {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 5px;
    }

    .inv-brand-mark h1 {
        margin: 0;
    }

    .inv-logo {
        width: 34px;
        height: 34px;
        padding: 5px;
        border-radius: 9px;
        color: #ffffff;
        background: #0f766e;
    }

    .inv-header {
        border-bottom-color: #0f766e;
    }

    .inv-brand h1,
    .inv-number,
    .inv-party h3 {
        color: #0f766e;
    }

    .inv-title h2 {
        font-weight: 700;
        letter-spacing: 2px;
    }

    .inv-date {
        margin-top: 3px !important;
        font-size: 10.5px;
        color: #6b7280;
    }

    .inv-meta {
        overflow: hidden;
        background: #f8fafc;
        border-color: #dbe4ea;
    }

    .inv-meta-item {
        padding: 10px 12px;
        border-color: #dbe4ea;
    }

    .inv-items thead th {
        background: #115e59;
    }

    .inv-totals .inv-grand td {
        background: #115e59;
    }

    .inv-discount {
        color: #047857 !important;
        font-weight: 600;
    }

    .inv-thanks-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }

    .inv-footer {
        margin-top: 28px;
        border-top-color: #cbd5e1;
    }

    @page {
        margin: 8mm;
    }

    #invoice-print {
        max-width: none;
        padding: 8mm;
    }

    .inv-parties,
    .inv-items,
    .inv-totals-wrap {
        page-break-inside: avoid;
    }
}
</style>