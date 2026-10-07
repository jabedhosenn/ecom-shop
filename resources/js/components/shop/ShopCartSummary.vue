<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { formatTaka } from '@/lib/shop/currency';
import shop from '@/routes/shop';

const { itemCount, subtotal, discountAmount, coupon, couponError, total } =
    defineProps<{
        itemCount: number;
        subtotal: number;
        discountAmount: number;
        coupon: { code: string } | null;
        couponError: string | null;
        total: number;
    }>();

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
</script>

<template>
    <div
        class="rounded-3xl border border-[#e7e8e1] bg-white p-5 shadow-sm sm:p-6 lg:sticky lg:top-24"
    >
        <h2 class="text-lg font-semibold text-gray-900">Order Summary</h2>
        <div class="mt-4 space-y-2.5 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500"
                    >Subtotal ({{ itemCount }} items)</span
                >
                <span class="font-medium text-gray-900">{{
                    formatTaka(subtotal)
                }}</span>
            </div>
            <div
                v-if="coupon"
                class="flex items-center justify-between text-shop-primary-700"
            >
                <span>Coupon ({{ coupon.code }})</span>
                <span class="font-medium"
                    >−{{ formatTaka(discountAmount) }}</span
                >
            </div>
            <div v-if="coupon" class="flex justify-end">
                <button
                    type="button"
                    :disabled="couponForm.processing"
                    class="text-xs font-semibold text-red-600 hover:text-red-700 disabled:opacity-60"
                    @click="removeCoupon"
                >
                    Remove coupon
                </button>
            </div>
            <p v-if="couponError" class="text-sm text-red-600" role="alert">
                {{ couponError }}
            </p>
            <div v-if="!coupon" class="space-y-2 border-t border-gray-100 pt-4">
                <label
                    for="cart_coupon_code"
                    class="block text-sm font-medium text-gray-700"
                >
                    Coupon code
                </label>
                <div class="flex gap-2">
                    <input
                        id="cart_coupon_code"
                        v-model="couponForm.coupon_code"
                        name="coupon_code"
                        type="text"
                        maxlength="50"
                        autocomplete="off"
                        class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3 py-2.5 text-sm uppercase focus:border-shop-primary-600 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                        :class="
                            couponForm.errors.coupon_code
                                ? 'border-red-500'
                                : ''
                        "
                        @input="couponForm.clearErrors('coupon_code')"
                    />
                    <button
                        type="button"
                        :disabled="couponForm.processing"
                        class="rounded-xl bg-gray-900 px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-shop-primary-700 disabled:opacity-60"
                        @click="applyCoupon"
                    >
                        {{ couponForm.processing ? 'Applying…' : 'Apply' }}
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
                <span class="text-gray-500">Delivery</span>
                <span class="text-right text-xs text-gray-400"
                    >Calculated at checkout</span
                >
            </div>
            <div
                class="mt-2 flex justify-between border-t border-gray-100 pt-4 text-base"
            >
                <span class="font-semibold text-gray-900">Estimated total</span>
                <span class="font-bold text-shop-primary-600">{{
                    formatTaka(total)
                }}</span>
            </div>
        </div>
        <Link
            :href="shop.checkout()"
            class="mt-5 block w-full rounded-full bg-shop-primary-600 px-5 py-3.5 text-center text-sm font-semibold text-white transition hover:bg-shop-primary-700 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
        >
            Proceed to Checkout
        </Link>
        <div class="mt-4 space-y-2 text-xs text-gray-500">
            <p class="flex items-center gap-2">
                <svg
                    class="h-4 w-4 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                Cash on Delivery available
            </p>
            <p class="flex items-center gap-2">
                <svg
                    class="h-4 w-4 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                Secure payment via SSLCommerz
            </p>
        </div>
    </div>
</template>
