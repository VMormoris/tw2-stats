<script setup>
import { ref, onMounted } from 'vue';
import Breadcrumb from './navigation/Breadcrumb.vue';
import Tabs from './navigation/Tabs.vue';
import History from './tables/History.vue';
import Overview from './inspection/Overview.vue';
import Conquers from './tables/Conquers.vue';

const props = defineProps({
    'name': String
});

const id = ref(0);
const view = ref('');

onMounted(() => {
    const paramstr = document.location.search;
    const params = get_params(paramstr);
    id.value = params['id'];
    view.value = params.hasOwnProperty('view') ? params['view'] : 'overview';
});
</script>

<template>
<div class="container">
    
    <breadcrumb :name="props.name"></breadcrumb>
    
    <tabs :endpoint="'village'" :active="view" :id="id"></tabs>

    <div class="tab">
        <overview v-if="view==='overview'"
            :endpoint="'village'"
            :name="props.name"
            :id="id">
        </overview>
    </div>
    
    <div class="tab">
        <history v-if="view==='history'"
            :endpoint="'village'"
            :name="props.name"
            :id="id">
        </history>
    </div>

    <div class="tab">
        <conquers v-if="view==='conquers'"
            :endpoint="'village'"
            :name="props.name"
            :id="id">
        </conquers>
    </div>
</div>
</template>