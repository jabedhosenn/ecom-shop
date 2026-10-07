<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const { variant = 'light' } = defineProps<{
    variant?: 'light' | 'dark';
}>();
</script>

<template>
    <Link
        :href="home()"
        class="flex items-center gap-2"
        aria-label="ShopEase home"
    >
        <span
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-shop-primary-600 text-white"
        >
            <AppLogoIcon class="h-5 w-5 fill-current text-white" />
        </span>
        <span
            class="text-xl font-bold"
            :class="variant === 'dark' ? 'text-white' : 'text-gray-900'"
        >
            Shop<span class="text-shop-primary-600">Ease</span>
        </span>
    </Link>
</template>
