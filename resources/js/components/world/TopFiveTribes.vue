<script setup>
import { ref, onMounted } from 'vue';
import TableComponent from '../tables/templates/TableComponent.vue';
import TopFiveRankGraph from '../graphs/TopFiveRankGraph.vue';

const world = ref('');
const link = ref('');
const rows = ref([]);
const headers = ref(['#', 'player', 'points', 'members', 'villages', 'domination']);
const graphdata = ref([]);

onMounted(() => {
    const url = document.location.href;
    world.value = extract_world(url);
    link.value = `/${world.value}/tribes`;

    GET(`/api/${world.value}`, {'view': 'tribes'}, (resp)=>{
        //Setup local variables
        const top5 = resp['top5'];
        const history = resp['history'];

        top5.forEach((obj) => {
            //Setup local variables
            const name = `<a href="/${world.value}/tribe?id=${obj['id']}">${obj['name']}</a>`;
            const members = `<a href="/${world.value}/tribe?id=${obj['id']}">${obj['members']}</a>`;
            rows.value.push({
                'num': obj['rankno'],
                'name': name,
                'points': format(obj['points']),
                'members': members,
                'villages': format(obj['villages'])
            });
        });

        history.forEach((obj)=>{ graphdata.value.push(obj); });
    });
});

</script>

<template>
    <div class="container" style="width: 50%;">
        
        <div class="text-center">
            <strong><a>Top 5 tribes </a></strong><a :href="link">show all</a>
        </div>

        <table-component :headers="headers" :rows="rows"></table-component>
        
        <div class="mt-5 mb-2">
            <top-five-rank-graph :data="graphdata"></top-five-rank-graph>
        </div>

    </div>
</template>