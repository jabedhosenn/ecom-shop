<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ShopCartLineItem from '@/components/shop/ShopCartLineItem.vue';
import ShopCartSummary from '@/components/shop/ShopCartSummary.vue';
import ShopPageBreadcrumb from '@/components/shop/ShopPageBreadcrumb.vue';
import { useShopCart } from '@/composables/shop/useShopCart';
import { useShopUi } from '@/composables/shop/useShopUi';
import shop from '@/routes/shop';

const { cart, cartQty, updateQty, removeItem, clearCart } = useShopCart();
const { showToast } = useShopUi();

const { subtotal, coupon, discountAmount, total, couponError } = defineProps<{
    subtotal: number;
    coupon: { code: string } | null;
    discountAmount: number;
    total: number;
    couponError: string | null;
}>();

function handleIncrement(productId: number): void {
    const item = cart.value.find((i) => i.productId === productId);

    if (item) {
        updateQty(productId, item.qty + 1);
    }
}

function handleDecrement(productId: number): void {
    const item = cart.value.find((i) => i.productId === productId);

    if (item && item.qty > 1) {
        updateQty(productId, item.qty - 1);
    }
}

function handleRemove(productId: number): void {
    const item = cart.value.find((i) => i.productId === productId);
    const name = item?.name ?? 'item';
    removeItem(productId);
    showToast(`Removed: ${name}`);
}

function handleClearCart(): void {
    clearCart();
    showToast('Cart cleared');
}
</script>

<template>
    <Head title="Shopping Cart">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin="anonymous"
        />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="bg-[#f8f8f4] py-7 md:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <ShopPageBreadcrumb :items="[{ label: 'Cart' }]" />

            <div class="mb-7 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.18em] text-shop-primary-600 uppercase"
                    >
                        Your selection
                    </p>
                    <h1
                        class="text-3xl font-bold tracking-tight text-gray-950 md:text-4xl"
                    >
                        Shopping cart
                    </h1>
                </div>
                <span
                    class="rounded-full border border-[#e4e6de] bg-white px-4 py-2 text-sm font-medium text-gray-600"
                >
                    {{ cartQty }} {{ cartQty === 1 ? 'item' : 'items' }}
                </span>
            </div>

            <div
                v-if="cart.length > 0"
                class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8"
            >
                <div class="lg:col-span-2">
                    <div
                        class="overflow-hidden rounded-3xl border border-[#e7e8e1] bg-white shadow-sm"
                    >
                        <div
                            class="hidden grid-cols-12 gap-4 border-b border-gray-100 bg-[#fafaf7] px-5 py-4 text-[11px] font-semibold tracking-[0.12em] text-gray-500 uppercase sm:grid"
                        >
                            <span class="col-span-6">Product</span>
                            <span class="col-span-2 text-center">Price</span>
                            <span class="col-span-2 text-center">Quantity</span>
                            <span class="col-span-2 text-right">Total</span>
                        </div>
                        <ul class="divide-y divide-gray-100">
                            <ShopCartLineItem
                                v-for="item in cart"
                                :key="item.productId"
                                :item="item"
                                @increment="handleIncrement"
                                @decrement="handleDecrement"
                                @remove="handleRemove"
                            />
                        </ul>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <Link
                            :href="shop.index()"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-shop-primary-600 hover:text-shop-primary-700"
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
                                    d="M19 12H5M11 18l-6-6 6-6"
                                />
                            </svg>
                            Continue shopping
                        </Link>
                        <button
                            type="button"
                            class="text-sm font-medium text-gray-500 transition hover:text-red-600"
                            @click="handleClearCart"
                        >
                            Clear cart
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <ShopCartSummary
                        :item-count="cartQty"
                        :subtotal="subtotal"
                        :discount-amount="discountAmount"
                        :coupon="coupon"
                        :coupon-error="couponError"
                        :total="total"
                    />
                </div>
            </div>

            <div
                v-else
                class="rounded-3xl border border-[#e7e8e1] bg-white px-5 py-16 text-center shadow-sm"
            >
                <div
                    class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 text-gray-400"
                >
                    <svg
                        class="h-10 w-10"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                        />
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Your cart is empty
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Looks like you haven't added anything yet.
                </p>
                <Link
                    :href="shop.index()"
                    class="mt-5 inline-block rounded-lg bg-shop-primary-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-shop-primary-700"
                >
                    Start Shopping
                </Link>
            </div>
        </div>
    </div>
</template>
