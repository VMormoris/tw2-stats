<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    'endpoint': String
});
</script>

<template>

<div>
    <h1>{{ props.title }}</h1>
</div>

<div>
    <div class="container mt-5">
        <div class="row">
            <div class="col">
            </div>
        </div>
    </div>
</div>

</template>