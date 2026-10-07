<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CouponFormData } from '@/types/admin';

const form = defineModel<InertiaForm<CouponFormData>>('form', {
    required: true,
});
</script>

<template>
    <div class="grid gap-6">
        <div class="grid gap-2">
            <Label for="code">Code</Label>
            <Input
                id="code"
                v-model="form.code"
                required
                maxlength="50"
                autocomplete="off"
                placeholder="SUMMER10"
            />
            <InputError :message="form.errors.code" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="discount_type">Discount type</Label>
                <select
                    id="discount_type"
                    v-model="form.discount_type"
                    required
                    class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                >
                    <option value="percentage">Percentage</option>
                    <option value="flat">Flat amount</option>
                </select>
                <InputError :message="form.errors.discount_type" />
            </div>

            <div class="grid gap-2">
                <Label for="discount_value">
                    Discount value
                    {{ form.discount_type === 'percentage' ? '(%)' : '(৳)' }}
                </Label>
                <Input
                    id="discount_value"
                    v-model.number="form.discount_value"
                    type="number"
                    min="0.01"
                    :max="form.discount_type === 'percentage' ? 100 : undefined"
                    step="0.01"
                    required
                />
                <InputError :message="form.errors.discount_value" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="minimum_order_amount">
                    Minimum order amount (৳)
                </Label>
                <Input
                    id="minimum_order_amount"
                    v-model.number="form.minimum_order_amount"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                />
                <InputError :message="form.errors.minimum_order_amount" />
            </div>

            <div class="grid gap-2">
                <Label for="maximum_discount_amount">
                    Maximum discount (৳, optional)
                </Label>
                <Input
                    id="maximum_discount_amount"
                    v-model.number="form.maximum_discount_amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                />
                <InputError :message="form.errors.maximum_discount_amount" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="usage_limit">Usage limit (optional)</Label>
                <Input
                    id="usage_limit"
                    v-model.number="form.usage_limit"
                    type="number"
                    min="1"
                    step="1"
                />
                <InputError :message="form.errors.usage_limit" />
            </div>

            <div class="grid gap-2">
                <Label for="expires_at">Expiry date (optional)</Label>
                <Input
                    id="expires_at"
                    v-model="form.expires_at"
                    type="datetime-local"
                />
                <InputError :message="form.errors.expires_at" />
            </div>
        </div>

        <div class="grid gap-2 sm:max-w-xs">
            <Label for="status">Status</Label>
            <select
                id="status"
                v-model="form.status"
                required
                class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm ring-offset-background focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <InputError :message="form.errors.status" />
        </div>
    </div>
</template>
