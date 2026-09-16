<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../components/AppLayout.vue';
import ListingFilters from '../../components/ListingFilters.vue';
import BackButton from '../../components/BackButton.vue';

const props = defineProps({
    filters: { type: Object, required: true },
    branches: { type: Array, required: true },
    propertyTypes: { type: Array, required: true },
});

const processing = ref(false);

const form = ref({
    property_type: props.filters.property_type ?? '',
    region: props.filters.region ?? '',
    min_bedrooms: props.filters.min_bedrooms ?? '',
    max_price: props.filters.max_price ?? '',
});

function save() {
    router.post('/saved-searches', form.value, {
        onStart: () => (processing.value = true),
        onFinish: () => (processing.value = false),
    });
}
</script>

<template>
    <Head title="Save search" />

    <AppLayout
        heading="Save search for alerts"
        subheading="We'll alert you when a new listing matches these criteria."
    >
 
        <BackButton label="← Back to listings" />
        <ListingFilters
            v-model="form"
            :branches="branches"
            :property-types="propertyTypes"
            :processing="processing"
            submit-label="Save search"
            :show-clear-button="false"
            @submit="save"
        />
    </AppLayout>
</template>
