<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ShopPlacedOrder } from '@/types/shop';
import { formatTaka } from '@/lib/shop/currency';
import shop from '@/routes/shop';

const { status, order } = defineProps<{
    status: 'success' | 'failed' | 'cancelled';
    order: ShopPlacedOrder | null;
}>();

const content = computed(() => {
    switch (status) {
        case 'success':
            return {
                iconBg: 'bg-green-50 text-green-600',
                title: 'Payment successful!',
                description:
                    'Your payment has been confirmed and your order is being processed. A confirmation email is on its way.',
                iconPath: 'M5 13l4 4L19 7',
            };
        case 'failed':
            return {
                iconBg: 'bg-red-50 text-red-600',
                title: 'Payment failed',
                description:
                    'We could not process your payment. No charges were made. You can try again or choose a different payment method.',
                iconPath: 'M6 18L18 6M6 6l12 12',
            };
        case 'cancelled':
            return {
                iconBg: 'bg-amber-50 text-amber-600',
                title: 'Payment cancelled',
                description:
                    'You cancelled the payment on the gateway page. Your order is saved but not paid yet.',
                iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            };
    }
});
</script>

<template>
    <div
        class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-8 text-center md:p-12"
    >
        <div
            class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full"
            :class="content.iconBg"
        >
            <svg
                class="h-9 w-9"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    :d="content.iconPath"
                />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">
            {{ content.title }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 md:text-base">
            {{ content.description }}
        </p>

        <div
            v-if="order"
            class="mt-6 grid grid-cols-1 gap-3 rounded-xl bg-gray-50 p-5 text-sm sm:grid-cols-3"
        >
            <div>
                <p class="text-gray-400">Order number</p>
                <p class="mt-0.5 font-semibold text-gray-900">
                    {{ order.orderNumber }}
                </p>
            </div>
            <div>
                <p class="text-gray-400">Total</p>
                <p class="mt-0.5 font-semibold text-gray-900">
                    {{ formatTaka(order.total) }}
                </p>
            </div>
            <div>
                <p class="text-gray-400">Payment</p>
                <p class="mt-0.5 font-semibold text-gray-900">
                    {{ order.paymentLabel }}
                </p>
            </div>
        </div>

        <div
            v-else
            class="mt-6 rounded-xl bg-gray-50 p-5 text-sm text-gray-600"
        >
            Order details are unavailable. If you completed a payment, check
            your email for confirmation.
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <Link
                v-if="status !== 'success'"
                :href="shop.checkout.url()"
                class="rounded-lg bg-shop-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-shop-primary-700"
            >
                {{ status === 'cancelled' ? 'Try Payment Again' : 'Back to Checkout' }}
            </Link>
            <Link
                :href="shop.index.url()"
                class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                :class="{ 'bg-shop-primary-600 text-white border-transparent hover:bg-shop-primary-700 hover:text-white': status === 'success' }"
            >
                Continue Shopping
            </Link>
        </div>
    </div>
</template>
