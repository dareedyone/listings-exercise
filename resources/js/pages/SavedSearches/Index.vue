<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../components/AppLayout.vue';
import BackButton from '../../components/BackButton.vue';

defineProps({
    savedSearches: { type: Array, required: true },
});

function deleteSearch(id) {
    if (!window.confirm('Delete this saved search?')) {
        return;
    }

    router.delete(`/saved-searches/${id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Saved searches" />

    <AppLayout
        heading="Saved searches"
        subheading="Searches you've saved for property alerts."
    >
        <BackButton label="← Back" />
        <Link
            href="/alerts"
            class="mb-6 block text-sm text-slate-500 hover:text-slate-900"
        >
             View alerts →
        </Link>

        <div
            v-if="savedSearches.length === 0"
            class="rounded-xl border border-dashed border-slate-300 p-10 text-center"
        >
            <p class="text-sm text-slate-500">
                You don't have any saved searches yet.
            </p>

            <Link
                href="/"
                class="mt-4 inline-block text-sm font-medium text-slate-900 underline underline-offset-4"
            >
                Search listings
            </Link>
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="savedSearch in savedSearches"
                :key="savedSearch.id"
                class="flex items-center justify-between gap-6 rounded-xl border border-slate-200 p-5"
            >
                <div>
                    <h2 class="font-medium text-slate-900">
                        Saved search
                    </h2>

                    <div class="mt-2 flex flex-wrap gap-2 text-sm text-slate-600">
                        <span v-if="savedSearch.property_type_label">
                            {{ savedSearch.property_type_label }}
                        </span>

                        <span v-if="savedSearch.region">
                            {{ savedSearch.region }}
                        </span>

                        <span v-if="savedSearch.min_bedrooms !== null">
                            {{ savedSearch.min_bedrooms }}+ beds
                        </span>

                        <span v-if="savedSearch.max_price !== null">
                            £{{ Number(savedSearch.max_price).toLocaleString() }} max
                        </span>
                    </div>

                    <p class="mt-2 text-xs text-slate-400">
                        You'll receive an alert when a new listing matches this search.
                    </p>
                </div>

                <button
                    type="button"
                    class="shrink-0 text-sm font-medium text-red-600 hover:text-red-800"
                    @click="deleteSearch(savedSearch.id)"
                >
                    Delete
                </button>
            </div>
        </div>
    </AppLayout>
</template>