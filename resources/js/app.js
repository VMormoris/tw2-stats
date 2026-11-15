import { createApp } from 'vue';
import WorldInfo from './components/world/WorldInfo.vue';
import LastFiveConquers from './components/tables/LastFiveConquers.vue';
import TopFive from './components/world/TopFive.vue';
import Leaderboard from './components/tables/Leaderboard.vue';
import Breadcrumb from './components/navigation/Breadcrumb.vue';
import Tribe from './components/Tribe.vue';
import Player from './components/Player.vue';
import Village from './components/Village.vue';

//Components
const app=createApp({});
app.component('world-info', WorldInfo);
app.component('lastfive-conquers', LastFiveConquers);
app.component('topfive', TopFive);
app.component('leaderboard', Leaderboard);
app.component('breadcrumb', Breadcrumb);
app.component('tribe', Tribe);
app.component('player', Player);
app.component('village', Village);
app.mount('#app');
