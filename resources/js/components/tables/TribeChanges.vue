<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'id': Number,
    'name': String
});

//Constant(s)
const headers = ref(['#', 'Old Tribe', 'New Tribe', 'Points', 'Villages', 'OBP', 'DBP', 'TBP', 'Rank', 'Victory Points', 'Timestamp']);

function Update(obj)
{
    const url = document.location.href;
    const world = extract_world(url);

    const reqobj = { 'id': props.id, 'view': 'changes', 'page': obj['page'], 'items': obj['items'] };
    GET(`/api/${world}/player`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];

        const offset = (obj['page'] - 1) * obj['items'] + 1;
        rows.forEach((row, index) => {
            const oldtribe = row['prevtid'] === 0 ? row['old tribe'] : `<a href="/${world}/tribe?id=${row['prevtid']}">${row['old tribe']}</a>`;
            const newtribe = row['nexttid'] === 0 ? row['new tribe'] : `<a href="/${world}/tribe?id=${row['nexttid']}">${row['new tribe']}</a>`;

            obj['rows'].push({
                'num': offset + index,
                'old tribe': oldtribe,
                'new tribe': newtribe,
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
    <h1>{{ props.name }}'s Tribe Changes</h1>
</div>
<interactive-table
    :headers="headers"
    :hasSearchbar="false"
    @update="Update">
</interactive-table>
</template>