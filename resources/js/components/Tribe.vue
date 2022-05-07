<script setup>
import {ref, onMounted } from 'vue';
import Breadcrumb from './navigation/Breadcrumb.vue';
import Tabs from './navigation/Tabs.vue';
import Members from './tables/Members.vue';
import History from './tables/History.vue';
import MemberChanges from './tables/MemberChanges.vue';

const props = defineProps({
    'name': String
});

const id = ref(0);
const view = ref('');
const show = ref('all');

onMounted(() => {
    const paramstr = document.location.search;
    const params = get_params(paramstr);
    id.value = params['id'];
    if(params.hasOwnProperty('view'))
        view.value = params['view'];
    if(params.hasOwnProperty('show'))
        show.value = params['show'];
});
</script>

<template>
<div class="container">
    
    <breadcrumb :name="props.name"></breadcrumb>
    
    <tabs :endpoint="'tribe'" :active="view" :id="id"></tabs>

    <div class="tab">
        <history v-if="view==='history'"
            :endpoint="'tribe'"
            :name="props.name"
            :id="id">
        </history>
    </div>

    <div class="tab">
        <members v-if="view==='members'"
            :name="props.name"
            :id="id">
        </members>
    </div>

    <div class="tab">
        <member-changes v-if="view==='changes'"
            :name="props.name"
            :id="id">
        </member-changes>
    </div>
</div>
</template>