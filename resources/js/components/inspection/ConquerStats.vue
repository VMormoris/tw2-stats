<script setup>
import { ref, onMounted } from 'vue';
import StatsComponent from './templates/StatsComponent.vue';

const props = defineProps({
    'endpoint': String,
    'id': Number,
    'name': String
});

const headers = ref({
    'gvt': ['#', 'Tribe', 'Gains', 'Percentage'],
    'lvt': ['#', 'Tribe', 'Losses', 'Percentage'],
    'gvp': ['#', 'Player', 'Gains', 'Percentage'],
    'lvp': ['#', 'Player', 'Losses', 'Percentage'],
});

const loadingFlags = ref({
    'gvt': true,
    'lvt': true,
    'gvp': true,
    'lvp': true
});

const rows = ref({
    'gvt': [],
    'lvt': [],
    'gvp': [],
    'lvp': []
});

const subtitles = ref({
    'gvt': 'Gains from Tribes',
    'lvt': 'Losses from Tribes',
    'gvp': 'Gains from Players',
    'lvp': 'Losses from Players'
});

const graphData = ref({
    'gvt': { 'labels': [], 'datasets': [{ 'data': [], 'backgroundColor': [] }] },
    'lvt': { 'labels': [], 'datasets': [{ 'data': [], 'backgroundColor': [] }] },
    'gvp': { 'labels': [], 'datasets': [{ 'data': [], 'backgroundColor': [] }] },
    'lvp': { 'labels': [], 'datasets': [{ 'data': [], 'backgroundColor': [] }] }
});

onMounted(() => {
    const world = extract_world(document.location.href);
    const url = `/api/${world}/${props.endpoint}`;
    build_url_params({ 'id': props.id , 'view': 'stats' });
    GET(url, { 'id': props.id, 'view': 'stats', 'spec': 'gvt' }, (resp) => { updateStats(resp, 'gvt'); });
    GET(url, { 'id': props.id, 'view': 'stats', 'spec': 'lvt' }, (resp) => { updateStats(resp, 'lvt'); });
    GET(url, { 'id': props.id, 'view': 'stats', 'spec': 'gvp' }, (resp) => { updateStats(resp, 'gvp'); });
    GET(url, { 'id': props.id, 'view': 'stats', 'spec': 'lvp' }, (resp) => { updateStats(resp, 'lvp'); });
});

function updateStats(resp, key)
{
    loadingFlags.value[key] = false;
    //Constant pie color palette
    const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(201, 203, 207)'];
    const prop = key === 'gvp' || key === 'gvt' ? 'gains' : 'losses';
    const link = key === 'gvt' || key === 'lvt' ? 'tribe' : 'player';
    const world = extract_world(document.location.href);

    const tdata = resp[key];
    let total = 0;
    tdata.forEach((row) => { total += parseInt(row[prop]); });
    subtitles.value[key] += ` (${total})`;

    const datapoints = [];
    const colors = [];
    tdata.forEach((row, index) => {
        const name = row['id'] === 0 ? row['name'] : `<a href="/${world}/${link}?id=${row['id']}">${row['name']}</a>`;
        rows.value[key].push({
            'num': index + 1,
            'name': name,
            'prop': row[prop],
            'percentage': format(row[prop] / total * 100) + '%'
        });

        //Fill data to use in pie later
        graphData.value[key]['labels'].push(row['name']);
        
        datapoints.push(parseInt(row[prop]));
        colors.push(colorpalette[index]);
    });

    graphData.value[key]['datasets'][0]['data'] = datapoints;
    graphData.value[key]['datasets'][0]['backgroundColor'] = colors;
}
</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Conquer Stats</h1>
</div>
<stats-component
    :subtitle="subtitles.gvt"
    :headers="headers.gvt"
    :rows="rows.gvt"
    :loading="loadingFlags.gvt"
    :graphData="graphData.gvt">
</stats-component>
<stats-component
    :subtitle="subtitles.lvt"
    :headers="headers.lvt"
    :rows="rows.lvt"
    :loading="loadingFlags.lvt"
    :graphData="graphData.lvt">
</stats-component>
<stats-component
    :subtitle="subtitles.gvp"
    :headers="headers.gvp"
    :rows="rows.gvp"
    :loading="loadingFlags.gvp"
    :graphData="graphData.gvp">
</stats-component>
<stats-component
    :subtitle="subtitles.lvp"
    :headers="headers.lvp"
    :rows="rows.lvp"
    :loading="loadingFlags.lvp"
    :graphData="graphData.lvp">
</stats-component>
</template>