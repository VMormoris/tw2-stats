<script setup>
import { ref } from 'vue';
import TableComponent from '../../tables/templates/TableComponent.vue';
import { PieChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);

const props = defineProps({
    'subtitle': String,
    'headers': Array,
    'rows': Array,
    'graphData': Object,
    'loading': Boolean
});
const options = ref({ 'responsive': true, 'plugins': { 'legend': { 'position': 'top' } } });
</script>

<template>
<div v-if="props.loading" class="container text-center" style="height: 475px">
    <a>
        <i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>
        <span class="sr-only">Loading...</span>
    </a>
</div>
<div v-else class="row mt-4 mb-2">
    <div class="col">
        <div class="text-center">
            <h2>{{ props.subtitle }}</h2>
        </div>
        <table-component
            :headers="props.headers"
            :rows="props.rows"
            :loading="props.loading">
        </table-component>
    </div>
    <div class="col">
        <PieChart :chartData="graphData" :options="options"/>
    </div>
</div>
</template>