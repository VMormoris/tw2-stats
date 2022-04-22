<script setup>
const props = defineProps({
    items: Number
});
</script>

<template>
<div>
    <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp12" autocomplete="off" @click="$emit('itemsChange', 12)" :checked="props.items == 12 ? 'true' : null">
    <label class="btn btn-outline-primary ml-2" for="ipp12">12</label>
            
    <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp25" autocomplete="off" @click="$emit('itemsChange', 25)" :checked="props.items == 25 ? 'true' : null">
    <label class="btn btn-outline-primary ml-2" for="ipp25">25</label>
            
    <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp50" autocomplete="off" @click="$emit('itemsChange', 50)" :checked="props.items == 50 ? 'true' : null">
    <label class="btn btn-outline-primary ml-2" for="ipp50">50</label>
            
    <input type="radio" class="btn-check ipp" name="options-outlined" id="ipp100" autocomplete="off" @click="$emit('itemsChange', 100)" :checked="props.items == 100 ? 'true' : null">
    <label class="btn btn-outline-primary ml-2" for="ipp100">100</label>
</div>
</template>