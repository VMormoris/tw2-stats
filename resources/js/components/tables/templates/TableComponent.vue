<script setup>
import TableContent from './TableContent.vue';

const props = defineProps({
    headers: Array,
    rows: Array
});
</script>

<template>
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <th v-for="header in props.headers" :key="header" scope="col"> {{ header }} </th>
            </tr>
        </thead>
        <table-content :rows="props.rows"></table-content>
    </table>
</template>