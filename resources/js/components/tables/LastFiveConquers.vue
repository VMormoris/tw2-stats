<script setup>
import { ref, onMounted } from 'vue';
import TableContent from './TableContent.vue';

const world = ref('');
const link = ref('');
const rows = ref([]);

onMounted(() => {
    const url = document.location.href;
    world.value = extract_world(url);
    link.value = `/${world.value}/villages`;

    GET(`/api/${world.value}`, {'view': 'villages'}, (resp) => {
        resp['top5'].forEach((row, index) => {
            //Setup local variables
            const villname = row['vid'] === 0 ? row['vname'] : '<a href="/' + world.value + '/village?id=' + row['vid'] + '">' + row['vname'] + '</a>';
            const oldowner = row['prevpid'] === 0 ? row['old owner'] : '<a href="/' + world.value + '/player?id=' + row['prevpid'] + '">' + row['old owner'] + '</a>';
            const newowner = '<a href="/' + world.value + '/player?id=' + row['nextpid'] + '">' + row['new owner'] + '</a>';
            const oldtribe = row['prevtid'] === 0 ? row['old tribe'] : '<a href="/' + world.value + '/tribe?id=' + row['prevtid'] + '">' + row['old tribe'] + '</a>';
            const newtribe = '<a href="/' + world.value + '/tribe?id=' + row['nexttid'] + '">' + row['new tribe'] + '</a>';

            rows.value.push({
                'num': index+1,
                'name': villname,
                'oldowner': oldowner, 'newowner': newowner,
                'oldtribe': oldtribe, 'newtribe': newtribe
            });
        });
        console.log(rows.value);
    });
});
</script>


<template>
    <div class="container" style="width: 70%;">
        <div class="text-center mt-2">
            <strong><a>Latest conquers </a></strong><a :href="link">show all</a>
        </div>
        <table class="table table-rounded mt-2">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">village</th>
                    <th scope="col">old owner</th>
                    <th scope="col">new owner</th>
                    <th scope="col">old tribe</th>
                    <th scope="col">new tribe</th>
                    <th scope="col">timestamp</th>
                </tr>
            </thead>
            <table-content :rows="rows"></table-content>
        </table>
    </div>
</template>