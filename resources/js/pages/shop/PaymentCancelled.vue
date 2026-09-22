<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ShopPageBreadcrumb from '@/components/shop/ShopPageBreadcrumb.vue';
import ShopPaymentResult from '@/components/shop/ShopPaymentResult.vue';
import type { ShopPlacedOrder } from '@/types/shop';

const { order } = defineProps<{
    order: ShopPlacedOrder | null;
}>();
</script>

<template>
    <Head title="Payment Cancelled">
        <meta
            name="description"
            content="Your ShopEase online payment was cancelled."
        />
    </Head>

    <div class="bg-gray-50 py-6 md:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <ShopPageBreadcrumb :items="[{ label: 'Payment Cancelled' }]" />
            <ShopPaymentResult status="cancelled" :order="order" />
        </div>
    </div>
</template>
