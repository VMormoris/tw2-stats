<script setup>
import { ref, onMounted } from 'vue';
import Breadcrumb from './navigation/Breadcrumb.vue';
import Tabs from './navigation/Tabs.vue';
import History from './tables/History.vue';
import Villages from './tables/Villages.vue';
import TribeChanges from './tables/TribeChanges.vue';

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

    <tabs :endpoint="'player'" :active="view" :id="id"></tabs>

    <div class="tab">
        <history v-if="view==='history'"
            :endpoint="'player'"
            :name="props.name"
            :id="id">            
        </history>
    </div>

    <div class="tab">
        <villages v-if="view==='villages'"
            :name="props.name"
            :id="id">
        </villages>
    </div>
    
    <div class="tab">
        <tribe-changes v-if="view==='changes'"
            :name="props.name"
            :id="id">
        </tribe-changes>
    </div>

</div>
</template>