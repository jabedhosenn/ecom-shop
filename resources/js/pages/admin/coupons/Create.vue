<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import CouponController from '@/actions/App/Http/Controllers/Admin/CouponController';
import CouponForm from '@/components/admin/CouponForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { create, index } from '@/routes/admin/coupons';
import type { CouponFormData } from '@/types/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Coupons', href: index() },
            { title: 'Create', href: create() },
        ],
    },
});

const form = useForm<CouponFormData>({
    code: '',
    discount_type: 'percentage',
    discount_value: 10,
    minimum_order_amount: 0,
    maximum_discount_amount: '',
    usage_limit: '',
    expires_at: '',
    status: 'active',
});

function submit(): void {
    form.post(CouponController.store.url(), { preserveScroll: true });
}
</script>

<template>
    <Head title="Create coupon" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading title="Create coupon" description="Add a discount coupon" />

        <div
            class="max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <CouponForm v-model:form="form" />

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        Create coupon
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="index()">Cancel</Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
