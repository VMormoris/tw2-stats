<!--
Component for creating a row using a JavaScript object.
    We iterate through each property of the object creating a column for each on of them

    * Use property 'num' as the first property to indicate the number of row (we will be using th)
-->
<script setup>

import { ref, onMounted } from 'vue';

const props = defineProps({
    row: Object//Object to display it's properties as rows
});

const hflag = ref('num' in props.row);//Flag for whether header exists
const header = ref(hflag ? props.row['num'] : '');
const data = ref({});

onMounted(() => {
    for(const prop in props.row)
    {
        if(prop === 'num') continue;
        data.value[prop] = props.row[prop];
    }
});

</script>

<template>
    <tr>
        <th v-if="hflag" scope="col">{{ header }}</th><!-- This th will make row number bold -->
        <td v-for="value in data" :key="value" v-html="value"></td>
    </tr>
</template>
