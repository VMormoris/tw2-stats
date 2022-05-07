<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'id': Number,
    'name': String
});

//Constant(s)
const headers = ref(['#', 'ID', 'Name', 'Points', 'Province']);

function Update(obj)
{
    const url = document.location.href;
    const world = extract_world(url);

    const reqobj = { 'id': props.id, 'view': 'villages', 'page': obj['page'], 'items': obj['items'], 'filter': obj['filter'] };
    GET(`/api/${world}/player`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];
        const offset = (obj['page'] - 1) * obj['items'] + 1;
        rows.forEach((row, index) => {
            const name = `<a href="/${world}/village?id=${row['id']}">${row['name']} (${row['x']}|${row['y']})</a>`
            obj['rows'].push({
                '#': offset + index,
                'id': row['id'],
                'name': name,
                'points': row['points'],
                'province': row['provname']
            })
        });
    });
}

</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Villages</h1>
</div>
<interactive-table
    :headers="headers"
    :searchBarHint="'Search village'"
    @update="Update">
</interactive-table>
</template>