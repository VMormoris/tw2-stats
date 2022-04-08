<script setup>
import { ref, watch } from 'vue';
import GraphComponent from './templates/GraphComponent.vue';

const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)'];

const props = defineProps({
    data: Array
});

const datasets = ref([]);
const options = ref({});
const type = ref('scatter');

watch(props.data, ()=>{
    const indexes = {};
    props.data.forEach((obj) => {
        const id = obj['id'];
        const timestamp = new Date(obj['timestamp']).getTime();

        if(!indexes.hasOwnProperty(id))
        {
            indexes[id] = datasets.value.length;
            const color = colorpalette[obj['rankno'] - 1];
            datasets.value.push({
                'label': obj['name'],
                'data': [],
                'backgroundColor': color,
                'borderColor': color,
                'showLine': true
            });
        }

        datasets.value[indexes[id]].data.push({ 'x': timestamp, 'y': obj['rankno'] });
    });
    options.value = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, ticks:{ stepSize: 1 } }},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}};
    console.log(GraphComponent);
});
</script>

<template>
    <graph-component :type="type" :datasets="datasets" :options="options"></graph-component>
</template>