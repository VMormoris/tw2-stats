<script setup>
import { ref, onMounted } from 'vue';
import Pagination from './templates/Pagination.vue';
import ItemSelection from './templates/ItemSelection.vue';
import TableComponent from './templates/TableComponent.vue';

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

//Variables that change base on user input
const items = ref(12);
const page = ref(1);
const filter = ref('');

//Variables that are been loaded asynchronously from response from server
const total = ref(0);
const rows = ref([]);

//Other Vairables
const loading = ref(true);
let clockHandle = null;

function onItemsChange(newitems)
{
    if(newitems != items.value)
    {
        const offset = items.value * (page.value - 1) + 1;
        items.value = newitems;
        page.value = Math.ceil(offset/newitems);
        Update();
    }
}

function onFilterChange(event)
{
    const str = event.target.value.toLowerCase();
    if(str != filter.value)
    {
        filter.value = str;
        page.value = 1;
        clearTimeout(clockHandle);
        clockHandle = setTimeout(Update, 500);
    }
}

function onPageChange(newpage)
{
    if(newpage != page.value)
    {
        page.value = newpage;
        Update();
    }
}


function Update()
{
    const url = document.location.href;
    const world = extract_world(url);
    
    //Delete previous content
    rows.value = [];
    //TODO(Vasilis): Setup loading animation (probably something like a buffering gif)
    loading.value = true;
    const reqobj = {'page': page.value, 'items': items.value, 'filter': filter.value };
    GET(`/api/${world}/${props.endpoint}`, reqobj, (resp) => {
        loading.value = false;
        build_url_params(reqobj);
        total.value = Math.ceil(resp['total']/items.value);
        const data = resp['data'];
        const offset = (page.value - 1) * items.value;
        for(let i = 0; i < data.length; i++)
        {
            const row = data[i];
            if(props.endpoint === 'tribes')
                UpdateTribes(row);
            else if(props.endpoint === 'players')
                UpdatePlayers(row);
            else if(props.endpoint === 'villages')
                UpdateVillages(row, offset+i+1);
        }
        
        function UpdateVillages(row, num)
        {
            const oldowner = row['prevpid'] === 0 ? row['old owner'] : `<a href="/${world}/player?id=${row['prevpid']}">${row['old owner']}</a>`;
            const oldtribe = row['prevtid'] === 0 ? row['old tribe'] : `<a href="/${world}/tribe?id=${row['prevtid']}">${row['old tribe']}</a>`;
            const newtribe = row['nexttid'] === 0 ? row['new tribe'] : `<a href="/${world}/tribe?id=${row['nexttid']}">${row['new tribe']}</a>`;
            rows.value.push({
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
            rows.value.push({
                'num': format(row['rankno']),
                'name': `<a href="/${world}/player?id=${row['id']}">${row['name']}</a>`,
                'tribe': tribestr,
                'points': format(row['points']),
                'villages': villstr,
                'offbash': format(row['offbash']), 'defbash': format(row['defbash']), 'totalbash': format(row['totalbash']),
                'vp': format(row['vp'])
            });        
        }

        function UpdateTribes(row)
        {
            const memberstr = row['members'] === 0 ? row['members'] : `<a href="/${world}/tribe?id=${row['id']}&view=members">${row['members']}</a>`;
            rows.value.push({
                'num': format(row['rankno']),
                'name': `<a href="/${world}/tribe?id=${row['id']}">${row['name']}</a>`, 'tag': row['tag'],
                'points': format(row['points']),
                'members': memberstr,
                'villages': format(row['villages']),
                'offbash': format(row['offbash']), 'defbash': format(row['defbash']), 'totalbash': format(row['totalbash']),
                'vp': format(row['vp'])
            });
        }
    });
}

onMounted(() => {
    const url = document.location.href;
    const params = get_params(url);
    if(params.hasOwnProperty('items'))
    {
        items.value = params['items'];
        document.getElementById('ipp'+items.value).checked = true;
    }
    if(params.hasOwnProperty('filter'))
        filter.value = params['filter'];
    if(params.hasOwnProperty('page'))
        page.value = params['page'];
    Update();
});
</script>

<template>

<div class="text-center mt-2">
    <h1>{{ props.title }}</h1>
</div>

<div>
        
    <div class="container mt-2">
        <div class="row">
                
            <div class="col">
                <item-selection :items="items" @itemsChange="onItemsChange"></item-selection>
            </div>
            <div class="col">
                <div class="d-flex flex-row-reverse">
                    <button type="submit" class="btn btn-primary ml-2" @click="Update"><i class="fa fa-search" aria-hidden="true"></i></button>
                    <input type="search" :placeholder="placeholder" class="searchbar" @keyup="onFilterChange" style="width: 250px">
                </div>
            </div>

        </div>
    </div>

    <div class="container mt-1">
        <table-component :headers="headers[props.endpoint]" :rows="rows" :loading="loading"></table-component>
        <pagination :page="page" :total="total" @pageChange="onPageChange"></pagination>
    </div>

</div>

</template>