import { createApp } from 'vue';

//Components
const app=createApp({});
app.component('world-info', require('./components/world/WorldInfo.vue').default);
app.component('lastfive-conquers', require('./components/tables/LastFiveConquers.vue').default);
app.component('topfive-tribes', require('./components/world/TopFiveTribes.vue').default);
app.mount('#app');
