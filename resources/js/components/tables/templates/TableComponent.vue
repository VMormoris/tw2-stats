<script setup>
import { ref } from 'vue';
import TableContent from './TableContent.vue';
import LoadingRow from './LoadingRow.vue';

const props = defineProps({
    'headers': Array,
    'rows': Array,
    'loading': Boolean
});

const columns = ref(props.headers.length);
</script>

<template>
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th v-for="header in props.headers" :key="header" scope="col"> {{ header }} </th>
            </tr>
        </thead>
        <loading-row v-if="props.loading" :columns="columns"></loading-row>
        <table-content :rows="props.rows" v-if="!props.loading"></table-content>
    </table>
</template>