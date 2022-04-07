import { createApp } from 'vue';

//Components
const app=createApp({});
app.component('test-component', require('./components/TestComponent.vue').default);
app.component('world-info', require('./components/WorldInfo.vue').default);
app.component('lastfive-conquers', require('./components/tables/LastFiveConquers.vue').default);
app.mount('#app');
