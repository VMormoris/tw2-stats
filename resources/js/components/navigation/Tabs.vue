<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    'endpoint': String,
    'active': String,
    'id': Number,
    'dropDown': String
});

const url = ref(`${document.location.pathname}?id=${props.id}`);
const tabs = ref({
    'tribe': ['overview', 'history', 'conquers', 'stats', 'members', 'changes'],
    'player': ['overview', 'history', 'conquers', 'stats', 'villages', 'changes'],
    'village': ['overview', 'history', 'conquers']
});
const map = ref({
    '': 'Overview', 'overview': 'Overview',
    'history': 'History',
    'conquers': 'Conquers', 'stats': 'Conquer Stats',
    'members': 'Members', 'villages': 'Villages'
});
const active = ref('');

onMounted(() => { map.value['changes'] = props.endpoint === 'tribe' ? 'Member Changes' : 'Tribe Changes'; });
watch(props, (newprops)=> { url.value = `${document.location.pathname}?id=${newprops.id}`; });

</script>

<template>
<ul class="nav nav-tabs mt-4">
    <li v-for="tab in tabs[props.endpoint]" :key="tab" :class="tab==='conquers' ? 'nav-item dropdown' : 'nav-item'">
        <a v-if="tab==='conquers' && props.active === tab && props.endpoint==='village'" class="nav-link active" aria-current="page" href="#">Conquers</a>
        <a v-else-if="tab==='conquers' && props.endpoint === 'village'" class="nav-link" :href="`${url}&view=conquers`">Conquers</a>
        <a v-else-if="tab==='conquers' && props.active === tab"
            class="nav-link dropdown-toggle active"
            data-bs-toggle="dropdown"
            :href="props.dropDown === 'all' ? '#' : `${url}&view=conquers`"
            role="button"
            aria-haspopup="true"
            aria-expanded="false">
            Conquers
        </a>
        <a v-else-if="tab==='conquers'" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" :href="`${url}&view=conquers`" role="button" aria-haspopup="true" aria-expanded="false">Conquers</a>
        <a v-else-if="props.active===tab" class="nav-link active" aria-current="page" href="#">{{ map[tab] }}</a>
        <a v-else class="nav-link" :href="tab==='' || tab==='overview' ? `${url}` : `${url}&view=${tab}`">{{ map[tab] }}</a>
        <div v-if="tab==='conquers' && props.endpoint != 'village'" class="dropdown-menu">
            <a class="dropdown-item" :href="props.dropDown === 'all' ? '#' : `${url}&view=conquers`">All</a>
            <a class="dropdown-item" :href="props.dropDown === 'gains' ? '#' : `${url}&view=conquers&show=gains`">Gains</a>
            <a class="dropdown-item" :href="props.dropDown === 'losses' ? '#' : `${url}&view=conquers&show=losses`">Losses</a>
            <a v-if="props.endpoint==='tribe'" class="dropdown-item" :href="props.dropDown === 'internals' ? '#' : `${url}&view=conquers&show=internals`">Internals</a>
        </div>
    </li>
</ul>
</template>