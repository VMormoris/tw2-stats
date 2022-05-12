<script setup>
const props = defineProps({
    'columns': Number
});
</script>

<template>
<tr>
    <td class="text-center" :colspan="props.columns">
        <a>
            <i class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>
            <span class="sr-only">Loading...</span>
        </a>
    </td>
</tr>
</template>