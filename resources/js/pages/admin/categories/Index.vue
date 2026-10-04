<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from '@lucide/vue';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
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
import { create, edit, index, show } from '@/routes/admin/categories';
import type { AdminCategory } from '@/types/admin';

defineProps<{
    categories: AdminCategory[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Categories',
                href: index(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Categories" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Categories"
                description="Manage product categories for your store"
            />

            <Button
                as-child
                class="bg-indigo-600 text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add category
                </Link>
            </Button>
        </div>

        <!-- Summary -->
        <div class="grid gap-3 sm:grid-cols-3">
            <div
                class="rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-4 dark:border-indigo-500/30 dark:from-indigo-500/10 dark:to-transparent"
            >
                <p class="text-xs font-medium text-indigo-700 dark:text-indigo-300">
                    Total categories
                </p>
                <p class="mt-1 text-2xl font-semibold text-indigo-900 dark:text-indigo-100">
                    {{ categories.length }}
                </p>
            </div>
            <div
                class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-4 dark:border-emerald-500/30 dark:from-emerald-500/10 dark:to-transparent"
            >
                <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    Active
                </p>
                <p class="mt-1 text-2xl font-semibold text-emerald-900 dark:text-emerald-100">
                    {{ categories.filter((c) => c.is_active).length }}
                </p>
            </div>
            <div
                class="rounded-xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 dark:border-slate-500/30 dark:from-slate-500/10 dark:to-transparent"
            >
                <p class="text-xs font-medium text-slate-600 dark:text-slate-300">
                    Inactive
                </p>
                <p class="mt-1 text-2xl font-semibold text-slate-800 dark:text-slate-100">
                    {{ categories.filter((c) => !c.is_active).length }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead
                        class="border-b bg-slate-50 text-left text-xs font-semibold tracking-wide text-slate-600 dark:bg-slate-800/50 dark:text-slate-300"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Image</th>
                            <th class="px-5 py-3.5">Name</th>
                            <th class="px-5 py-3.5">Slug</th>
                            <th class="px-5 py-3.5">Products</th>
                            <th class="px-5 py-3.5">Sort</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="category in categories"
                            :key="category.id"
                            class="transition-colors hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5"
                        >
                            <td class="px-5 py-3.5">
                                <div
                                    class="size-12 overflow-hidden rounded-lg border bg-gradient-to-br from-slate-100 to-slate-200 ring-1 ring-black/5 dark:from-slate-800 dark:to-slate-700"
                                >
                                    <img
                                        v-if="category.image"
                                        :src="category.image"
                                        :alt="category.name"
                                        class="size-full object-cover"
                                    />
                                </div>
                            </td>
                            <td
                                class="px-5 py-3.5 font-semibold text-slate-900 dark:text-slate-100"
                            >
                                {{ category.name }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ category.slug }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="inline-flex min-w-8 items-center justify-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"
                                >
                                    {{ category.products_count }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-3.5 text-slate-600 tabular-nums dark:text-slate-300"
                            >
                                {{ category.sort_order }}
                            </td>
                            <td class="px-5 py-3.5">
                                <Badge
                                    :variant="
                                        category.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    :class="
                                        category.is_active
                                            ? 'gap-1.5 border-emerald-200 bg-emerald-100 text-emerald-700 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'gap-1.5 border-slate-200 bg-slate-100 text-slate-600 hover:bg-slate-100 dark:border-slate-500/30 dark:bg-slate-500/15 dark:text-slate-300'
                                    "
                                >
                                    <span
                                        class="size-1.5 rounded-full"
                                        :class="
                                            category.is_active
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-400'
                                        "
                                    />
                                    {{
                                        category.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3.5">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="border-sky-200 text-sky-700 hover:bg-sky-50 hover:text-sky-800 dark:border-sky-500/30 dark:text-sky-300 dark:hover:bg-sky-500/10"
                                    >
                                        <Link :href="show(category.id)">
                                            <Eye class="size-4" />
                                            View
                                        </Link>
                                    </Button>
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="border-amber-200 text-amber-700 hover:bg-amber-50 hover:text-amber-800 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10"
                                    >
                                        <Link :href="edit(category.id)">
                                            <Pencil class="size-4" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                                class="bg-rose-600 text-white hover:bg-rose-700"
                                                :data-test="`delete-category-${category.id}`"
                                            >
                                                <Trash2 class="size-4" />
                                                Delete
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <Form
                                                v-bind="
                                                    CategoryController.destroy.form(
                                                        category.id,
                                                    )
                                                "
                                                :options="{
                                                    preserveScroll: true,
                                                }"
                                                v-slot="{ processing }"
                                            >
                                                <DialogHeader class="space-y-3">
                                                    <div
                                                        class="flex size-11 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300"
                                                    >
                                                        <Trash2
                                                            class="size-5"
                                                        />
                                                    </div>
                                                    <DialogTitle>
                                                        Delete category?
                                                    </DialogTitle>
                                                    <DialogDescription>
                                                        This will remove
                                                        <strong>{{
                                                            category.name
                                                        }}</strong>
                                                        from your store. This
                                                        action can be undone
                                                        from the database if
                                                        needed.
                                                    </DialogDescription>
                                                </DialogHeader>

                                                <DialogFooter class="gap-2">
                                                    <DialogClose as-child>
                                                        <Button
                                                            type="button"
                                                            variant="secondary"
                                                        >
                                                            Cancel
                                                        </Button>
                                                    </DialogClose>
                                                    <Button
                                                        type="submit"
                                                        variant="destructive"
                                                        class="bg-rose-600 text-white hover:bg-rose-700"
                                                        :disabled="processing"
                                                        :data-test="`confirm-delete-category-${category.id}`"
                                                    >
                                                        Delete
                                                    </Button>
                                                </DialogFooter>
                                            </Form>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="categories.length === 0">
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center text-muted-foreground"
                            >
                                No categories yet. Create your first category.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>