<script setup lang="ts">
import { Form, Head, Link, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Ticket, Trash2 } from '@lucide/vue';
import CouponController from '@/actions/App/Http/Controllers/Admin/CouponController';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import { create, index, edit } from '@/routes/admin/coupons';
import type { AdminCoupon } from '@/types/admin';

const props = defineProps<{
    coupons: AdminCoupon[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Coupons', href: index() },
        ],
    },
});

const statusForm = useForm({});

function toggleStatus(coupon: AdminCoupon): void {
    statusForm.patch(CouponController.toggleStatus.url(coupon.id), {
        preserveScroll: true,
    });
}

function formatAmount(amount: number): string {
    return new Intl.NumberFormat('en-BD', {
        style: 'currency',
        currency: 'BDT',
        maximumFractionDigits: 2,
    }).format(amount);
}

function formatExpiry(value: string | null): string {
    if (!value) {
        return 'No expiry';
    }

    return new Date(value).toLocaleString('en-BD', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
</script>

<template>
    <Head title="Coupons" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
    >
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Coupons"
                description="Manage discount coupons for your store"
            />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add coupon
                </Link>
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1120px] text-sm">
                    <thead
                        class="border-b bg-muted/50 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Discount type</th>
                            <th class="px-4 py-3">Discount value</th>
                            <th class="px-4 py-3">Minimum order</th>
                            <th class="px-4 py-3">Usage limit</th>
                            <th class="px-4 py-3">Used count</th>
                            <th class="px-4 py-3">Expiry date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr
                            v-for="coupon in coupons"
                            :key="coupon.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <td class="px-4 py-4 font-mono font-semibold">
                                {{ coupon.code }}
                            </td>
                            <td class="px-4 py-4 capitalize">
                                {{ coupon.discount_type }}
                            </td>
                            <td class="px-4 py-4 tabular-nums">
                                {{
                                    coupon.discount_type === 'percentage'
                                        ? `${coupon.discount_value}%`
                                        : formatAmount(coupon.discount_value)
                                }}
                                <span
                                    v-if="
                                        coupon.maximum_discount_amount !==
                                            null &&
                                        coupon.discount_type === 'percentage'
                                    "
                                    class="block text-xs text-muted-foreground"
                                >
                                    Max
                                    {{
                                        formatAmount(
                                            coupon.maximum_discount_amount,
                                        )
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-4 tabular-nums">
                                {{ formatAmount(coupon.minimum_order_amount) }}
                            </td>
                            <td class="px-4 py-4 tabular-nums">
                                {{ coupon.usage_limit ?? 'Unlimited' }}
                            </td>
                            <td class="px-4 py-4 tabular-nums">
                                {{ coupon.used_count }}
                            </td>
                            <td class="px-4 py-4">
                                {{ formatExpiry(coupon.expires_at) }}
                            </td>
                            <td class="px-4 py-4">
                                <Badge
                                    :variant="
                                        coupon.status === 'active'
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    class="capitalize"
                                >
                                    {{ coupon.status }}
                                </Badge>
                            </td>
                            <td class="px-4 py-4">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        :disabled="statusForm.processing"
                                        @click="toggleStatus(coupon)"
                                    >
                                        {{
                                            coupon.status === 'active'
                                                ? 'Deactivate'
                                                : 'Activate'
                                        }}
                                    </Button>
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                    >
                                        <Link :href="edit(coupon.id)">
                                            <Pencil class="size-4" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                <Trash2 class="size-4" />
                                                Delete
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <Form
                                                v-bind="
                                                    CouponController.destroy.form(
                                                        coupon.id,
                                                    )
                                                "
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                v-slot="{ processing }"
                                            >
                                                <DialogHeader>
                                                    <DialogTitle
                                                        >Delete
                                                        coupon?</DialogTitle
                                                    >
                                                    <DialogDescription>
                                                        This will permanently
                                                        delete
                                                        <strong>{{
                                                            coupon.code
                                                        }}</strong
                                                        >. Past orders keep
                                                        their coupon code and
                                                        discount snapshot.
                                                    </DialogDescription>
                                                </DialogHeader>
                                                <DialogFooter
                                                    class="mt-4 gap-2"
                                                >
                                                    <DialogClose as-child>
                                                        <Button
                                                            type="button"
                                                            variant="secondary"
                                                            >Cancel</Button
                                                        >
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        :disabled="processing"
                                                    >
                                                        Delete coupon
                                                    </Button>
                                                </DialogFooter>
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="props.coupons.length === 0">
                            <td
                                colspan="9"
                                class="px-4 py-16 text-center text-muted-foreground"
                            >
                                <div class="flex flex-col items-center gap-3">
                                    <Ticket class="size-8" />
                                    <span
                                        >No coupons yet. Create your first
                                        coupon.</span
                                    >
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
