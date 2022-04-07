<script setup>
import { ref, onMounted } from 'vue';

const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)'];

const props = defineProps({
    datapoints: Array,
    indexes: Array
});

onMounted(() => {
    const datasets = [];

    data.rank.forEach((obj) => { datapoints.push(obj.y); });
    const min = Math.max(Math.min(...datapoints) - 3, 1);
    const max = Math.max(Math.max(...datapoints) + 2, 5);
    const options = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, suggestedMin: min, suggestedMax: max, ticks:{ stepSize: 1 } }},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}};
    
});

</script>

<template>
    
</template>