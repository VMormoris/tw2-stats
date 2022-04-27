<script setup>
import { ref, onMounted } from 'vue';

const data = ref({
  'players': 0,
  'tribes': 0,
  'villages': 0,
  'wcond': '',
  'start': '',
  'end': 'TBA'
});


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
    data.value['players'] = format(obj['players']);
    data.value['tribes'] = format(obj['tribes']);
    data.value['villages'] = format(obj['villages']);
    data.value['wcond'] = wcond;
    data.value['start'] = start;
    data.value['end'] = end;
        
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
    <table class="table table-bordered mt-2">
      <thead class="thead-dark">
        <tr>
          <th>Players:</th>
          <td id="player_num"> {{ data.players }}</td>
        </tr>
      </thead>
      <thead class="thead-dark">
        <tr>
          <th>Tribes:</th>
          <td id="tribe_num">{{ data.tribes }}</td>
        </tr>
      </thead>
      <thead class="thead-dark">
        <tr>
          <th>Villages:</th>
          <td id="village_num">{{ data.villages }}</td>
        </tr>
      </thead>
      <thead class="thead-dark">
        <tr>
          <th>Win Condition:</th>
          <td id="win_cond">{{ data.wcond }}</td>
        </tr>
      </thead>
      <thead class="thead-dark">
        <tr>
          <th>Starting date:</th>
          <td id="start">{{ data.start }}</td>
        </tr>
      </thead>
      <thead class="thead-dark">
        <tr>
          <th>Ending date:</th>
          <td id="end">{{ data.end }}</td>
        </tr>
      </thead>
    </table>
  </div>
</template>
