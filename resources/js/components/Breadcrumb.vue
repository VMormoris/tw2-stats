<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    'name': { 'type': String, 'default': '' }
});

const crumbs = ref([{ 'name': 'Home', 'url': '/' }]);
const lastcrumb = ref({'name': '', 'url': '#' });

onMounted(() => {
    const toFormalize = ['tribes', 'tribe', 'players', 'player', 'villages', 'village'];
    const path = document.location.pathname.substring(1);
    if(path === '/privacy')
    {
        lastcrumb.value = { 'name': 'privacy', 'url': '/privacy' };
        return;
    }

    //TODO(Vasilis): Add Worlds Crump pointing to World Selection when it's ready
    let url = '';
    const pages = path.split('/');
    for(let i = 0; i < pages.length-1; i++)
    {
        const page = pages[i];
        url += `/${page}`;
        crumbs.value.push({ 'name': toFormalize.includes(page) ? formalize(page) : page, 'url': url });
    }
    const lastpage = pages[pages.length - 1];
    url += `/${lastpage}`;
    lastcrumb.value = { 'name': toFormalize.includes(lastpage) ? formalize(lastpage) : lastpage, 'url': url };
    
    const params = get_params(document.location.search);
    if(params.hasOwnProperty('id'))
    {
        crumbs.value.push(lastcrumb.value);
        url += `?id=${params['id']}`;
        lastcrumb.value = { 'name': props.name, 'url': url };

        if(params.hasOwnProperty('view') && params.hasOwnProperty('show'))
            addview(params['view'], params['show']);
        else if(params.hasOwnProperty('view'))
            addview(params['view'])
        else
            addview('overview')
    }
    
    function addview(view, show='')
    {
        if(view != 'overview')
            url += `&view=${view}`;
        if(show === '')
            show = 'all';

        crumbs.value.push(lastcrumb.value);
        if(lastpage === 'player')
        {
            const map = { 'overview': 'Overview', 'history': 'History', 'conquers': 'Conquers', 'stats': 'Conquers Stats', 'villages': 'Villages', 'changes': 'Tribe Changes' };
            lastcrumb.value = { 'name': map[view], 'url': url };
            if(view === 'conquers')
            {
                url += show === 'all' ? '' : `&show=${show}`;
                crumbs.value.push(lastcrumb.value);
                lastcrumb.value = { 'name': formalize(show), 'url': url };
            }
        }
        else if(lastpage === 'tribe')
        {
            const map = { 'overview': 'Overview', 'history': 'History', 'conquers': 'Conquers', 'stats': 'Conquers Stats', 'members': 'Member', 'changes': 'Member Changes' };
            lastcrumb.value = { 'name': map[view], 'url': url };
            lastcrumb.value = { 'name': map[view], 'url': url };
            if(view === 'conquers')
            {
                url += show === 'all' ? '' : `&show=${show}`;
                crumbs.value.push(lastcrumb.value);
                lastcrumb.value = { 'name': formalize(show), 'url': url };
            }
        }
        else//(aka lastpage == village)
        {
            const map = { 'overview': 'Overview', 'history': 'History', 'conquers': 'Conquers', 'stats': 'Conquers Stats' };
            lastcrumb.value = { 'name': map[view], 'url': url };
        }
    }

});
</script>

<template>
<div class="mt-5">
    <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item" v-for="crumb in crumbs" :key="crumb.name">
            <a :href="crumb.url">{{crumb.name}}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{lastcrumb.name}}</li>
    </ol>
    </nav>
</div>
</template>
