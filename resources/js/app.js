import { createApp } from 'vue';

//Components
const app=createApp({});
app.config.globalProperties.$total_villages = 0;
app.component('world-info', require('./components/world/WorldInfo.vue').default);
app.component('lastfive-conquers', require('./components/tables/LastFiveConquers.vue').default);
app.component('topfive', require('./components/world/TopFive.vue').default);
app.component('test-component', require('./components/TestComponent.vue').default);
app.component('leaderboard', require('./components/tables/Leaderboard.vue').default);
app.mount('#app');
