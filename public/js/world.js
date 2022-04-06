let world = '';
let total_villages = 1;

var onload = function()
{
    const url = document.location.href;
    world = extract_world(url);

    GET('/api/' + world, {}, (obj) => {
        //Setup local variables
        const data = obj['world'];
        const win_cond = data['win_condition'] === 'Domination' ? '' + data['win_ammount'] + '% Domination' : '' + format(data['win_ammount']) + ' VP';
        const start = new Date(Date.parse(data['start'])).toLocaleDateString('en-uk');
        const end = data['end'] === null ? 'TBA' : new Date(Date.parse(data['start'])).toLocaleDateString('en-uk');

        //Update webpage
        document.getElementById('player_num').innerText = format(data['players']);
        document.getElementById('tribe_num').innerText = format(data['tribes']);
        document.getElementById('village_num').innerText = format(data['villages']);
        document.getElementById('win_cond').innerText = win_cond;
        document.getElementById('start').innerText = start;
        document.getElementById('end').innerText = end;

        //Store variable for later use
        total_villages = data['villages'];
    });

    GET('/api/' + world, { 'view': 'villages'}, updateConquers);
    GET('/api/' + world, { 'view': 'players' }, (obj) => { updateRankAndGraph('player', obj); });
    GET('/api/' + world, { 'view': 'tribes' }, (obj) => { updateRankAndGraph('tribe', obj); });

}

function updateConquers(obj)
{
    const data = obj['top5'];
    const conquers = document.getElementById('top5_conquers');
    conquers.innerHTML = '';
    let i = 1;
    data.forEach((el) => {
        const villname = el['vid'] === 0 ? el['vname'] : '<a href="/' + world + '/village?id=' + el['vid'] + '">' + el['vname'] + '</a>';
        const oldowner = el['prevpid'] === 0 ? el['old owner'] : '<a href="/' + world + '/player?id=' + el['prevpid'] + '">' + el['old owner'] + '</a>';
        const newowner = '<a href="/' + world + '/player?id=' + el['nextpid'] + '">' + el['new owner'] + '</a>';
        const oldtribe = el['prevtid'] === 0 ? el['old tribe'] : '<a href="/' + world + '/tribe?id=' + el['prevtid'] + '">' + el['old tribe'] + '</a>';
        const newtribe = '<a href="/' + world + '/tribe?id=' + el['nexttid'] + '">' + el['new tribe'] + '</a>';
        conquers.innerHTML +=
        '<tr>\n' +
            '<th scope="col">' + (i++) + '</th>\n' +
            '<td>' + villname + '</td>\n' +
            '<td>' + oldowner + '</td>\n' +
            '<td>' + newowner + '</td>\n' +
            '<td>' + oldtribe + '</td>\n' +
            '<td>' + newtribe + '</td>\n' +
            '<td>' + el['timestamp'] + '</td>\n' +
        '</tr>\n';
    });
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

/**
 * Creates a date label base on the given timestamp 
 * @param {number} timestamp Unix like timestamp in miliseconds
 * @returns {string[]} Two string in an array containg the date and time respectively
 */
function createDateLabel(timestamp)
{
    const date = new Date(timestamp);
    let datestr = '';
    if(date.getDate() < 10)
        datestr += '0';
    datestr += date.getDate() + '/';
    if(date.getMonth() + 1 < 10)
        datestr += '0';
    datestr += (date.getMonth() + 1) + '/' + date.getFullYear();
     
    let timestr = '';
    if(date.getHours()<10)
        timestr += '0';
    timestr += date.getHours() + ':';
    if(date.getMinutes()<10)
        timestr += '0';
    timestr += date.getMinutes() + ':';
    if(date.getSeconds()<10)
        timestr += '0';
    timestr += date.getSeconds();
    return [datestr, timestr];
}