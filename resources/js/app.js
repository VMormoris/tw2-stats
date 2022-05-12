import { createApp } from 'vue';

//Components
const app=createApp({});
app.component('world-info', require('./components/world/WorldInfo.vue').default);
app.component('lastfive-conquers', require('./components/tables/LastFiveConquers.vue').default);
app.component('topfive', require('./components/world/TopFive.vue').default);
app.component('leaderboard', require('./components/tables/Leaderboard.vue').default);
app.component('breadcrumb', require('./components/Breadcrumb.vue').default);
app.component('tribe', require('./components/Tribe.vue').default);
app.component('player', require('./components/Player.vue').default);
app.component('village', require('./components/Village.vue').default);
app.mount('#app');
