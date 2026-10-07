<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { formatTaka } from '@/lib/shop/currency';
import shop from '@/routes/shop';
import type { ShopCartItem } from '@/types/shop';

const {
    items,
    subtotal,
    discountAmount,
    coupon,
    couponError,
    deliveryCharge,
    deliveryNote,
    isEmpty,
    processing = false,
    submitLabel = 'Place Order',
} = defineProps<{
    items: ShopCartItem[];
    subtotal: number;
    discountAmount: number;
    coupon: { code: string } | null;
    couponError: string | null;
    deliveryCharge: number;
    deliveryNote: string;
    isEmpty: boolean;
    processing?: boolean;
    submitLabel?: string;
}>();

const emit = defineEmits<{
    increment: [productId: number];
    decrement: [productId: number];
    remove: [productId: number];
}>();

const isSummaryOpen = ref(false);

const itemCount = computed(() =>
    items.reduce((sum, item) => sum + item.qty, 0),
);

const grandTotal = computed(() =>
    Math.max(0, subtotal - discountAmount + deliveryCharge),
);

const couponForm = useForm({
    coupon_code: coupon?.code ?? '',
});

function applyCoupon(): void {
    couponForm.post(shop.cart.coupon.apply.url(), {
        preserveScroll: true,
    });
}

function removeCoupon(): void {
    router.delete(shop.cart.coupon.remove.url(), {
        preserveScroll: true,
        onSuccess: () => {
            couponForm.coupon_code = '';
            couponForm.clearErrors();
        },
    });
}

function toggleSummary(): void {
    if (window.innerWidth >= 1024) {
        return;
    }

    isSummaryOpen.value = !isSummaryOpen.value;
}
</script>

<template>
    <div
        class="overflow-hidden rounded-3xl border border-[#e7e8e1] bg-white shadow-sm"
    >
        <button
            type="button"
            class="flex w-full items-center justify-between gap-2 p-5 text-left sm:p-6 lg:cursor-default"
            @click="toggleSummary"
        >
            <span class="text-lg font-semibold text-gray-900"
                >Order Summary</span
            >
            <span class="flex items-center gap-2">
                <span
                    class="rounded-full bg-shop-primary-50 px-2.5 py-0.5 text-xs font-medium text-shop-primary-700"
                >
                    {{ itemCount }}
                    {{ itemCount === 1 ? 'item' : 'items' }}
                </span>
                <svg
                    class="h-5 w-5 text-gray-400 lg:hidden"
                    :class="{ 'rotate-180': isSummaryOpen }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 9l-7 7-7-7"
                    />
                </svg>
            </span>
        </button>

        <div
            class="border-t border-gray-100 lg:block"
            :class="isSummaryOpen ? 'block' : 'hidden'"
        >
            <ul class="divide-y divide-gray-100 px-5">
                <li
                    v-if="isEmpty"
                    class="py-8 text-center text-sm text-gray-400"
                >
                    Your cart is empty.
                </li>
                <li
                    v-for="item in items"
                    v-else
                    :key="item.productId"
                    class="flex gap-3 py-4"
                >
                    <div
                        class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100"
                    >
                        <img
                            :src="item.img"
                            :alt="item.name"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p
                                class="line-clamp-1 text-sm font-medium text-gray-900"
                            >
                                {{ item.name }}
                            </p>
                            <button
                                type="button"
                                :aria-label="`Remove ${item.name}`"
                                class="shrink-0 rounded text-gray-300 transition hover:text-red-600 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                @click="emit('remove', item.productId)"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ formatTaka(item.price) }} each
                        </p>
                        <div class="mt-2 flex items-center justify-between">
                            <div
                                class="inline-flex items-center rounded-lg border border-gray-300"
                            >
                                <button
                                    type="button"
                                    :aria-label="`Decrease quantity of ${item.name}`"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-l-lg text-gray-600 hover:bg-gray-100 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none disabled:opacity-40 disabled:hover:bg-transparent"
                                    :disabled="item.qty <= 1"
                                    @click="emit('decrement', item.productId)"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M20 12H4"
                                        />
                                    </svg>
                                </button>
                                <span
                                    class="w-9 text-center text-sm font-medium text-gray-900"
                                    >{{ item.qty }}</span
                                >
                                <button
                                    type="button"
                                    :aria-label="`Increase quantity of ${item.name}`"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-r-lg text-gray-600 hover:bg-gray-100 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    @click="emit('increment', item.productId)"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{
                                formatTaka(item.price * item.qty)
                            }}</span>
                        </div>
                    </div>
                </li>
            </ul>

            <div
                class="space-y-3 border-t border-gray-100 px-5 py-5 text-sm sm:px-6"
            >
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-900">{{
                        formatTaka(subtotal)
                    }}</span>
                </div>
                <div
                    v-if="coupon"
                    class="flex items-center justify-between text-green-700"
                >
                    <span>Coupon ({{ coupon.code }})</span>
                    <span class="font-medium"
                        >-{{ formatTaka(discountAmount) }}</span
                    >
                </div>
                <div v-if="coupon" class="flex justify-end">
                    <button
                        type="button"
                        :disabled="couponForm.processing"
                        class="text-xs font-semibold text-red-600 hover:text-red-700 disabled:opacity-60"
                        @click="removeCoupon"
                    >
                        Remove Coupon
                    </button>
                </div>
                <p v-if="couponError" class="text-sm text-red-600" role="alert">
                    {{ couponError }}
                    <a :href="shop.cart.url()" class="font-medium underline">
                        Return to cart
                    </a>
                </p>
                <div v-if="!coupon" class="space-y-2 pt-1">
                    <label
                        for="checkout_coupon_code"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Coupon code
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="checkout_coupon_code"
                            v-model="couponForm.coupon_code"
                            name="coupon_code"
                            type="text"
                            maxlength="50"
                            autocomplete="off"
                            class="min-w-0 flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 focus:border-shop-primary-600 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                            :class="
                                couponForm.errors.coupon_code
                                    ? 'border-red-500 focus:ring-red-500'
                                    : ''
                            "
                            @input="couponForm.clearErrors('coupon_code')"
                        />
                        <button
                            type="button"
                            :disabled="couponForm.processing"
                            class="rounded-lg bg-shop-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-shop-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                            @click="applyCoupon"
                        >
                            {{
                                couponForm.processing
                                    ? 'Applying...'
                                    : 'Apply Coupon'
                            }}
                        </button>
                    </div>
                    <p
                        v-if="couponForm.errors.coupon_code"
                        class="text-sm text-red-600"
                        role="alert"
                    >
                        {{ couponForm.errors.coupon_code }}
                    </p>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">
                        Delivery
                        <span v-if="deliveryNote" class="text-gray-400">{{
                            deliveryNote
                        }}</span>
                    </span>
                    <span class="font-medium text-gray-900">{{
                        formatTaka(deliveryCharge)
                    }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tax</span>
                    <span class="text-right text-xs text-gray-400"
                        >No separate tax</span
                    >
                </div>
                <div
                    class="mt-2 flex justify-between border-t border-gray-100 pt-3 text-base"
                >
                    <span class="font-semibold text-gray-900">Total</span>
                    <span class="font-bold text-shop-primary-600">{{
                        formatTaka(grandTotal)
                    }}</span>
                </div>
            </div>

            <div class="px-5 pb-5 sm:px-6 sm:pb-6">
                <button
                    type="submit"
                    class="w-full rounded-full bg-shop-primary-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-shop-primary-700 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="isEmpty || processing"
                >
                    {{ processing ? 'Processing…' : submitLabel }}
                </button>
                <p
                    class="mt-3 flex items-center justify-center gap-1.5 text-xs text-gray-400"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                    </svg>
                    Your information is safe & encrypted
                </p>
            </div>
        </div>
    </div>
</template>
