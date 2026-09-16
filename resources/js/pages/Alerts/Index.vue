<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "../../components/AppLayout.vue";

defineProps({
  alerts: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <Head title="Alerts" />

  <AppLayout
    heading="Alerts"
    subheading="New listings matching your saved searches."
  >
    <div class="mb-6">
      <Link
        href="/saved-searches"
        class="mb-6 block text-sm text-slate-500 hover:text-slate-900 underline"
      >
        View saved searches →
      </Link>
    </div>

    <p
      v-if="alerts.length === 0"
      class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-slate-500"
    >
      You don't have any alerts yet.
    </p>

    <div v-else class="space-y-4">
      <article
        v-for="alert in alerts"
        :key="alert.listing.id"
        class="rounded-lg border border-gray-200 bg-white p-6"
      >
        <p class="mb-2 text-sm font-medium text-gray-500">New property match</p>

        <h2 class="text-lg font-semibold text-gray-900">
          {{ alert.listing.address_line_1 }}
        </h2>

        <p class="mt-2 text-gray-600">
          £{{ Number(alert.listing.price).toLocaleString() }}

          · {{ alert.listing.bedrooms }} bedrooms ·
          {{ alert.listing.property_type_label }}
        </p>

        <p class="mt-1 text-sm text-gray-500">
          {{ alert.listing.city }}
        </p>

        <div class="mt-4">
          <p class="text-sm font-medium text-gray-700">
            Matched your saved search<span
              v-if="alert.saved_searches.length !== 1"
              >es</span
            >:
          </p>

          <ul class="mt-2 space-y-1 text-sm text-gray-500">
            <li v-for="search in alert.saved_searches" :key="search.id">
              <span v-if="search.property_type_label">
                {{ search.property_type_label }}
              </span>

              <span v-if="search.region">
                <span v-if="search.property_type_label"> · </span>

                {{ search.region }}
              </span>

              <span v-if="search.min_bedrooms !== null">
                <span v-if="search.property_type_label || search.region">
                  ·
                </span>

                {{ search.min_bedrooms }}+ bedrooms
              </span>

              <span v-if="search.max_price !== null">
                <span
                  v-if="
                    search.property_type_label ||
                    search.region ||
                    search.min_bedrooms !== null
                  "
                >
                  ·
                </span>

                up to £{{ Number(search.max_price).toLocaleString() }}
              </span>
            </li>
          </ul>
        </div>

        <Link
          :href="`/listings/${alert.listing.id}`"
          class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-800"
        >
          View listing
        </Link>
      </article>
    </div>
  </AppLayout>
</template>