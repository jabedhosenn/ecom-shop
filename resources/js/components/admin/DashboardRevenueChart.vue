<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatTaka } from '@/lib/shop/currency';
import type { AdminDashboardRevenuePoint } from '@/types/admin';
import { computed } from 'vue';

const props = defineProps<{
    data: AdminDashboardRevenuePoint[];
}>();

const maxRevenue = computed(() =>
    Math.max(...props.data.map((point) => point.revenue), 1),
);

const totalRevenue = computed(() =>
    props.data.reduce((sum, point) => sum + point.revenue, 0),
);

const totalOrders = computed(() =>
    props.data.reduce((sum, point) => sum + point.orders, 0),
);
</script>

<template>
    <Card class="h-full">
        <CardHeader>
            <CardTitle>Revenue trend</CardTitle>
            <CardDescription>
                Paid orders over the last 30 days ·
                {{ formatTaka(totalRevenue) }} from {{ totalOrders }} orders
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div class="flex h-48 items-end gap-1 sm:gap-1.5">
                <div
                    v-for="point in data"
                    :key="point.date"
                    class="group flex min-w-0 flex-1 flex-col items-center gap-2"
                >
                    <div
                        class="relative flex w-full flex-1 items-end justify-center"
                    >
                        <div
                            class="w-full max-w-4 rounded-t-md bg-primary/80 transition-colors group-hover:bg-primary"
                            :style="{
                                height: `${Math.max((point.revenue / maxRevenue) * 100, point.revenue > 0 ? 4 : 0)}%`,
                            }"
                            :title="`${point.label}: ${formatTaka(point.revenue)}`"
                        />
                    </div>
                    <span
                        class="hidden text-[10px] text-muted-foreground sm:block"
                    >
                        {{ point.label }}
                    </span>
                </div>
            </div>
            <p class="mt-3 text-xs text-muted-foreground sm:hidden">
                Swipe horizontally to view daily bars on smaller screens.
            </p>
        </CardContent>
    </Card>
</template>
