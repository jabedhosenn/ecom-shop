<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <path
            d="M10 14h20l2 19H8l2-19Zm4 0v-4a6 6 0 0 1 12 0v4"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
        />
        <path
            d="M25 20c-1-.8-2.2-1.2-3.6-1.2-2.5 0-4 1.2-4 3 0 1.6 1.2 2.3 3.8 2.9 2.1.5 3 .9 3 2.2 0 1.5-1.4 2.4-3.5 2.4-1.5 0-3-.6-4.1-1.6"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
        />
    </svg>
</template>
