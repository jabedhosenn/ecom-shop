<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShopCheckoutSummary from '@/components/shop/ShopCheckoutSummary.vue';
import ShopPageBreadcrumb from '@/components/shop/ShopPageBreadcrumb.vue';
import { useShopCart } from '@/composables/shop/useShopCart';
import { useShopUi } from '@/composables/shop/useShopUi';
import shop from '@/routes/shop';
import type { ShopAppliedCoupon, ShopCheckoutConfig } from '@/types/shop';

type PaymentMethod = 'cod' | 'sslcommerz';

const {
    districts,
    deliveryCharges,
    subtotal,
    coupon,
    discountAmount,
    couponError,
} = defineProps<{
    districts: string[];
    deliveryCharges: ShopCheckoutConfig;
    subtotal: number;
    coupon: ShopAppliedCoupon | null;
    discountAmount: number;
    couponError: string | null;
}>();

const { cart, updateQty, removeItem } = useShopCart();
const { showToast } = useShopUi();

const form = useForm({
    customer_name: '',
    phone: '',
    email: '',
    district: '',
    area: '',
    address: '',
    notes: '',
    payment_method: 'cod' as PaymentMethod,
});

const deliveryCharge = computed(() => {
    if (cart.value.length === 0) {
        return 0;
    }

    if (!form.district) {
        return deliveryCharges.outsideDhaka;
    }

    return form.district === deliveryCharges.dhakaDistrict
        ? deliveryCharges.insideDhaka
        : deliveryCharges.outsideDhaka;
});

const deliveryNote = computed(() => {
    if (cart.value.length === 0 || !form.district) {
        return '';
    }

    return form.district === deliveryCharges.dhakaDistrict
        ? '(Inside Dhaka)'
        : '(Outside Dhaka)';
});

const submitLabel = computed(() =>
    form.payment_method === 'sslcommerz' ? 'Proceed to Payment' : 'Place Order',
);

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
    removeItem(productId);

    if (item) {
        showToast(`Removed: ${item.name}`);
    }
}

function handleSubmit(): void {
    if (cart.value.length === 0) {
        showToast('Your cart is empty');

        return;
    }

    form.post(shop.checkout.store.url(), {
        preserveScroll: true,
        onError: () => {
            showToast('Please complete the highlighted fields');
        },
    });
}
</script>

<template>
    <Head title="Checkout">
        <meta
            name="description"
            content="Secure checkout with Cash on Delivery or SSLCommerz online payment. Delivery across Bangladesh."
        />
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
            <ShopPageBreadcrumb
                :items="[
                    { label: 'Cart', href: shop.cart.url() },
                    { label: 'Checkout' },
                ]"
            />

            <div class="mb-7">
                <p
                    class="mb-2 text-xs font-semibold tracking-[0.18em] text-shop-primary-600 uppercase"
                >
                    Almost there
                </p>
                <h1
                    class="text-3xl font-bold tracking-tight text-gray-950 md:text-4xl"
                >
                    Secure checkout
                </h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-gray-600">
                    Add your contact and delivery details, then choose how you’d
                    like to pay.
                </p>
            </div>

            <form
                novalidate
                class="grid min-w-0 grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-8"
                @submit.prevent="handleSubmit"
            >
                <div class="space-y-6 lg:col-span-2">
                    <section
                        class="rounded-3xl border border-[#e7e8e1] bg-white p-5 shadow-sm md:p-7"
                    >
                        <p
                            class="mb-2 text-xs font-semibold tracking-[0.16em] text-shop-primary-600 uppercase"
                        >
                            Step 1
                        </p>
                        <h2
                            class="text-xl font-bold tracking-tight text-gray-950"
                        >
                            Customer information
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            How can we reach you about this order?
                        </p>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="customer_name"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Full name
                                    <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="customer_name"
                                    v-model="form.customer_name"
                                    name="customer_name"
                                    type="text"
                                    required
                                    autocomplete="name"
                                    class="w-full rounded-xl border px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.customer_name
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                    placeholder="e.g. Rina Akter"
                                />
                                <p
                                    v-show="form.errors.customer_name"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.customer_name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="phone"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Phone
                                    <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    name="phone"
                                    type="tel"
                                    required
                                    autocomplete="tel"
                                    inputmode="numeric"
                                    class="w-full rounded-xl border px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.phone
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                    placeholder="01XXXXXXXXX"
                                />
                                <p
                                    v-show="form.errors.phone"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label
                                    for="email"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Email
                                    <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    name="email"
                                    type="email"
                                    required
                                    autocomplete="email"
                                    class="w-full rounded-xl border px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.email
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                    placeholder="you@example.com"
                                />
                                <p
                                    v-show="form.errors.email"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-[#e7e8e1] bg-white p-5 shadow-sm md:p-7"
                    >
                        <p
                            class="mb-2 text-xs font-semibold tracking-[0.16em] text-shop-primary-600 uppercase"
                        >
                            Step 2
                        </p>
                        <h2
                            class="text-xl font-bold tracking-tight text-gray-950"
                        >
                            Shipping information
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Enter the address where your order should arrive.
                        </p>
                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    for="district"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    District
                                    <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="district"
                                    v-model="form.district"
                                    name="district"
                                    required
                                    class="w-full rounded-xl border bg-white px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.district
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                >
                                    <option value="">Select district</option>
                                    <option
                                        v-for="district in districts"
                                        :key="district"
                                        :value="district"
                                    >
                                        {{ district }}
                                    </option>
                                </select>
                                <p
                                    v-show="form.errors.district"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.district }}
                                </p>
                            </div>
                            <div>
                                <label
                                    for="area"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Area / Thana
                                    <span class="text-red-600">*</span>
                                </label>
                                <input
                                    id="area"
                                    v-model="form.area"
                                    name="area"
                                    type="text"
                                    required
                                    class="w-full rounded-xl border px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.area
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                    placeholder="e.g. Dhanmondi"
                                />
                                <p
                                    v-show="form.errors.area"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.area }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label
                                    for="address"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Full address
                                    <span class="text-red-600">*</span>
                                </label>
                                <textarea
                                    id="address"
                                    v-model="form.address"
                                    name="address"
                                    rows="2"
                                    required
                                    class="w-full rounded-xl border px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    :class="
                                        form.errors.address
                                            ? 'border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-shop-primary-600'
                                    "
                                    placeholder="House, road, and any landmark"
                                />
                                <p
                                    v-show="form.errors.address"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ form.errors.address }}
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <label
                                    for="notes"
                                    class="mb-1.5 block text-sm font-medium text-gray-700"
                                >
                                    Order notes
                                    <span class="text-gray-400"
                                        >(optional)</span
                                    >
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    name="notes"
                                    rows="2"
                                    class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 shadow-sm shadow-gray-950/[0.02] focus:border-shop-primary-600 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                                    placeholder="Delivery instructions, preferred time, etc."
                                />
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-3xl border border-[#e7e8e1] bg-white p-5 shadow-sm md:p-7"
                    >
                        <p
                            class="mb-2 text-xs font-semibold tracking-[0.16em] text-shop-primary-600 uppercase"
                        >
                            Step 3
                        </p>
                        <h2
                            class="text-xl font-bold tracking-tight text-gray-950"
                        >
                            Payment method
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Choose how you would like to pay for your order.
                        </p>

                        <div class="mt-5 space-y-3">
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition"
                                :class="
                                    form.payment_method === 'cod'
                                        ? 'border-shop-primary-600 bg-shop-primary-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                "
                            >
                                <input
                                    v-model="form.payment_method"
                                    type="radio"
                                    name="payment_method"
                                    value="cod"
                                    class="mt-1 h-4 w-4 border-gray-300 text-shop-primary-600 focus:ring-shop-primary-600"
                                />
                                <svg
                                    class="mt-0.5 h-6 w-6 shrink-0 text-shop-primary-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2m2-6h10a2 2 0 012 2v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6a2 2 0 012-2zm7 5a2 2 0 11-4 0 2 2 0 014 0z"
                                    />
                                </svg>
                                <div>
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Cash on Delivery
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Pay the delivery agent in cash when you
                                        receive your order.
                                    </p>
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-xl border-2 p-4 transition"
                                :class="
                                    form.payment_method === 'sslcommerz'
                                        ? 'border-shop-primary-600 bg-shop-primary-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                "
                            >
                                <input
                                    v-model="form.payment_method"
                                    type="radio"
                                    name="payment_method"
                                    value="sslcommerz"
                                    class="mt-1 h-4 w-4 border-gray-300 text-shop-primary-600 focus:ring-shop-primary-600"
                                />
                                <svg
                                    class="mt-0.5 h-6 w-6 shrink-0 text-shop-primary-600"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                                    />
                                </svg>
                                <div>
                                    <p
                                        class="text-sm font-semibold text-gray-900"
                                    >
                                        Pay Online (SSLCommerz)
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Pay securely with bKash, Nagad, cards,
                                        or mobile banking via SSLCommerz.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <p
                            v-show="form.errors.payment_method"
                            class="mt-3 text-sm text-red-600"
                        >
                            {{ form.errors.payment_method }}
                        </p>
                    </section>
                </div>

                <div class="lg:col-span-1">
                    <div class="lg:sticky lg:top-24">
                        <ShopCheckoutSummary
                            :items="cart"
                            :subtotal="subtotal"
                            :discount-amount="discountAmount"
                            :coupon="coupon"
                            :coupon-error="couponError"
                            :delivery-charge="deliveryCharge"
                            :delivery-note="deliveryNote"
                            :is-empty="cart.length === 0"
                            :processing="form.processing"
                            :submit-label="submitLabel"
                            @increment="handleIncrement"
                            @decrement="handleDecrement"
                            @remove="handleRemove"
                        />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
