<script setup>
import { ref, onMounted } from 'vue';
import InfoTable from '../tables/templates/InfoTable.vue';

const info = ref([]);

onMounted(()=>{
  const url = document.location.href;
  const world = extract_world(url);

  GET(`/api/${world}`, {}, (resp) => {
    //Setup local variables
    const obj = resp['world'];
    const wcond = obj['win_condition'] === 'Domination' ? '' + obj['win_ammount'] + '% Domination' : '' + format(obj['win_ammount']) + ' VP';
    const start = new Date(Date.parse(obj['start'])).toLocaleDateString('en-uk');
    const end = obj['end'] === null ? 'TBA' : new Date(Date.parse(obj['start'])).toLocaleDateString('en-uk');

    //Update webpage
    info.value.push({ 'Players': format(obj['players']) });
    info.value.push({ 'Tribes': format(obj['tribes']) });
    info.value.push({ 'Villages': format(obj['villages']) });
    info.value.push({ 'Win Condition': wcond });
    info.value.push({ 'Starting Date': start });
    info.value.push({ 'Ending Date': end });
        
    //Store variable for later use
    globals.wcond = obj['win_condition'];
  });

});

</script>

<template>
  <div class="container" style="width: 25%">
    <div class="text-center mt-2">
      <strong><a style="font-size: 20px">General Information</a></strong>
    </div>
    <info-table :info="info"></info-table>
  </div>
</template>
