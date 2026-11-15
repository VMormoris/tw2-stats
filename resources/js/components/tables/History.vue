<script setup>
import { ref } from 'vue';
import InteractiveTable from './templates/InteractiveTable.vue';

const props = defineProps({
    'endpoint': String,
    'id': Number,
    'name': String
});

//Constant(s)
const headers = ref({
    'tribe': ['#', 'Points', 'Members', 'Villages', 'OBP', 'DBP', 'TBP', 'Rank', 'Victory Points', 'Timestamp'],
    'player': ['#', 'Tribe', 'Points', 'Villages', 'OBP', 'DBP', 'TBP', 'Rank', 'Victory Points', 'Timestamp'],
    'village': ['#', 'Name', 'Owner', 'Points', 'Timestamp']
});

function Update(obj)
{
    const url = document.location.href;
    const world = extract_world(url);
    const reqobj = { 'id': props['id'], 'view': 'history', 'page': obj['page'], 'items': obj['items'] };
    GET(`/api/${world}/${props.endpoint}`, reqobj, (resp) => {
        obj['loading'] = false;
        build_url_params(reqobj);

        obj['total'] = Math.ceil(resp['total']/obj['items']);
        const rows = resp['data'];
        const offset = (obj['page'] - 1) * obj['items'] + 1;
        //The API returns one extra (older) row, which is only used for calculating the differences
        const shown = Math.min(rows.length, obj['items']);
        for(let i = 0; i < shown; i++)
        {
            const row = rows[i];
            const nextrow = rows[i+1];//Undefined for the oldest record, which has nothing to compare with
            if(props['endpoint'] === 'tribe')
                UpdateTribe(row, nextrow, offset+i);
            else if(props['endpoint'] === 'player')
                UpdatePlayer(row, nextrow, offset+i);
            else if(props['endpoint'] === 'village')
                UpdateVillage(row, nextrow, offset+i);
        }

        function UpdateTribe(row, nextrow, num)
        {
            const pointstr = `<a>${format(row['points'])}</a>${change(row, nextrow, 'points')}`;
            const memberstr = `<a>${format(row['members'])}</a>${change(row, nextrow, 'members')}`;
            const villstr = `<a>${format(row['villages'])}</a>${change(row, nextrow, 'villages')}`;
            const offbashstr = `<a>${format(row['offbash'])}</a>${change(row, nextrow, 'offbash')}`;
            const defbashstr = `<a>${format(row['defbash'])}</a>${change(row, nextrow, 'defbash')}`;
            const totalbashstr = `<a>${format(row['totalbash'])}</a>${change(row, nextrow, 'totalbash')}`;
            const vpstr = `<a>${format(row['vp'])}</a>${change(row, nextrow, 'vp')}`;

            obj['rows'].push({
                'num': num,
                'points': pointstr,
                'members': memberstr,
                'villages': villstr,
                'offbash': offbashstr,
                'defbash': defbashstr,
                'totalbash': totalbashstr,
                'rankno': format(row['rankno']),
                'vp': vpstr,
                'timestamp': row.timestamp
            });
        }

        function UpdatePlayer(row, nextrow, num)
        {
            const pointstr = `<a>${format(row['points'])}</a>${change(row, nextrow, 'points')}`;
            const villstr = `<a>${format(row['villages'])}</a>${change(row, nextrow, 'villages')}`;
            const offbashstr = `<a>${format(row['offbash'])}</a>${change(row, nextrow, 'offbash')}`;
            const defbashstr = `<a>${format(row['defbash'])}</a>${change(row, nextrow, 'defbash')}`;
            const totalbashstr = `<a>${format(row['totalbash'])}</a>${change(row, nextrow, 'totalbash')}`;
            const vpstr = `<a>${format(row['vp'])}</a>${change(row, nextrow, 'vp')}`;
            const tribestr = row['tid'] === 0 ? row['new tribe'] : `<a href="/${world}/tribe?id=${row['tid']}">${row['new tribe']}</a>`;

            obj['rows'].push({
                'num': num,
                'tribe': tribestr,
                'points': pointstr,
                'villages': villstr,
                'offbash': offbashstr,
                'defbash': defbashstr,
                'totalbash': totalbashstr,
                'rankno': format(row['rankno']),
                'vp': vpstr,
                'timestamp': row.timestamp
            });
        }

        function UpdateVillage(row, nextrow, num)
        {
            const pointstr = `<a>${format(row['points'])}</a>${change(row, nextrow, 'points')}`;
            const owner = row['pid'] === 0 ? row['owner'] : `<a href="/${world}/player?id=${row['pid']}">${row['owner']}</a>`;
            
            obj['rows'].push({
                'num': num,
                'name': `${row['name']} (${row['x']}|${row['y']})`,
                'owmer': owner,
                'points': pointstr,
                'timestamp': row.timestamp
            });
        }
    });
}

function change(row, nextrow, key)
{
    return nextrow ? pretify(row[key] - nextrow[key]) : '';
}

function pretify(offset)
{
    let style = 'text-warning';
    let symbol = '='
    if(offset > 0)
    {
        style = 'text-success';
        symbol = '+';
    }
    else if(offset < 0)
    {
        style = 'text-danger';
        symbol = '';
    }
    return `<a class="${style}">(${symbol}${format(offset)})</a>`;
}

</script>

<template>
<div class="text-center" style="padding-top: 1rem">
    <h1>{{ props.name }}'s History</h1>
</div>
<interactive-table
    :headers="headers[props.endpoint]"
    :hasSearchbar="false"
    :isHistory="true"
    @update="Update">
</interactive-table>
</template>