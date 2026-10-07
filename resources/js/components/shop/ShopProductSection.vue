<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ShopProductCard from '@/components/shop/ShopProductCard.vue';
import shop from '@/routes/shop';
import type { ShopProduct } from '@/types/shop';

const {
    id,
    title,
    description,
    products,
    background = 'white',
} = defineProps<{
    id: string;
    title: string;
    description: string;
    products: ShopProduct[];
    background?: 'white' | 'gray';
}>();
</script>

<template>
    <section
        :id="id"
        class="py-14 md:py-20 lg:py-24"
        :class="background === 'gray' ? 'bg-gray-100' : 'bg-white'"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-4">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.18em] text-shop-primary-600 uppercase"
                    >
                        Curated for you
                    </p>
                    <h2
                        class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl"
                    >
                        {{ title }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 md:text-base">
                        {{ description }}
                    </p>
                </div>
                <Link
                    :href="shop.index()"
                    class="inline-flex shrink-0 items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-shop-primary-600 hover:text-shop-primary-700"
                >
                    View shop
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
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </Link>
            </div>

            <div
                class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4"
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
