<script setup>
import { ref, onMounted } from 'vue';
import ItemSelection from './ItemSelection.vue';
import Pagination from './Pagination.vue';
import TableComponent from './TableComponent.vue';

const props = defineProps({
    'headers': Array,
    'hasSearchbar': { 'type': Boolean, 'default': true },
    'hasShow': { 'type': Boolean, 'default': false },
    'hasInternals': { 'type': Boolean, 'default': false },
    'hasDetails': { 'type': Boolean, 'default': false },
    'isHistory': { 'type': Boolean, 'default': false },
    'searchBarHint': { 'type': String, 'default': '' }
});

const emit = defineEmits(['update']);

const data = ref({
    'page': 1,
    'items': 12,
    'filter': '',
    'show': 'all',
    'rows': [],
    'total': 0,
    'loading': true
});

let clockHandle = null;

function TriggerUpdate()
{
    data.value['rows'] = [];
    data.value['loading'] = true;  
    emit('update', data.value);
}

function onItemsChange(newitems)
{
    if(newitems != data.value['items'])
    {
        const offset = data.value['items'] * (data.value['page'] - 1) + 1;
        data.value['items'] = newitems;
        data.value['page'] = Math.ceil(offset/newitems);
        TriggerUpdate();
    }
}

function onShowChange(newshow)
{
    if(newshow != data.value['show'])
    {
        data.value['show'] = newshow;
        data.value['page'] = 1;
        TriggerUpdate();
    }
}

function onFilterChange(event)
{
    const str = event.target.value.toLowerCase();
    if(str != data.value['filter'])
    {
        data.value['filter'] = str;
        data.value['page'] = 1;
        clearTimeout(clockHandle);
        clockHandle = setTimeout(TriggerUpdate, 500);
    }
}

function onPageChange(newpage)
{
    if(newpage != data.value['page'])
    {
        data.value['page'] = newpage;
        TriggerUpdate();
    }
}

onMounted(() => {
    //Check arguments and set the apropriate value if needed it
    const paramstr = document.location.search;
    const params = get_params(paramstr);
    if(params.hasOwnProperty('page'))
        data.value['page'] = params['page'];
    if(params.hasOwnProperty('items'))
        data.value['items'] = params['items'];
    if(params.hasOwnProperty('filter'))
        data.value['filter'] = params['filter'];
    if(params.hasOwnProperty('show'))
        data.value['show'] = params['show'];

    TriggerUpdate();
});

</script>
<template>
<div>
    <div class="container mt-2">
        <div class="row">
            <div class="col">
                <item-selection :items="data.items" @itemsChange="onItemsChange"></item-selection>
            </div>
            <div class="col" v-if="props.hasShow">
                <div class="text-center">
                    <input type="radio" class="btn-check conquers-type" name="conquers-type" id="all" autocomplete="off" @click="onShowChange('all')" :checked="data.show==='all' ? 'true' : null">
                    <label class="btn btn-outline-primary cnqrs-type" for="all">all</label>

                    <input type="radio" class="btn-check conquers-type" name="conquers-type" id="gains" autocomplete="off" @click="onShowChange('gains')" :checked="data.show==='gains' ? 'true' : null">
                    <label class="btn btn-outline-success cnqrs-type ml-2" for="gains">gains</label>

                    <input type="radio" class="btn-check conquers-type" name="conquers-type" id="losses" autocomplete="off" @click="onShowChange('losses')" :checked="data.show==='losses' ? 'true' : null">
                    <label class="btn btn-outline-danger cnqrs-type ml-2" for="losses">losses</label>

                    <input v-if="props.hasInternals" type="radio" class="btn-check conquers-type" name="conquers-type" id="internals" autocomplete="off" @click="onShowChange('internals')" :checked="data.show==='internals' ? 'true' : null">
                    <label v-if="props.hasInternals" class="btn btn-outline-warning big-cnqrs-type ml-2" for="internals">internals</label>
                </div>
            </div>
            <div class="col" v-if="props.hasSearchbar">
                <div class="d-flex flex-row-reverse">
                    <button type="submit" class="btn btn-primary ml-2" @click="TriggerUpdate"><i class="fa fa-search" aria-hidden="true"></i></button>
                    <input type="search" :placeholder="props.searchBarHint" class="searchbar" @keyup="onFilterChange" style="width: 250px">
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-1">
        <table-component :headers="headers" :rows="data.rows" :loading="data.loading"></table-component>
        <div v-if="!data.loading" class="row mb-2">
            <div v-if="props.hasDetails" class="col">
                <div class="disclaimer">
                    <a class="text-warning no-u"><span></span></a> Same as yesterday's max <br>
                    <a class="text-success no-u">&uarr;</a> Greater than yesterday's max <br>
                    <a class="text-danger no-u">&darr;</a> Less than yesterday's max <br>
                </div>
            </div>
            <div v-else-if="props.isHistory" class="col">* The number inside the parenthesis represents the difference between two records</div>
            <div class="col">
                <pagination :page="data.page" :total="data.total" @pageChange="onPageChange"></pagination>
            </div>
        </div>
    </div>
</div>
</template>