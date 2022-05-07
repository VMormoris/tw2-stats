<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'endpoint': String,
    'title': String,
    'placeholder': String
});

//Constant(s)
const headers = ref({
    'tribes': ['#', 'Name', 'Tag', 'Points', 'Members', 'Villages', 'OBP', 'DBP', 'TBP', 'Victory Points'],
    'players': ['#', 'Name', 'Tribe', 'Points', 'Villages', 'OBP', 'DBP', 'TBP', 'Victory Points'],
    'villages': ['#', 'Name', 'Points', 'Old Owner', 'New Owner', 'Old Tribe', 'New Tribe', 'Timestamp']
});

function Update(obj)
{
    const nochange = ' <a class="text-warning no-u"><span></span></a>';
    const increase = ' <a class="text-success no-u">&uarr;</a>';
    const decrease = ' <a class="text-danger no-u">&darr;</a>';
    const url = document.location.href;
    const world = extract_world(url);
    const reqobj = {'page': obj['page'], 'items': obj['items'], 'filter': obj['filter'] };
    GET(`/api/${world}/${props.endpoint}`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];
        const details = resp.hasOwnProperty('details') ? resp['details'] : null;
        if(props.endpoint != 'villages')
        {
            details.forEach((item) => {
                const id = item['id'];
                delete item.id;
                data.forEach((row) => { 
                    if(row['id'] === id)
                    {
                        for(const prop in item)
                            row[prop] = item[prop];
                    }
                });
            });
        }

        const offset = (obj['page'] - 1) * obj['items'];
        rows.forEach((row, index) => {
            if(props.endpoint === 'tribes')
                UpdateTribes(row);
            else if(props.endpoint === 'players')
                UpdatePlayers(row);
            else if(props.endpoint === 'villages')
                UpdateVillages(row, offset+index+1);
        });

        function UpdateVillages(row, num)
        {
            const oldowner = row['prevpid'] === 0 ? row['old owner'] : `<a href="/${world}/player?id=${row['prevpid']}">${row['old owner']}</a>`;
            const oldtribe = row['prevtid'] === 0 ? row['old tribe'] : `<a href="/${world}/tribe?id=${row['prevtid']}">${row['old tribe']}</a>`;
            const newtribe = row['nexttid'] === 0 ? row['new tribe'] : `<a href="/${world}/tribe?id=${row['nexttid']}">${row['new tribe']}</a>`;
            obj['rows'].push({
                'num': format(num),
                'name': `<a href="/${world}/village?id=${row['vid']}">${row['name']} (${row['x']}|${row['y']})</a>`,
                'points': format(row['points']),
                'oldowner': oldowner, 'newowner': `<a href="/${world}/player?id=${row['nextpid']}">${row['new owner']}</a>`,
                'oldtribe': oldtribe, 'newtribe': newtribe,
                'timestamp': row['timestamp']
            });
        }

        function UpdatePlayers(row)
        {
            const villstr = row['villages'] === 0 ? row['villages'] : `<a href="/${world}/player?id=${row['id']}&view=villages">${format(row['villages'])}</a>`;
            const tribestr = row['tid'] === 0 ? row['tname'] : `<a href="/${world}/tribe?id=${row['tid']}">${row['tname']}</a>`;
            const extra = {
                'rankno': details.length === 0 ? nochange : (row['oldrank'] === row['rankno'] ? nochange : (row['oldrank'] < row['rankno'] ? decrease : increase)),
                'points': details.length === 0 ? nochange : (row['oldpoints'] === row['points'] ? nochange : (row['oldpoints'] < row['points'] ? increase : decrease)),
                'villages': details.length === 0 ? nochange : (row['oldvillages'] === row['villages'] ? nochange : (row['oldvillages'] < row['villages'] ? increase : decrease)),
                'offbash': details.length === 0 ? nochange : (row['oldoffbash'] === row['offbash'] ? nochange : (row['oldoffbash'] < row['offbash'] ? increase : decrease)),
                'defbash': details.length === 0 ? nochange : (row['olddefbash'] === row['defbash'] ? nochange : (row['olddefbash'] < row['defbash'] ? increase : decrease)),
                'totalbash': details.length === 0 ? nochange : (row['oldtotalbash'] === row['totalbash'] ? nochange : (row['oldtotalbash'] < row['totalbash'] ? increase : decrease)),
                'vp': details.length === 0 ? nochange : (row['oldvp'] === row['vp'] ? nochange : (row['oldvp'] < row['vp'] ? increase : decrease)),
            };

            obj['rows'].push({
                'num': format(row['rankno']) + extra['rankno'],
                'name': `<a href="/${world}/player?id=${row['id']}">${row['name']}</a>`,
                'tribe': tribestr,
                'points': format(row['points']) + extra['points'],
                'villages': villstr + extra['villages'],
                'offbash': format(row['offbash']) + extra['offbash'], 'defbash': format(row['defbash']) + extra['defbash'], 'totalbash': format(row['totalbash']) + extra['totalbash'],
                'vp': format(row['vp']) + extra['vp']
            });        
        }

        function UpdateTribes(row)
        {
            const memberstr = row['members'] === 0 ? row['members'] : `<a href="/${world}/tribe?id=${row['id']}&view=members">${row['members']}</a>`;
            const extra = {
                'rankno': details.length === 0 ? nochange : (row['oldrank'] === row['rankno'] ? nochange : (row['oldrank'] < row['rankno'] ? decrease : increase)),
                'points': details.length === 0 ? nochange : (row['oldpoints'] === row['points'] ? nochange : (row['oldpoints'] < row['points'] ? increase : decrease)),
                'members': details.length === 0 ? nochange : (row['oldmembers'] === row['members'] ? nochange : (row['oldmembers'] < row['members'] ? increase : decrease)),
                'villages': details.length === 0 ? nochange : (row['oldvillages'] === row['villages'] ? nochange : (row['oldvillages'] < row['villages'] ? increase : decrease)),
                'offbash': details.length === 0 ? nochange : (row['oldoffbash'] === row['offbash'] ? nochange : (row['oldoffbash'] < row['offbash'] ? increase : decrease)),
                'defbash': details.length === 0 ? nochange : (row['olddefbash'] === row['defbash'] ? nochange : (row['olddefbash'] < row['defbash'] ? increase : decrease)),
                'totalbash': details.length === 0 ? nochange : (row['oldtotalbash'] === row['totalbash'] ? nochange : (row['oldtotalbash'] < row['totalbash'] ? increase : decrease)),
                'vp': details.length === 0 ? nochange : (row['oldvp'] === row['vp'] ? nochange : (row['oldvp'] < row['vp'] ? increase : decrease)),
            };
            
            obj['rows'].push({
                'num': format(row['rankno']) + extra['rankno'],
                'name': `<a href="/${world}/tribe?id=${row['id']}">${row['name']}</a>`, 'tag': row['tag'],
                'points': format(row['points']) + extra['points'],
                'members': memberstr + extra['members'],
                'villages': format(row['villages']) + extra['villages'],
                'offbash': format(row['offbash']) + extra['offbash'], 'defbash': format(row['defbash']) + extra['defbash'], 'totalbash': format(row['totalbash']) + extra['totalbash'],
                'vp': format(row['vp']) + extra['vp']
            });
        }
    });
}

</script>

<template>

<div class="text-center mt-2">
    <h1>{{ props.title }}</h1>
</div>

<interactive-table
    :headers="headers[props.endpoint]"
    :searchBarHint="props.placeholder"
    @update="Update">
</interactive-table>

</template>