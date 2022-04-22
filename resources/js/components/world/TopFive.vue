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
    'tribe': ['#', 'player', 'points', 'members', 'villages', 'domination'],
    'player': ['#', 'player', 'points', 'villages', 'domination']
});

const graphdata = ref({'tribe': { }, 'player': { } });
const options = ref({scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, ticks:{ stepSize: 1 } }},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}});

onMounted(() => {
    const url = document.location.href;
    world.value = extract_world(url);

    link.value = { 'tribe': `/${world}/tribes`, 'player': `/${world}/players` };
    GET(`/api/${world.value}`, {'view': 'players'}, (resp) => { updateRankAndGraph('player', resp); });
    GET(`/api/${world.value}`, {'view': 'tribes'}, (resp) => { updateRankAndGraph('tribe', resp); });

});

function updateRankAndGraph(endpoint, obj)
{
    const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)'];
    //Setup local variables
    const top5 = obj['top5'];
    const history = obj['history'];
    const count = obj['count'];
    const datasets = [];

    //Setup table
    top5.forEach((obj) => {
        //Setup local variables
        const name = `<a href="/${world.value}/${endpoint}?id=${obj['id']}">${obj['name']}</a>`;
        const villages =  endpoint === 'tribe' ? format(obj['villages']) : `<a href="/${world.value}/${endpoint}?id=${obj['id']}">${format(obj['villages'])}</a>`;
        const members = `<a href="/${world.value}/${endpoint}?id=${obj['id']}">${obj['members']}</a>`;
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
        
        datasets.push({
            'label': obj['name'],
            'data': [],
            'backgroundColor': colorpalette[index],
            'borderColor': colorpalette[index],
            'showLine': true
        });
    });

    history.forEach((obj) => {
        const index = obj['rankno'] - 1;
        const data = datasets[index].data;
        const timestamp = new Date(obj['timestamp']).getTime();
        data.push({ 'x': timestamp, 'y': obj['rankno']});
    });

    graphdata.value[endpoint]['datasets'] = datasets;
}

</script>

<template>
    <div class="container" style="width: 50%;">
        
        <div class="text-center">
            <strong><a>Top 5 tribes </a></strong><a :href="link.tribe">show all</a>
        </div>

        <table-component :headers="headers.tribe" :rows="rows.tribe"></table-component>
        
        <div class="mt-5 mb-2">
            <ScatterChart :chartData="graphdata.tribe" :options="options"/>
        </div>

    </div>

    <div class="container" style="width: 50%;">
        
        <div class="text-center">
            <strong><a>Top 5 players </a></strong><a :href="link.player">show all</a>
        </div>

        <table-component :headers="headers.player" :rows="rows.player"></table-component>
        
        <div class="mt-5 mb-2">
            <ScatterChart :chartData="graphdata.player" :options="options"/>
        </div>

    </div>
</template>