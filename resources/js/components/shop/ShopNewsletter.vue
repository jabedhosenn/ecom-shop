<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useShopUi } from '@/composables/shop/useShopUi';
import shop from '@/routes/shop';

const { showToast } = useShopUi();

function handleSubmit(event: Event): void {
    event.preventDefault();
    showToast('Subscribed! Check your inbox 🎉');
}
</script>

<template>
    <section class="bg-[#173d32]">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 md:py-16 lg:px-8">
            <div
                class="flex flex-col gap-7 rounded-3xl border border-white/10 bg-white/[0.04] p-6 sm:p-9 md:flex-row md:items-center md:justify-between"
            >
                <div class="max-w-lg">
                    <p
                        class="mb-3 text-xs font-semibold tracking-[0.18em] text-emerald-200 uppercase"
                    >
                        ShopEase updates
                    </p>
                    <h2
                        class="text-2xl font-bold tracking-tight text-white md:text-3xl"
                    >
                        Discover what’s new.
                    </h2>
                    <p
                        class="mt-3 text-sm leading-6 text-emerald-50/75 md:text-base"
                    >
                        Get updates on new arrivals and products in the current
                        ShopEase collection.
                    </p>
                </div>
                <div
                    class="flex w-full flex-col gap-4 md:max-w-md md:items-end"
                >
                    <form
                        class="flex w-full flex-col gap-3 sm:flex-row"
                        @submit="handleSubmit"
                    >
                        <label for="newsletterEmail" class="sr-only">
                            Email address
                        </label>
                        <input
                            id="newsletterEmail"
                            type="email"
                            required
                            placeholder="Enter your email"
                            class="w-full rounded-full border border-white/20 bg-white/10 px-5 py-3 text-sm text-white placeholder:text-emerald-50/55 focus:border-white focus:ring-2 focus:ring-white/50 focus:outline-none"
                        />
                        <button
                            type="submit"
                            class="shrink-0 rounded-full bg-white px-6 py-3 text-sm font-semibold text-[#173d32] transition hover:bg-emerald-100 focus:ring-2 focus:ring-white focus:outline-none"
                        >
                            Subscribe
                        </button>
                    </form>
                    <Link
                        :href="shop.index()"
                        class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-white/80 transition hover:text-white"
                    >
                        Browse products
                        <span aria-hidden="true">→</span>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
