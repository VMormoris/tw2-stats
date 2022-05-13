<script setup>
import { ref, onMounted } from 'vue';
import InfoTable from '../tables/templates/InfoTable.vue';
import { ScatterChart, BarChart } from 'vue-chart-3';
import { Chart, registerables } from "chart.js";
Chart.register(...registerables);

const props = defineProps({
    'endpoint': String,
    'name': String,
    'id': Number
});

const info = ref([]);
const loading = ref(true);
const graphsData = ref({
    'points': { 'datasets': [{ 'label': 'Points', 'backgroundColor': 'rgba(255, 99, 132, 0.2)', 'borderColor': 'rgb(225,99,132)', 'fill': true, 'showLine': true, 'pointRadius': 0, 'data': [] }] },
    'rank': { 'datasets': [{ 'label': 'Rank', 'backgroundColor': 'rgba(255, 99, 132, 0.2)', 'borderColor': 'rgb(225,99,132)', 'showLine': true, 'pointRadius': 0, 'data': [] }] },
    'bash': {
        'datasets':
        [
            { 'label': 'Offensive', 'backgroundColor': 'rgba(255, 66, 32, 0.2)', 'borderColor': 'rgb(225,66,32)', 'showLine': true, 'data': [] },
            { 'label': 'Defensive', 'backgroundColor': 'rgba(75, 192, 102, 0.2)', 'borderColor': 'rgb(75, 192, 102)', 'showLine': true, 'data': [] },
            { 'label': 'Total', 'backgroundColor': 'rgba(54, 162, 235, 0.2)', 'borderColor': 'rgb(54, 162, 235)', 'showLine': true, 'data': [] }
        ]
    },
    'villages': { 'labels': [], 'datasets':
        [
            { 'label': 'Same number of villages', 'data': [], 'backgroundColor': 'rgb(255, 205, 86)' },
            { 'label': 'Gain villages', 'data': [], 'backgroundColor': 'rgb(75, 192, 102)' },
            { 'label': 'Lost villages', 'data': [], 'backgroundColor': 'rgb(225, 66, 32)' },
        ]
    }
});
const options = ref({
    'points': {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}}},
    'bash': {scales:{x:{ticks:{callback: function(val, index) { return index % 3 === 0 ? createDateLabel(parseInt(val)) : ''; }}}}, plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || ''; return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}},
    'villages': {scales:{x:{ticks:{callback: function(val, index) { return createDateLabel(this.getLabelForValue(val)); }}}, y:{ suggestedMin: -3, suggestedMax: 3 }},plugins:{tooltip:{callbacks:{title: function(ctx) { return ctx[0].dataset.label; },label: function(ctx){const timestamp = parseInt(ctx.label);return (ctx.parsed.y == 0.05 ? 0 : format(ctx.parsed.y)) + ' villages at ' + asString(timestamp);}}}}},
    'rank': null
});

onMounted(() => {
    const url = document.location.href;
    const world = extract_world(url);

    const reqobj = { 'id': props.id };
    GET(`/api/${world}/${props.endpoint}`, reqobj, (resp) => {
        loading.value = false;
        if(props.endpoint === 'tribe')
            TribeInfo(resp['details']);
        else if(props.endpoint === 'player')
            PlayerInfo(resp['details']);
        else
            VillageInfo(resp['details']);

        const gd = resp['graphs_data'];
        if(props.endpoint === 'village')
        {
            const datapoints = []; 
            gd.forEach((obj) => {
                const timestamp = new Date(obj['timestamp']).getTime();
                datapoints.push({ 'x': timestamp, 'y': obj['points'] });
            });
            graphsData.value['points']['datasets'][0]['data'] = datapoints;
            return;
        }

        const prevbash = { 'obp': 0, 'dbp': 0, 'tbp': 0 };
        gd['general'].forEach((obj, index) => {
            const timestamp = new Date(obj['timestamp']).getTime();
            graphsData.value['points']['datasets'][0]['data'].push({ 'x': timestamp, 'y': obj['points'] });
            graphsData.value['rank']['datasets'][0]['data'].push({ 'x': timestamp, 'y': obj['rankno'] });
            if(index > 0)
            {
                graphsData.value['bash']['datasets'][0]['data'].push({ 'x': timestamp, 'y': obj['offbash'] - prevbash['obp'] });
                graphsData.value['bash']['datasets'][1]['data'].push({ 'x': timestamp, 'y': obj['defbash'] - prevbash['dbp'] });
                graphsData.value['bash']['datasets'][2]['data'].push({ 'x': timestamp, 'y': obj['totalbash'] - prevbash['tbp'] });
            }
            else
            {
                prevbash['obp'] = obj['offbash'];
                prevbash['dbp'] = obj['defbash'];
                prevbash['tbp'] = obj['totalbash'];
            }
        });
        
        for(let i = 1; i < gd['villages'].length; i++)
        {
            const now = gd['villages'][i]['villages'];
            const yesterday = gd['villages'][i-1]['villages'];
            const timestamp = new Date(gd['villages'][i]['timestamp']).getTime();
            graphsData.value['villages']['labels'].push(timestamp);
            if(now === yesterday)
                graphsData.value['villages']['datasets'][0]['data'].push({ 'x': timestamp, 'y': 0.05 });
            else if(now > yesterday)
                graphsData.value['villages']['datasets'][1]['data'].push({ 'x': timestamp, 'y': now - yesterday});
            else
                graphsData.value['villages']['datasets'][2]['data'].push({ 'x': timestamp, 'y': now - yesterday});
        }

        {//Ranking graph options
            const datapoints = [];
            graphsData.value['rank']['datasets'][0]['data'].forEach((obj) => { datapoints.push(obj['y']); });
            const min = Math.max(Math.min(...datapoints) - 3, 1);
            const max = Math.max(Math.max(...datapoints) + 2, 5);
            options.value['rank'] = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, suggestedMin: min, suggestedMax: max, ticks:{ stepSize: 1 } }}};
        }

        

        function TribeInfo(details)
        {
            const all = details['conquers']['gains'] + details['conquers']['losses'] - details['conquers']['internals'];
            const conquersendpoint = `/${world}/tribe?id=${props.id}&view=conquers`
            const conquers = `<a href="${conquersendpoint}">${all}</a>(<a href="${conquersendpoint}&show=gains">+${details['conquers']['gains']}</a>,<a href="${conquersendpoint}&show=losses">-${details['conquers']['losses']}</a>) Internally: <a href="${conquersendpoint}&show=internals">${details['conquers']['internals']}</a>`;
            const members = details['members'] === 0 ? 0 : `<a href="/${world}/tribe?id=${props.id}&view=members">${details['members']}</a>`;
            const changes = details['tchanges'] === 0 ? 0 : `<a href="/${world}/tribe?id=${props.id}&view=members">${details['tchanges']}</a>`;
            info.value.push({ 'Rank': details['rankno'] });
            info.value.push({ 'Name': details['name'] });
            info.value.push({ 'Points': format(details['points']) });
            info.value.push({ 'Members': members });
            info.value.push({ 'Average Points per Member': format(parseInt(details['points']/details['members'])) });
            info.value.push({ 'Villages': format(details['villages']) });
            info.value.push({ 'Average Points per Village': format(parseInt(details['points']/details['villages'])) });
            info.value.push({ 'Member Changes': changes });
            info.value.push({ 'Conquers': conquers });
            info.value.push({ 'OBP': format(details['offbash']) });
            info.value.push({ 'DBP': format(details['defbash']) });
            info.value.push({ 'TBP': format(details['totalbash']) });
            info.value.push({ 'Victory Points': format(details['vp']) });
        }
        
        function PlayerInfo(details)
        {
            const all = details['conquers']['gains'] + details['conquers']['losses'];
            const conquersendpoint = `/${world}/player?id=${props.id}&view=conquers`
            const conquers = `<a href="${conquersendpoint}">${all}</a>(<a href="${conquersendpoint}&show=gains">+${details['conquers']['gains']}</a>,<a href="${conquersendpoint}&show=losses">-${details['conquers']['losses']}</a>)`;
            const tribe = details['tid'] === 0 ? details['tname'] : `<a href="/${world}/tribe?id=${details['tid']}">${details['tname']}</a>`;
            const villages = details['villages'] === 0 ? 0 : `<a href="/${world}/player?id=${props.id}&view=villages">${details['villages']}</a>`;
            const changes = details['tchanges'] === 0 ? 0 : `<a href="/${world}/player?id=${props.id}&view=changes">${details['tchanges']}</a>`;
            info.value.push({ 'Rank': details['rankno'] });
            info.value.push({ 'Name': details['name'] });
            info.value.push({ 'Tribe': tribe });
            info.value.push({ 'Points': format(details['points']) });
            info.value.push({ 'Villages': villages });
            info.value.push({ 'Average Points per Village': format(parseInt(details['points']/details['villages'])) });
            info.value.push({ 'Tribe Changes': changes });
            info.value.push({ 'Conquers': conquers });
            info.value.push({ 'OBP': format(details['offbash']) });
            info.value.push({ 'DBP': format(details['defbash']) });
            info.value.push({ 'TBP': format(details['totalbash']) });
            info.value.push({ 'Victory Points': format(details['vp']) });
        }

        function VillageInfo(details)
        {
            const conquers = details['conquers'] === 0 ? '0' : `<a href="/${world}/village?id=${props.id}&view=conquers">${format(details['conquers'])}</a>`;
            info.value.push({ 'ID': details['id'] });
            info.value.push({ 'Name': details['name']});
            info.value.push({ 'Coordinates': `(${details['x']}|${details['y']})` });
            info.value.push({ 'Owner': `<a href="/${world}/player?id=${details['pid']}">${details['owner']}</a>` });
            info.value.push({ 'Points': format(details['points']) });
            info.value.push({ 'Conquers': conquers });
            info.value.push({ 'Province': details['provname'] });
        }
    });
});
</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Overview</h1>
</div>
<div  v-if="loading" class="text-center mt-2">
    <a>
        <i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>
        <span class="sr-only">Loading...</span>
    </a>
</div>
<div v-else class="mt-2">
    <div class="d-flex flex-row">
        <div class="container col">
            <div class="center-content">
                <info-table :info="info"></info-table>
            </div>
        </div>
        <div class="container col">
            <ScatterChart :chartData="graphsData.points" :options="options.points" style="height: 320px"/>
            <ScatterChart v-if="props.endpoint!='village'" :chartData="graphsData.rank" :options="options.rank" style="height: 320px"/>
        </div>
    </div>

    <div class="text-center">Differnce in bash points in last 3 days</div>
    <ScatterChart :chartData="graphsData.bash" :options="options.bash"/>
    <div class="mt-2 mb-2">
        <BarChart :chartData="graphsData.villages" :options="options.villages"/>
    </div>
</div>
</template>