<script setup lang="ts">
import { useShopCatalog } from '@/composables/shop/useShopCatalog';
import type { ShopSortOption } from '@/types/shop';

const { total, sort, openFilterDrawer, setSort } = useShopCatalog();

function handleSortChange(event: Event): void {
    setSort((event.target as HTMLSelectElement).value as ShopSortOption);
}
</script>

<template>
    <div
        class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#e7e8e1] bg-white p-3.5 shadow-sm sm:p-4"
    >
        <p class="text-sm text-gray-600">
            <span class="font-bold text-gray-950">{{ total }}</span>
            {{ total === 1 ? 'product' : 'products' }}
        </p>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 lg:hidden"
                @click="openFilterDrawer"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"
                    />
                </svg>
                Filters
            </button>
            <div class="flex items-center gap-2">
                <label
                    for="sortSelect"
                    class="hidden text-sm text-gray-500 sm:block"
                    >Sort:</label
                >
                <select
                    id="sortSelect"
                    :value="sort"
                    class="rounded-full border border-gray-200 bg-[#fafaf7] px-4 py-2.5 text-sm text-gray-900 focus:border-shop-primary-600 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                    @change="handleSortChange"
                >
                    <option value="newest">Newest</option>
                    <option value="best">Best Selling</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                </select>
            </div>
        </div>
    </div>
</template>
