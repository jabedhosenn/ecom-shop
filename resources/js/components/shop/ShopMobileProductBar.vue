<script setup lang="ts">
import { useShopCart } from '@/composables/shop/useShopCart';
import { useShopUi } from '@/composables/shop/useShopUi';
import { formatTaka } from '@/lib/shop/currency';
import type { ShopProductDetail } from '@/types/shop';

const { product } = defineProps<{
    product: ShopProductDetail;
}>();

const quantity = defineModel<number>('quantity', { default: 1 });

const { addToCart, buyNow } = useShopCart();
const { showToast } = useShopUi();

function handleAddToCart(): void {
    addToCart(product.id, quantity.value);
    showToast('Added to cart');
}

function handleBuyNow(): void {
    buyNow(product.id, quantity.value);
}
</script>

<template>
    <div
        class="fixed inset-x-0 bottom-0 z-40 border-t border-[#e4e6de] bg-white/95 shadow-[0_-8px_28px_rgba(25,45,36,0.08)] backdrop-blur lg:hidden"
    >
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="leading-tight">
                <p class="text-lg font-bold text-gray-950">
                    {{ formatTaka(product.price) }}
                </p>
                <p
                    v-if="product.oldPrice"
                    class="text-xs text-gray-400 line-through"
                >
                    {{ formatTaka(product.oldPrice) }}
                </p>
            </div>
            <button
                type="button"
                :disabled="!product.inStock"
                class="flex-1 rounded-full border border-shop-primary-600 px-4 py-3 text-sm font-semibold text-shop-primary-700 transition hover:bg-shop-primary-50 disabled:cursor-not-allowed disabled:border-gray-200 disabled:text-gray-400"
                @click="handleAddToCart"
            >
                Add to Cart
            </button>
            <button
                type="button"
                :disabled="!product.inStock"
                class="flex-1 rounded-full bg-shop-primary-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-shop-primary-700 disabled:cursor-not-allowed disabled:bg-gray-300"
                @click="handleBuyNow"
            >
                Buy Now
            </button>
        </div>
    </div>
</template>
