<script setup lang="ts">
import shop from '@/routes/shop';
import type { ShopCategory } from '@/types/shop';

defineProps<{
    categories: ShopCategory[];
}>();
</script>

<template>
    <section id="categories" class="bg-[#f8f8f4] py-14 md:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-end justify-between">
                <div>
                    <p
                        class="mb-2 text-xs font-semibold tracking-[0.18em] text-shop-primary-600 uppercase"
                    >
                        Browse the store
                    </p>
                    <h2
                        class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl"
                    >
                        Shop by category
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 md:text-base">
                        Find exactly what you're looking for.
                    </p>
                </div>
                <a
                    :href="shop.index()"
                    class="hidden items-center gap-1 text-sm font-medium text-shop-primary-600 hover:text-shop-primary-700 sm:inline-flex"
                >
                    All categories
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
                </a>
            </div>

            <div
                class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:gap-6 lg:grid-cols-6"
            >
                <a
                    v-for="category in categories"
                    :key="category.name"
                    :href="category.href"
                    class="group overflow-hidden rounded-2xl border border-[#e7e8e1] bg-white shadow-sm transition duration-300 ease-out hover:-translate-y-1 hover:border-shop-primary-600/40 hover:shadow-xl hover:shadow-gray-900/10"
                >
                    <div
                        class="relative aspect-square overflow-hidden bg-gray-100"
                    >
                        <img
                            :src="category.img"
                            :alt="category.name"
                            loading="lazy"
                            class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-110"
                        />
                        <div
                            class="pointer-events-none absolute inset-0 flex items-end justify-end bg-gradient-to-t from-black/45 via-black/5 to-transparent p-2 opacity-0 transition duration-300 group-hover:opacity-100"
                        >
                            <span
                                class="inline-flex h-8 w-8 translate-y-1 items-center justify-center rounded-full bg-white text-shop-primary-600 shadow transition duration-300 group-hover:translate-y-0"
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
                                        d="M5 12h14M13 6l6 6-6 6"
                                    />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <span
                        class="block px-2 py-4 text-center text-sm font-semibold text-gray-900 transition group-hover:text-shop-primary-600"
                        >{{ category.name }}</span
                    >
                </a>
            </div>
        </div>
    </section>
</template>
