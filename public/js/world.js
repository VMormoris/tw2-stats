let world = '';
let total_villages = 1;

var onload = function()
{
    const url = document.location.href;
    world = extract_world(url);
    GET('/api/' + world, { 'view': 'players' }, (obj) => { updateRankAndGraph('player', obj); });
    //GET('/api/' + world, { 'view': 'tribes' }, (obj) => { updateRankAndGraph('tribe', obj); });

}

function updateRankAndGraph(endpoint, obj)
{
    const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)'];
    //Setup local variables
    const top5 = obj['top5'];
    const history = obj['history'];
    const indexes = {};
    const datasets = [];

    //Setup table
    const table_ctx = endpoint === 'tribe' ? document.getElementById('top5_tribes') : document.getElementById('top5_players');
    table_ctx.innerHTML = '';
    top5.forEach((el) => {
        
        //Setup local variables
        const name = '<a href="/' + world + '/' + endpoint +'?id='+ el['id']+'">' + el['name'] + '</a>';
        const villages = endpoint === 'tribe' ? format(el['villages']) : '<a href="/' + world + '/' + endpoint + '?id='+ el['id']+'&view=villages">' + format(el['villages']) + '</a>';
        
        //Add table row
        table_ctx.innerHTML +=
        '<tr>\n' +
            '<th scope="col">' + el['rankno'] + '</th>\n' +
            '<td>' + name + '</td>\n' +
            '<td>' + format(el['points']) + '</td>\n' +
            (endpoint === 'player' ? '' : '<td><a href="/'+ world +'/tribe?id='+ el['id'] +'&view=members">'+ el['members'] +'</a></td>\n') +
            '<td>' + villages + '</td>\n' +
            '<td>' + format(el['villages']/total_villages*100) + '%</td>\n' +
        '</tr>\n';
        
        //Setup names for graph to avoid extra loop
        indexes[el['id']] = datasets.length;
        datasets.push({
            'label':  el['name'],
            'data': [],
            'backgroundColor': colorpalette[el['rankno'] - 1],
            'borderColor': colorpalette[el['rankno'] - 1],
            'showLine': true
        });
    });

    //Setup graph
    history.forEach((el) => {
        const data = datasets[indexes[el['id']]].data;
        const timestamp = new Date(el['timestamp']).getTime();
        data.push({ 'x': timestamp, 'y': el['rankno']});
    });
    console.log(datasets);
    const options = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, ticks:{ stepSize: 1 } }},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}};
    const graph_ctx = (endpoint === 'tribe' ? document.getElementById('tribes_graph') : document.getElementById('players_graph')).getContext('2d');
    const graph = new Chart(graph_ctx, {
        'type': 'scatter',
        'data': { 'datasets': datasets },
        'options': options
    });

}

