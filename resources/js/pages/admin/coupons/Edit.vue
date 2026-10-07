<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import CouponController from '@/actions/App/Http/Controllers/Admin/CouponController';
import CouponForm from '@/components/admin/CouponForm.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { edit, index } from '@/routes/admin/coupons';
import type { AdminCoupon, CouponFormData } from '@/types/admin';

const props = defineProps<{
    coupon: AdminCoupon;
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Coupons', href: index() },
        { title: `Edit ${props.coupon.code}`, href: edit(props.coupon.id) },
    ],
});

const form = useForm<CouponFormData>({
    code: props.coupon.code,
    discount_type: props.coupon.discount_type,
    discount_value: props.coupon.discount_value,
    minimum_order_amount: props.coupon.minimum_order_amount,
    maximum_discount_amount: props.coupon.maximum_discount_amount ?? '',
    usage_limit: props.coupon.usage_limit ?? '',
    expires_at: props.coupon.expires_at ?? '',
    status: props.coupon.status,
});

function submit(): void {
    form.transform((data) => ({ ...data, _method: 'put' })).post(
        CouponController.update.url(props.coupon.id),
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="`Edit ${coupon.code}`" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <Heading
            :title="`Edit ${coupon.code}`"
            description="Update coupon details"
        />

        <div
            class="max-w-2xl rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border"
        >
            <form class="space-y-6" @submit.prevent="submit">
                <CouponForm v-model:form="form" />

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        Save changes
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="index()">Cancel</Link>
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
