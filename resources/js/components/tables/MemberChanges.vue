<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'id': Number,
    'name': String
});

//Constant(s)
const headers = ref(['#', 'Name', 'Action', 'Points', 'Villages', 'OBP', 'DBP', 'TBP', 'Rank', 'Victory Points', 'Timestamp']);

function Update(obj)
{
    const joined = '<b><a class="text-success">joined</a></b>';
    const left = '<b><a class="text-danger">left</a></b>';

    const url = document.location.href;
    const world = extract_world(url);

    const reqobj = { 'id': props.id, 'view': 'changes', 'page': obj['page'], 'items': obj['items'] };
    GET(`/api/${world}/tribe`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];

        const offset = (obj['page'] - 1) * obj['items'] + 1;
        rows.forEach((row, index) => {
            const playername = row['pid'] == 0 ? row['player'] : `<a href="/${world}/player?id=${row['pid']}">${row['player']}</a>`;
            const action = row['nexttid'] == props.id ? joined : left;
            obj['rows'].push({
                'num': offset + index,
                'player': playername,
                'action': action,
                'points': format(row['points']),
                'villages': format(row['villages']),
                'offbash': format(row['offbash']),
                'defbash': format(row['defbash']),
                'totalbash': format(row['totalbash']),
                'rankno': format(row['rankno']),
                'vp': format(row['vp']),
                'timestamp': row['timestamp']
            });
        });
    });
}
</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Member Changes</h1>
</div>
<interactive-table
    :headers="headers"
    :hasSearchbar="false"
    @update="Update">
</interactive-table>
</template>