<script setup>
import { Link } from '@inertiajs/vue3';
import { formatPrice } from '../format';

defineProps({
    label: { type: String, required: true },
});

function goBack() {

window.history.back();

}
</script>

<template>
      <button
    type="button"
    class="mb-6 inline-block text-sm text-slate-500 hover:text-slate-900"
    @click="goBack"
>
     {{ label }}
    </button>
</template>
