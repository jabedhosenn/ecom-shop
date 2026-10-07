<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ShopCategoryGrid from '@/components/shop/ShopCategoryGrid.vue';
import ShopHeroCarousel from '@/components/shop/ShopHeroCarousel.vue';
import ShopNewsletter from '@/components/shop/ShopNewsletter.vue';
import ShopProductSection from '@/components/shop/ShopProductSection.vue';
import ShopTrustStrip from '@/components/shop/ShopTrustStrip.vue';
import type {
    ShopCarouselSlide,
    ShopCategory,
    ShopProduct,
} from '@/types/shop';

const page = usePage();

const heroSlides = computed(
    () => (page.props.heroSlides as ShopCarouselSlide[] | undefined) ?? [],
);
const categories = computed(
    () => (page.props.categories as ShopCategory[] | undefined) ?? [],
);
const bestSellingProducts = computed(
    () => (page.props.bestSellingProducts as ShopProduct[] | undefined) ?? [],
);
const newCollectionProducts = computed(
    () => (page.props.newCollectionProducts as ShopProduct[] | undefined) ?? [],
);
const latestProducts = computed(
    () => (page.props.latestProducts as ShopProduct[] | undefined) ?? [],
);

const hasStorefrontData = computed(
    () =>
        heroSlides.value.length > 0 ||
        categories.value.length > 0 ||
        bestSellingProducts.value.length > 0 ||
        newCollectionProducts.value.length > 0 ||
        latestProducts.value.length > 0,
);
</script>

<template>
    <Head title="Home">
        <meta
            name="description"
            content="Explore current products and categories in the ShopEase store. Cash on Delivery and online payment are available across Bangladesh."
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

    <div
        v-if="!hasStorefrontData"
        class="border-b border-amber-200 bg-amber-50 px-4 py-6 text-center text-sm text-amber-900"
    >
        Storefront data is not loaded yet. Run
        <code class="rounded bg-white px-2 py-1 font-mono text-xs"
            >php artisan db:seed</code
        >
        to populate demo products, categories, and hero slides.
    </div>

    <ShopHeroCarousel :slides="heroSlides" />
    <ShopTrustStrip />
    <ShopCategoryGrid v-if="categories.length > 0" :categories="categories" />
    <ShopProductSection
        v-if="bestSellingProducts.length > 0"
        id="bestselling"
        title="Best sellers"
        description="Products marked as best sellers in the store."
        :products="bestSellingProducts"
        background="gray"
    />
    <ShopProductSection
        v-if="newCollectionProducts.length > 0"
        id="newcollection"
        title="Featured collection"
        description="Explore more products selected by the store."
        :products="newCollectionProducts"
        background="gray"
    />
    <ShopProductSection
        v-if="latestProducts.length > 0"
        id="latestproducts"
        title="Latest products"
        description="Recently added to the store catalog."
        :products="latestProducts"
    />
    <ShopNewsletter />
</template>
