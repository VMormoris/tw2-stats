<script setup>
import { ref, onMounted } from 'vue';
import TableComponent from '../tables/templates/TableComponent.vue';
import { ScatterChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);

const world = ref('');
const link = ref({ 'tribe': '', 'player': '' });
const rows = ref({ 'tribe': [], 'player': []});
const headers = ref({
    'tribe': ['#', 'Name', 'Points', 'Members', 'Villages', 'Domination'],
    'player': ['#', 'Name', 'Points', 'Villages', 'Domination']
});

const loadingTribe = ref(true);
const loadingPlayer = ref(true);
const graphdata = ref({'tribe': { }, 'player': { } });
const options = ref({scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, ticks:{ stepSize: 1 } }}});

onMounted(() => {
    const url = document.location.href;
    world.value = extract_world(url);

    link.value = { 'tribe': `/${world.value}/tribes`, 'player': `/${world.value}/players` };
    GET(`/api/${world.value}`, {'view': 'players'}, (resp) => { updateRankAndGraph('player', resp); });
    GET(`/api/${world.value}`, {'view': 'tribes'}, (resp) => { updateRankAndGraph('tribe', resp); });

});

function updateRankAndGraph(endpoint, obj)
{
    if(endpoint === 'player')
        loadingPlayer.value = false;
    else if(endpoint === 'tribe')
        loadingTribe.value = false;

    const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)'];
    //Setup local variables
    const top5 = obj['top5'];
    const history = obj['history'];
    const count = obj['count'];
    const indexes = {};
    const datasets = [];

    //Setup table
    top5.forEach((obj) => {
        //Setup local variables
        const name = `<a href="/${world.value}/${endpoint}?id=${obj['id']}">${obj['name']}</a>`;
        const villages =  endpoint === 'tribe' ? format(obj['villages']) : `<a href="/${world.value}/${endpoint}?id=${obj['id']}&view=villages">${format(obj['villages'])}</a>`;
        const members = `<a href="/${world.value}/${endpoint}?id=${obj['id']}&view=members">${obj['members']}</a>`;
        const index = obj['rankno'] - 1;

        const row = {
            'num': obj['rankno'],
            'name': name,
            'points': format(obj['points'])
        };

        if(endpoint === 'tribe')
            row['members'] = members;
        row['villages'] = villages;
        row['domination'] = globals.wcond === 'Domination' ? format(obj['villages']/count*100) + '%' : format(obj['vp']);
        rows.value[endpoint].push(row);
        
        indexes[obj['id']] = datasets.length;
        datasets.push({
            'label': obj['name'],
            'data': [],
            'backgroundColor': colorpalette[index],
            'borderColor': colorpalette[index],
            'showLine': true,
            'pointRadius': 0
        });
    });

    history.forEach((obj) => {
        const data = datasets[indexes[obj['id']]].data;
        const timestamp = new Date(obj['timestamp']).getTime();
        data.push({ 'x': timestamp, 'y': obj['rankno']});
    });

    graphdata.value[endpoint]['datasets'] = datasets;
}

</script>

<template>
    <div class="container" style="width: 50%;">
        
        <div class="text-center">
            <strong><a style="font-size: 20px">Top 5 tribes </a></strong><a style="font-size: 20px" :href="link.tribe">show all</a>
        </div>

        <div class="mt-2">
            <table-component :headers="headers.tribe" :rows="rows.tribe" :loading="loadingTribe"></table-component>
        </div>

        <div class="mt-5 mb-2" v-if="!loadingTribe">
            <ScatterChart :chartData="graphdata.tribe" :options="options"/>
        </div>

    </div>

    <div class="container" style="width: 50%;">
        
        <div class="text-center">
            <strong><a style="font-size: 20px">Top 5 players </a></strong><a style="font-size: 20px" :href="link.player">show all</a>
        </div>

        <div class="mt-2">
            <table-component :headers="headers.player" :rows="rows.player" :loading="loadingPlayer"></table-component>
        </div>
        <div class="mt-5 mb-2" v-if="!loadingPlayer">
            <ScatterChart :chartData="graphdata.player" :options="options"/>
        </div>

    </div>
</template>