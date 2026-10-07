<script setup lang="ts">
import type { Component } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Props = {
    title: string;
    value: string;
    description?: string;
    changePercent?: number | null;
    icon?: Component;
    iconClass?: string;
};

defineProps<Props>();

function changeLabel(percent: number): string {
    const prefix = percent > 0 ? '+' : '';

    return `${prefix}${percent}% vs last month`;
}

function changeClass(percent: number | null | undefined): string {
    if (percent === null || percent === undefined) {
        return 'text-muted-foreground';
    }

    if (percent > 0) {
        return 'text-emerald-600 dark:text-emerald-400';
    }

    if (percent < 0) {
        return 'text-red-600 dark:text-red-400';
    }

    return 'text-muted-foreground';
}
</script>

<template>
    <Card
        class="h-full rounded-2xl border-border/70 bg-card py-0 shadow-sm transition-shadow hover:shadow-md"
    >
        <CardHeader
            class="flex flex-row items-start justify-between gap-3 px-4 pt-4 pb-2 sm:px-5 sm:pt-5"
        >
            <div class="space-y-1">
                <CardDescription class="text-xs font-medium tracking-wide">
                    {{ title }}
                </CardDescription>
                <CardTitle class="text-xl font-bold tracking-tight tabular-nums sm:text-2xl">
                    {{ value }}
                </CardTitle>
            </div>
            <div
                v-if="icon"
                class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                :class="iconClass ?? 'bg-primary/10 text-primary'"
            >
                <component :is="icon" class="size-5" />
            </div>
        </CardHeader>
        <CardContent class="px-4 pb-4 sm:px-5 sm:pb-5">
            <p
                v-if="changePercent !== undefined"
                class="text-sm"
                :class="changeClass(changePercent)"
            >
                <template v-if="changePercent !== null">
                    {{ changeLabel(changePercent) }}
                </template>
                <template v-else>
                    No data for last month
                </template>
            </p>
            <p v-else-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </CardContent>
    </Card>
</template>
