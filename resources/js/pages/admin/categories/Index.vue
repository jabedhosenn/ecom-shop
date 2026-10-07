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

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6"
    >
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
                class="h-10 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 text-white shadow-md shadow-indigo-600/25 transition hover:from-indigo-700 hover:to-violet-700 hover:shadow-lg hover:shadow-indigo-600/30"
            >
                <Link :href="create()">
                    <Plus class="size-4" />
                    Add category
                </Link>
            </Button>
        </div>

        <!-- Summary -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 p-5 text-white shadow-lg shadow-indigo-500/20"
            >
                <div
                    class="absolute -top-6 -right-6 size-24 rounded-full bg-white/15"
                />
                <p class="text-sm font-medium text-indigo-100">
                    Total categories
                </p>
                <p class="mt-2 text-3xl font-bold tabular-nums">
                    {{ categories.length }}
                </p>
            </div>
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 p-5 text-white shadow-lg shadow-emerald-500/20"
            >
                <div
                    class="absolute -top-6 -right-6 size-24 rounded-full bg-white/15"
                />
                <p class="text-sm font-medium text-emerald-100">Active</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">
                    {{ categories.filter((c) => c.is_active).length }}
                </p>
            </div>
            <div
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-500 to-slate-700 p-5 text-white shadow-lg shadow-slate-500/20"
            >
                <div
                    class="absolute -top-6 -right-6 size-24 rounded-full bg-white/15"
                />
                <p class="text-sm font-medium text-slate-200">Inactive</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">
                    {{ categories.filter((c) => !c.is_active).length }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <div
            class="overflow-hidden rounded-2xl border border-sidebar-border/70 bg-card shadow-sm dark:border-sidebar-border"
        >
            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm">
                    <thead
                        class="border-b bg-gradient-to-r from-indigo-50 via-violet-50 to-slate-50 text-left text-xs font-semibold tracking-wide text-indigo-900/80 dark:from-indigo-500/10 dark:via-violet-500/10 dark:to-transparent dark:text-indigo-200"
                    >
                        <tr>
                            <th class="px-5 py-4">Image</th>
                            <th class="px-5 py-4">Name</th>
                            <th class="px-5 py-4">Slug</th>
                            <th class="px-5 py-4">Products</th>
                            <th class="px-5 py-4">Sort</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr
                            v-for="category in categories"
                            :key="category.id"
                            class="group transition-colors hover:bg-indigo-50/60 dark:hover:bg-indigo-500/5"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="size-14 overflow-hidden rounded-xl border bg-gradient-to-br from-indigo-100 to-violet-100 shadow-sm ring-1 ring-black/5 transition group-hover:shadow-md dark:from-indigo-500/20 dark:to-violet-500/20"
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
                                class="px-5 py-4 text-base font-semibold text-slate-900 dark:text-slate-100"
                            >
                                {{ category.name }}
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 font-mono text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ category.slug }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex min-w-9 items-center justify-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700 tabular-nums dark:bg-indigo-500/20 dark:text-indigo-300"
                                >
                                    {{ category.products_count }}
                                </span>
                            </td>
                            <td
                                class="px-5 py-4 font-medium text-slate-600 tabular-nums dark:text-slate-300"
                            >
                                {{ category.sort_order }}
                            </td>
                            <td class="px-5 py-4">
                                <Badge
                                    :variant="
                                        category.is_active
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    :class="
                                        category.is_active
                                            ? 'gap-1.5 rounded-full border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-300'
                                            : 'gap-1.5 rounded-full border-slate-200 bg-slate-100 px-3 py-1 text-slate-600 hover:bg-slate-100 dark:border-slate-500/30 dark:bg-slate-500/15 dark:text-slate-300'
                                    "
                                >
                                    <span
                                        class="size-2 rounded-full"
                                        :class="
                                            category.is_active
                                                ? 'bg-emerald-500 shadow-[0_0_0_3px] shadow-emerald-500/20'
                                                : 'bg-slate-400 shadow-[0_0_0_3px] shadow-slate-400/20'
                                        "
                                    />
                                    {{
                                        category.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-5 py-4">
                                <div
                                    class="flex items-center justify-end gap-2"
                                >
                                    <Button
                                        as-child
                                        variant="outline"
                                        size="sm"
                                        class="rounded-lg border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 hover:text-sky-800 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:bg-sky-500/20"
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
                                        class="rounded-lg border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20"
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
                                                class="rounded-lg bg-rose-600 text-white shadow-sm shadow-rose-600/20 hover:bg-rose-700"
                                                :data-test="`delete-category-${category.id}`"
                                            >
                                                <Trash2 class="size-4" />
                                                Delete
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent class="rounded-2xl">
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
                                                        class="flex size-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 ring-8 ring-rose-50 dark:bg-rose-500/20 dark:text-rose-300 dark:ring-rose-500/10"
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
                                class="px-5 py-16 text-center text-muted-foreground"
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