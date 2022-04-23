<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    page: Number,
    total: Number
});

const continues = { 'txt': '...', 'flag': false, 'active': false };

const leftArrow = ref(false);
const rightArrow = ref(false);

const pages = ref([ continues ]);

onMounted(() => { UpdateComponent(); });
watch(props, (newprops) => { UpdateComponent(); });

function UpdateComponent()
{
    if(props.total == 0 || props.page == 0)
        return;
    
    leftArrow.value = props.page != 1;
    rightArrow.value = props.page != props.total;

    pages.value = [{ 'txt': 1, 'flag': props.page != 1, 'active': props.page == 1 }];
    if(props.page - 2 > 2)
        pages.value.push(continues);
    for(let i = props.page - 2; i <= props.page + 2; i++)
    {
        if( i <= 1 || i >= props.total)
            continue;
        pages.value.push({ 'txt': i, 'flag': props.page != i, 'active': i == props.page });
    }
    if(props.page + 2 < props.total - 1)
        pages.value.push(continues)
    if(props.total > 1)
        pages.value.push({ 'txt': props.total, 'flag': props.page != props.total, 'active': props.total == props.page });
}


</script>

<template>
    <nav aria-label="...">
        <ul class="pagination justify-content-end pages">
            <li :class="leftArrow ? 'page-item' : 'page-item disabled'">
                <a class="page-link" :disabled="leftArrow" @click="$emit('pageChange', props.page-1)">
                    <span>{{'\u00AB'}}</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <li v-for="page, index in pages" :key="index"
                :class="page.active ? 'page-item active' : (page.flag ? 'page-item' : 'page-item disabled')">
                <a class="page-link" @click="$emit('pageChange', page.txt)">
                    {{ page.txt }}
                </a>
            </li>
            <li :class="rightArrow ? 'page-item' : 'page-item disabled'">
                <a class="page-link" :disabled="rightArrow" @click="$emit('pageChange', props.page+1)">
                    <span>{{'\u00BB'}}</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
        </ul>
    </nav>
</template>