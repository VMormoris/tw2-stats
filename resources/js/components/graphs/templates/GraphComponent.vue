<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    type: String,
    datasets: Array,
    options: Object
});

const canvas = ref(null);

watch(props.datasets, () => {
    const data = JSON.parse(JSON.stringify(props.datasets));
    const options = JSON.parse(JSON.stringify(props.options));
    console.log(props.options);
    const ctx = canvas.value.getContext('2d');
    const graph = new Chart(ctx, {
        'type': props.type,
        'data': data,
        'options': options
    });
});
</script>


<template>
    <canvas ref="canvas"></canvas>
</template>