<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'id': Number,
    'name': String
});

//Constant(s)
const headers = ref(['#', 'Name', 'Points', 'Villages', 'OBP', 'DBP', 'TBP', 'Rank', 'Victory Points']);

function Update(obj)
{
    const nochange = ' <a class="text-warning no-u"><span></span></a>';
    const increase = ' <a class="text-success no-u">&uarr;</a>';
    const decrease = ' <a class="text-danger no-u">&darr;</a>';
    //A row without a value from yesterday has nothing to be compared against
    const compare = (old, current, higher, lower) => old == null || old === current ? nochange : (old < current ? higher : lower);
    const url = document.location.href;
    const world = extract_world(url);

    const reqobj = { 'id': props.id, 'view': 'members', 'page': obj['page'], 'items': obj['items'], 'filter': obj['filter'] };
    GET(`/api/${world}/tribe`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];
        const details = resp['details'];
        details.forEach((item) => {
            const id = item['id'];
            delete item.id;
            rows.forEach((row) => {
                if(row['id'] === id)
                {
                    for(const prop in item)
                        row[prop] = item[prop];
                }
            });
        });
        
        const offset = (obj['page'] - 1) * obj['items'] + 1;
        rows.forEach((row, index) => {
            const villstr = row['villages'] === 0 ? row['villages'] : `<a href="/${world}/player?id=${row['id']}&view=villages">${format(row['villages'])}</a>`;
            const extra = {
                'rankno': compare(row['oldrank'], row['rankno'], decrease, increase),
                'points': compare(row['oldpoints'], row['points'], increase, decrease),
                'villages': compare(row['oldvillages'], row['villages'], increase, decrease),
                'offbash': compare(row['oldoffbash'], row['offbash'], increase, decrease),
                'defbash': compare(row['olddefbash'], row['defbash'], increase, decrease),
                'totalbash': compare(row['oldtotalbash'], row['totalbash'], increase, decrease),
                'vp': compare(row['oldvp'], row['vp'], increase, decrease),
            };

            obj['rows'].push({
                'num': format(offset+index),
                'name': `<a href="/${world}/player?id=${row['id']}">${row['name']}</a>`,
                'points': format(row['points']) + extra['points'],
                'villages': format(row['villages']) + extra['villages'],
                'offbash': format(row['offbash']) + extra['offbash'], 'defbash': format(row['defbash']) + extra['defbash'], 'totalbash': format(row['totalbash']) + extra['totalbash'],
                'rankno': format(row['rankno']) + extra['rankno'],
                'vp': format(row['vp']) + extra['vp']
            });
        });
    });
}
</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s Members</h1>
</div>
<interactive-table
    :headers="headers"
    :searchBarHint="'Search Member'"
    :hasDetails="true"
    @update="Update">
</interactive-table>
</template>