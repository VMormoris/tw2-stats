<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    'info': Array
});
</script>

<template>
<table class="table mt-2">
    <thead class="thead-dark" v-for="obj, index in props.info" :key="index">
        <tr v-for="(value, prop) in obj" :key="prop"><!-- Not actual for loop only on property per object -->
            <th scope="col">{{ prop }}</th>
            <td v-html="value"></td>
        </tr>
    </thead>
</table>
</template>