<script setup lang="ts">
import { ref } from 'vue';
import type { ShopProductImage } from '@/types/shop';

const { images, alt } = defineProps<{
    images: ShopProductImage[];
    alt: string;
}>();

const activeIndex = ref(0);

function selectImage(index: number): void {
    activeIndex.value = index;
}
</script>

<template>
    <div>
        <div
            class="overflow-hidden rounded-3xl border border-[#e8e9e2] bg-[#f1f2ed] shadow-sm"
        >
            <img
                :src="images[activeIndex].full"
                :alt="alt"
                class="aspect-square w-full object-contain p-3 sm:p-5"
            />
        </div>

        <div class="no-scrollbar mt-4 flex gap-3 overflow-x-auto">
            <button
                v-for="(image, index) in images"
                :key="index"
                type="button"
                class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 bg-[#f1f2ed] p-1 focus:ring-2 focus:ring-shop-primary-600 focus:outline-none sm:h-20 sm:w-20"
                :class="
                    activeIndex === index
                        ? 'border-shop-primary-600'
                        : 'border-transparent hover:border-gray-300'
                "
                :aria-label="`View image ${index + 1}`"
                @click="selectImage(index)"
            >
                <img
                    :src="image.thumb"
                    alt=""
                    class="h-full w-full object-cover"
                />
            </button>
        </div>
    </div>
</template>
