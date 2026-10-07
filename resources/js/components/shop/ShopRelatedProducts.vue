<script setup lang="ts">
import ShopProductCard from '@/components/shop/ShopProductCard.vue';
import type { ShopProduct } from '@/types/shop';

const { products } = defineProps<{
    products: ShopProduct[];
}>();
</script>

<template>
    <section class="bg-[#f8f8f4] py-14 md:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p
                class="mb-2 text-xs font-semibold tracking-[0.18em] text-shop-primary-600 uppercase"
            >
                More to explore
            </p>
            <h2
                class="mb-8 text-3xl font-bold tracking-tight text-gray-950 md:text-4xl"
            >
                You may also like
            </h2>
            <div
                class="grid grid-cols-2 gap-3.5 sm:gap-5 md:grid-cols-3 lg:grid-cols-4"
            >
                <ShopProductCard
                    v-for="product in products"
                    :key="product.id ?? product.slug ?? product.name"
                    :product="product"
                />
            </div>
        </div>
    </section>
</template>
