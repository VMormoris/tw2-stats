<script setup>
import { ref, onMounted } from 'vue';
import Pagination from './tables/templates/Pagination.vue';
import ItemSelection from './tables/templates/ItemSelection.vue';
import TableComponent from './tables/templates/TableComponent.vue';

const items = ref(12);
const page = ref(1);
const total = ref(0);

const headers = (['#', 'Name', 'Tag', 'Members', 'Points', 'Offbash', 'Defbash', 'Total bash', 'Victory Points']);
const rows = ref([]);

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

function onPageChange(newpage)
{
    if(newpage != page.value)
    {
        page.value = newpage;
        Update();
    }
}

onMounted(() => { Update(); });

function Update()
{
    const url = document.location.href;
    const world = extract_world(url);
    rows.value = [];
    GET(`/api/${world}/tribes`, {'page': page.value, 'items': items.value }, (resp) => {
        total.value = Math.ceil(resp['total']/items.value);
        const data = resp['data'];
        
        for(let i = 0; i < data.length; i++)
        {
            const row = data[i]; 
            const memberstr = row['members'] === 0 ? row['members'] : `<a href=/${world}/tribe?id=${row['id']}&view="members">${row['members']}</a>`;
            
            rows.value.push({
                'num': format(row['rankno']),
                'name': `<a href="/${world}/tribe?id=${row['id']}">${row['name']}</a>`,
                'tag': row['tag'],
                'members': memberstr,
                'points': format(row['points']),
                'offbash': format(row['offbash']),
                'defbash': format(row['defbash']),
                'totalbash': format(row['totalbash']),
                'vp': format(row['vp'])
            });
        }
        
    });
}

</script>

<template>
<div class="container">
    
    <div class="text-center mt-5">
        <h1>Tribe's Ranking</h1>
    </div>

    <div>
        
        <div class="container mt-5">
            <div class="row">
                <div class="col">
                    <item-selection :items="items" @itemsChange="onItemsChange"></item-selection>
                </div>

                <div class="col">
                    <div class="d-flex flex-row-reverse">
                        <button type="submit" class="btn btn-primary ml-2" onclick="updateFilter(event)"><i class="fa fa-search" aria-hidden="true"></i></button>
                        <input type="search" placeholder="Search Tribe" class="searchbar">
                    </div>
                </div>
            </div> 
        </div>


        <div class="container mt-1">
            <table-component :headers="headers" :rows="rows"></table-component>
            <pagination :page="page" :total="total" @pageChange="onPageChange"></pagination>
        </div>

    </div>

</div>
</template>