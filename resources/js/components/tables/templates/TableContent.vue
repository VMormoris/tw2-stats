<script setup>
import Row from './RowComponent.vue';

const props = defineProps({
    rows: Array
});

</script>

<template>
    <tbody>
        <row v-for="obj in rows"
            :key="obj.num"
            :row="obj"
        ></row>
    </tbody>
</template>