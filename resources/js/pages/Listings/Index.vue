<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../components/AppLayout.vue';
import ListingCard from '../../components/ListingCard.vue';
import ListingFilters from '../../components/ListingFilters.vue';
import Pagination from '../../components/Pagination.vue';

const props = defineProps({
    listings: { type: Object, required: true },
    branches: { type: Array, required: true },
    propertyTypes: { type: Array, required: true },
    filters: { type: Object, required: true },
});

const processing = ref(false);

// Seeded from the server so the filter form survives a refresh, a shared link
// or the back button — the query string is the single source of truth.
const form = ref({
    property_type: props.filters.property_type ?? '',
    region: props.filters.region ?? '',
    min_bedrooms: props.filters.min_bedrooms ?? '',
    max_price: props.filters.max_price ?? '',
});


function getQueryString() {
    return Object.fromEntries(
        Object.entries(form.value).filter(([, value]) => value !== '' && value !== null),
    );
}

const saveSearchUrl = computed(() => {
    const query = getQueryString();

    const search = new URLSearchParams(query).toString();

    return search
        ? `/saved-searches/create?${search}`
        : '/saved-searches/create';
});


function search() {
    // Blank fields are dropped rather than sent empty, so the URL stays clean.
    const query = getQueryString();

    router.get('/', query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        // Inertia cancels the in-flight visit when a new one starts, so rapid
        // searches can't land out of order.
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
    });
}

function reset() {
    form.value = { property_type: '', region: '', min_bedrooms: '', max_price: '' };
    search();
}
</script>

<template>
    <Head title="Properties for sale" />

    <AppLayout heading="Properties for sale" subheading="Across our branches.">
        <ListingFilters
            v-model="form"
            :branches="branches"
            :property-types="propertyTypes"
            :processing="processing"
            @submit="search"
            @reset="reset"
        />

        <Link
            :href="saveSearchUrl"
            class="inline-block text-sm font-medium text-slate-700 underline underline-offset-4 hover:text-slate-900">
            Save this search for alerts
        </Link>

        <p class="my-4 text-sm text-slate-500" aria-live="polite">
            <span v-if="processing">Loading…</span>
            <span v-else>{{ listings.meta.total }} listing(s) found</span>
        </p>

        <div
            v-if="listings.data.length"
            class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
            :class="processing && 'opacity-50 transition'"
        >
            <ListingCard
                v-for="listing in listings.data"
                :key="listing.id"
                :listing="listing"
            />
        </div>

        <p
            v-else
            class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500"
        >
            No listings match those filters.
        </p>

        <Pagination :meta="listings.meta" :links="listings.links" />
    </AppLayout>
</template>
