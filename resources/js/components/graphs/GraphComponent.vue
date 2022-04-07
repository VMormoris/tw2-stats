<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    id: String,
    type: String,
    datasets: Array,
    options: Array
});

onMounted(() => {
    const ctx = $(`#${props.id}`);
    const graph = new Chart(ctx, {
        'type': props.type,
        'data': props.datasets,
        'options': props.options
    });
});
</script>


<template>
    <canvas :id="props.id"></canvas>
</template>