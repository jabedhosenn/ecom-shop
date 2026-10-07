<script setup lang="ts">
import { useShopCatalog } from '@/composables/shop/useShopCatalog';
import { shopPriceRanges } from '@/data/shop/catalog';
import type { ShopPriceRange } from '@/types/shop';

const {
    categories,
    selectedCategories,
    priceRange,
    inStockOnly,
    isFilterDrawerOpen,
    toggleCategory,
    setPriceRange,
    setInStockOnly,
    clearFilters,
    closeFilterDrawer,
} = useShopCatalog();

function handlePriceChange(event: Event): void {
    setPriceRange((event.target as HTMLInputElement).value as ShopPriceRange);
}

function handleInStockChange(event: Event): void {
    setInStockOnly((event.target as HTMLInputElement).checked);
}
</script>

<template>
    <div>
        <div
            class="fixed inset-0 z-50 bg-black/40 lg:hidden"
            :class="isFilterDrawerOpen ? 'block' : 'hidden'"
            @click="closeFilterDrawer"
        />

        <aside
            id="filters"
            aria-label="Product filters"
            class="fixed inset-y-0 right-0 z-50 w-80 max-w-[88%] translate-x-full overflow-y-auto bg-[#fbfbf7] p-5 shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:z-auto lg:w-auto lg:max-w-none lg:translate-x-0 lg:overflow-visible lg:bg-transparent lg:p-0 lg:shadow-none"
            :class="isFilterDrawerOpen ? '!translate-x-0' : ''"
        >
            <div class="mb-4 flex items-center justify-between lg:hidden">
                <span class="text-lg font-bold text-gray-900">Filters</span>
                <button
                    type="button"
                    aria-label="Close filters"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                    @click="closeFilterDrawer"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 lg:sticky lg:top-24">
                <!-- Category (multi-select checkboxes) -->
                <div
                    class="rounded-2xl border border-[#e7e8e1] bg-white p-4 shadow-sm"
                >
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">
                        Category
                    </h3>
                    <div class="space-y-1.5 text-sm">
                        <label
                            v-for="cat in categories"
                            :key="cat.slug"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-[#f7f8f3]"
                        >
                            <input
                                type="checkbox"
                                :value="cat.slug"
                                :checked="selectedCategories.includes(cat.slug)"
                                class="h-4 w-4 rounded text-shop-primary-600 focus:ring-shop-primary-600"
                                @change="toggleCategory(cat.slug)"
                            />
                            <span class="text-gray-600">{{ cat.name }}</span>
                        </label>
                    </div>
                </div>

                <!-- Price Range (radio — single select) -->
                <div
                    class="rounded-2xl border border-[#e7e8e1] bg-white p-4 shadow-sm"
                >
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">
                        Price Range
                    </h3>
                    <div class="space-y-1.5 text-sm">
                        <label
                            v-for="range in shopPriceRanges"
                            :key="range.value"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-[#f7f8f3]"
                        >
                            <input
                                type="radio"
                                name="price"
                                :value="range.value"
                                :checked="priceRange === range.value"
                                class="h-4 w-4 text-shop-primary-600 focus:ring-shop-primary-600"
                                @change="handlePriceChange"
                            />
                            <span class="text-gray-600">{{ range.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Availability -->
                <div
                    class="rounded-2xl border border-[#e7e8e1] bg-white p-4 shadow-sm"
                >
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">
                        Availability
                    </h3>
                    <label
                        class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm transition hover:bg-[#f7f8f3]"
                    >
                        <input
                            id="inStockOnly"
                            type="checkbox"
                            :checked="inStockOnly"
                            class="h-4 w-4 rounded text-shop-primary-600 focus:ring-shop-primary-600"
                            @change="handleInStockChange"
                        />
                        <span class="text-gray-600">In Stock only</span>
                    </label>
                </div>

                <button
                    type="button"
                    class="w-full rounded-full border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none"
                    @click="clearFilters"
                >
                    Clear all filters
                </button>

                <button
                    type="button"
                    class="w-full rounded-full bg-shop-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-shop-primary-700 lg:hidden"
                    @click="closeFilterDrawer"
                >
                    Show results
                </button>
            </div>
        </aside>
    </div>
</template>
