<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'endpoint': String,
    'id': Number,
    'name': String
});

const headers = ref(['#', 'Name', 'Points', 'Old Owner', 'New Owner', 'Old Tribe', 'New Tribe', 'Timestamp']);

function Update(obj)
{
    const url = document.location.href;
    const world = extract_world(url);
    const reqobj = {'id': props.id, 'view': 'conquers', 'page': obj['page'], 'items': obj['items'], 'show': obj['show'], 'filter': obj['filter'] };
    GET(`/api/${world}/${props.endpoint}`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);


        obj['total'] = Math.ceil((props.endpoint === 'village' ? resp['total'] : resp[obj['show']]['total'])/obj['items']);
        const rows = props.endpoint === 'village' ? resp['data'] : resp[obj['show']]['data'];
        const offset = (obj['page'] - 1) * obj['items']+1;
        rows.forEach((row, index) => {
            const oldowner = row['prevpid'] === 0 ? row['old owner'] : (row['prevpid'] === props.id && props.endpoint === 'player' ? `<b>${props.name}</b>` : `<a href="/${world}/player?id=${row['prevpid']}">${row['old owner']}</a>`);
            const newowner = row['nextpid'] === props.id && props.endpoint === 'player' ? `<b>${props.name}</b>` : `<a href="/${world}/player?id=${row['nextpid']}">${row['new owner']}</a>`;
            const oldtribe = row['prevtid'] === 0 ? row['old tribe'] : (row['prevtid'] === props.id && props.endpoint === 'tribe' ? `<b>${props.name}</b>` : `<a href="/${world}/tribe?id=${row['prevtid']}">${row['old tribe']}</a>`);
            const newtribe = row['nexttid'] === 0 ? row['new tribe'] : (row['nexttid'] === props.id && props.endpoint === 'tribe' ? `<b>${props.name}</b>` : `<a href="/${world}/tribe?id=${row['nexttid']}">${row['new tribe']}</a>`);
            const name = `<a href="/${world}/village?id=${row['vid']}">${row['name']} (${row['x']}|${row['y']})</a>`;

            obj['rows'].push({
                'num': format(offset+index),
                'name': name,
                'points': format(row['points']),
                'oldowner': oldowner, 'newowner': newowner,
                'oldtribe': oldtribe, 'newtribe': newtribe,
                'timestamp': row['timestamp']
            });
        });
    });
}

</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Conquers</h1>
</div>
<interactive-table
    :headers="headers"
    :searchBarHint="'Search Village, Player or Tribe'"
    :hasShow="props.endpoint != 'village'"
    :hasInternals="props.endpoint === 'tribe'"
    @update="Update">
</interactive-table>
</template>
