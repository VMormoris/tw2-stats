const endpoint = 'tribe';
const data = {overview: null, history: null, conquers: {}, stats: null, members: null, villages: null, changes: null };
let tribe_id = 0;
let tribe_name = '';
let view = '';
let show = '';
let world = '';

let historyTable = null;
let conquersTable = null;
let membersTable = null;
let changesTable = null;

var onload = function()
{
    const url = document.location.href;
    world = extract_world(url);

    const params = get_params(url);
    tribe_id = parseInt(params.id);

    if(params.hasOwnProperty('view'))
    {
        view = params.view;
        parse_extra_params(params);
    }

    const shows = document.getElementsByClassName('conquers-type');
    for(let i = 0; i < shows.length; i++)
    {
        const radio = shows[i];
        radio.addEventListener('click', (event) => {
            const prop = event.target.id;
            show = prop == 'all' ? '' : prop;
            const reqobj = build_url_params();
            if(data.conquers.hasOwnProperty(prop))
                updateConquers(data.conquers);
            else
                GET('/api/' + world + '/tribe', reqobj, updateConquers);
        });
    }

    const historyctx = document.getElementById('historyTable');
    historyTable = new Table(historyctx, {
        'ipp': historyItems,
        'page': historyPage,
        'onItemsChange': historyChangeItems,
        'onPageChange': historyChangePage
    });

    const conquersctx = document.getElementById('conquersTable');
    conquersTable = new Table(conquersctx,{
        'ipp': conquers.items,
        'page': conquers.allPage,
        'onItemsChange': conquersChangeItems,
        'onPageChange': conquersChangePage,
        'onFilterChange': conquersChangeFilter,
    });

    const membersctx = document.getElementById('membersTable');
    membersTable = new Table(membersctx,{
        'ipp': membersItems,
        'page': membersPage,
        'onItemsChange': membersChangeItems,
        'onPageChange': membersChangePage,
        'onFilterChange': membersChangeFilter,
    });

    const changesctx = document.getElementById('changesTable');
    changesTable = new Table(changesctx, {
        'ipp': changesItems,
        'page': changesPage,
        'onItemsChange': changesChangeItems,
        'onPageChange': changesChangePage,
    });

    const reqobj = build_url_params();
    GET('/api/' + world + '/tribe', { id: tribe_id, 'view': 'name'}, (obj) =>
    {
        tribe_name = obj['name']; 
        document.getElementById('overview-title').innerText = tribe_name + "'s Overview";
        document.getElementById('history-title').innerText = tribe_name + "'s History";
        document.getElementById('conquers-title').innerText = tribe_name + "'s Conquers";
        document.getElementById('stats-title').innerText = tribe_name + "'s Conquer stats";
        document.getElementById('members-title').innerText = tribe_name + "'s Members";
        document.getElementById('changes-title').innerText = tribe_name + "'s Member changes";
    });
    
    if(reqobj['view'] == 'stats')
    {
        data.stats = {};
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvt_gains'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvt_losses'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvp_gains'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvp_losses'}, updateView);
    }
    else
        GET('/api/' + world + '/tribe', reqobj, updateView);
}

/**
 * Change the pages view
 * @param {string} newview The new view that will be changing to 
 * @param {string} newshow The show option only usefull during conquers
 */
function changeView(newview, newshow = '')
{
    hideAll();
    view = newview;
    show = newshow;
    
    const showprop = newshow == '' ? 'all' : newshow;
    document.getElementById(showprop).checked = true;
    const prop = view == '' ? 'overview' : view;
    
    if(view == 'history') { page = historyPage, items = historyItems; }
    else if(view == 'conquers') { page = conquers[showprop+'Page'], items = conquers.items, filter = conquers.filter; }
    else if(view == 'members') { page = membersPage, items = membersItems, filter = membersFilter;}
    else if(view == 'changes') { page = changesPage, items = changesItems; }

    const reqobj = build_url_params();
    if(prop == 'stats' && data[prop] == null)
    {
        data.stats = {};
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvt_gains'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvt_losses'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvp_gains'}, updateView);
        GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': 'stats', 'spec': 'tvp_losses'}, updateView);
    }
    else if(data[prop] == null)//Load view data from server
        GET('/api/' + world + '/tribe', reqobj, updateView);
    else if(prop == 'conquers' && data[prop][showprop] == null)
        GET('/api/' + world + '/tribe', reqobj, updateView);
    else//Data already loaded from server
        document.getElementById(prop).style.display = 'inherit';
}

function updateView(obj)
{
    if(view == '' || view == 'overview')
    {
        data.overview = obj;
        updateDetails(data.overview.details);
        updateGraphs(data.overview.graphs_data);
        document.getElementById('overview').style.display = 'inherit';
    }
    else if(view == 'history')
    {
        data.history = obj;
        updateHistory(data.history);
        document.getElementById('history').style.display = 'inherit';
    }
    else if(view == 'conquers')
    {
        for(const prop in obj)
            data.conquers[prop] = obj[prop];
        updateConquers(data.conquers);
        document.getElementById('conquers').style.display = 'inherit';
    }
    else if(view == 'stats')
    {
        for(const prop in obj)//Only one prop each time
        {
            data.stats[prop] = obj[prop];
            updateStats(obj);
        }
        document.getElementById('stats').style.display = 'inherit';
    }
    else if(view == 'members')
    {
        data.members = obj;
        updateMembers(data.members);
        document.getElementById('members').style.display = 'inherit';
    }
    else if(view == 'changes')
    {
        data.changes = obj;
        updateChanges(data.changes);
        document.getElementById('changes').style.display = 'inherit';
    }
}

/**
 * Populates the box containing tribes's information
 * @param {object} details Object containing tribes's information
 */
function updateDetails(details)
{
    //Easy just picking elements by id
    //  and reasigning their innerText or innerHTML values
    document.getElementById('rank').innerText = format(details.rankno);
    document.getElementById('name').innerText = details.name;
    document.getElementById('points').innerText = format(details.points);
    document.getElementById('dmembers').innerHTML = '<a href="javascript:void(0);" onclick="changeView(\'members\')">' + format(details.members) + '</a>';
    document.getElementById('avg-member-points').innerText = format(parseInt(details.points/details.members));
    document.getElementById('vills').innerHTML = format(details.villages);
    document.getElementById('avg-vill-points').innerText = format(parseInt(details.points/details.villages));
    document.getElementById('tchanges').innerHTML = '<a href="javascript:void(0);" onclick="changeView(\'changes\')">' + format(details.tchanges) + '</a>'; 
    document.getElementById('dconquers').innerHTML = '<a href="javascript:void(0);" onclick="changeView(\'conquers\')">' + format(details.conquers.all) + '</a>(<a href="javascript:void(0);" onclick="changeView(\'conquers\', \'gains\')">+' + format(details.conquers.gains) + ', <a href="javascript:void(0);" onclick="changeView(\'conquers\', \'losses\')">-' + format(details.conquers.losses) + '</a>) <a>Internally: </a><a href="javascript:void(0);" onclick="changeView(\'conquers\',\'internals\')">' + format(details.conquers.internals) + '</a>'; 
    document.getElementById('offbash').innerText = format(details.offbash);
    document.getElementById('defbash').innerText = format(details.defbash);
    document.getElementById('totalbash').innerText = format(details.totalbash);
    document.getElementById('vp').innerText = format(details.vp);
}

/**
 * Updates the graphs
 * @param {object} history Object containing tribe's history
 */
function updateGraphs(history)
{
    const data = { points: [], rank: [], off: [], def: [], total: [], timestamps: [] };
    for(let i = history.general.length - 1; i >= 0; i--)
    {
        const record = history.general[i];
        const timestamp = new Date(record.timestamp).getTime();
        data.timestamps.push(timestamp);
        data.points.push({ x: timestamp, y: record.points });
        data.rank.push({ x: timestamp, y: record.rankno });
        data.off.push({ x: timestamp, y: record.offbash });
        data.def.push({ x: timestamp, y: record.defbash });
        data.total.push({ x: timestamp, y: record.totalbash });
    }

    let lastNumOfVillages = history.villages[history.villages.length - 1].villages;
    const villdata = { same: [], losses: [], gains: [], timestamps: [] };
    for(let i = history.villages.length - 2; i >= 0; i--)
    {
        const record = history.villages[i];
        const timestamp = new Date(record.timestamp).getTime();
        villdata.timestamps.push(timestamp);
        if(lastNumOfVillages == record.villages)
            villdata.same.push({ x: timestamp, y: 0.05 });
        else if(lastNumOfVillages < record.villages)
            villdata.gains.push({ x: timestamp, y: record.villages - lastNumOfVillages });
        else
            villdata.losses.push({ x: timestamp, y: record.villages - lastNumOfVillages });
        lastNumOfVillages = record.villages;
    }

    {//Points Graph
        const options = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}};
        const pointsctx = document.getElementById('pointsgraph').getContext('2d');
        const pointsgraph = new Chart(pointsctx,
            {
                type: 'scatter',
                data:
                {
                    datasets:
                    [{
                        label: 'Points',
                        data: data.points,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: "rgb(225,99,132)",
                        fill: true,
                        showLine: true
                    }]
                },
                options: options
            }
        );
    }

    {//Rank Graph
        const datapoints = [];
        data.rank.forEach((obj) => { datapoints.push(obj.y); });
        const min = Math.max(Math.min(...datapoints) - 3, 1);
        const max = Math.max(Math.max(...datapoints) + 2, 5);
        const options = {scales:{x:{ticks:{callback: function(val, index){ return index % 3 === 0 ? createDateLabel(parseInt(val)) : '';}}}, y:{ reverse: true, suggestedMin: min, suggestedMax: max, ticks:{ stepSize: 1 } }},plugins:{tooltip:{callbacks:{label: function(ctx){const label = ctx.dataset.label || '';return label + ': ' + format(ctx.parsed.y) + ' at ' + asString(ctx.parsed.x);}}}}};
        const rankctx = document.getElementById('rankgraph').getContext('2d');
        const rankgraph = new Chart(rankctx,
            {
                type: 'scatter',
                data:
                {
                    datasets:
                    [{
                        label: 'Rank',
                        data: data.rank,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: "rgb(225,99,132)",
                        showLine: true
                    }]
                },
                options: options
            }
        );
    }

    {//Bash Graph
        const options ={scales:{x:{ticks:{callback: function(val, index) { return index % 3 === 0 ? createDateLabel(parseInt(val)) : ''; }}}},plugins:{tooltip:{callbacks:{title: function(ctx) { return ctx[0].dataset.label; },label: function(ctx) { return format(ctx.parsed.y) + ' bash points at ' + asString(ctx.parsed.x); }}}}};
        const bashctx = document.getElementById('bashgraph').getContext('2d');
        const bashgraph = new Chart(bashctx,
            {
                type: 'scatter',
                data:
                {
                    datasets:
                    [
                        {
                            label: 'Offensive',
                            data: data.off,
                            backgroundColor: 'rgba(255, 66, 32, 0.2)',
                            borderColor: "rgb(225,66,32)",
                            showLine: true
                        },
                        {
                            label: 'Defensive',
                            data: data.def,
                            backgroundColor: 'rgba(75, 192, 102, 0.2)',
                            borderColor: 'rgb(75, 192, 102)',
                            showLine: true
                        },
                        {
                            label: 'Total',
                            data: data.total,
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: "rgb(54, 162, 235)",
                            showLine: true
                        }
                    ]
                },
                options: options
            }
        );
    }

    {//Villages Graph (aka Conquers graph) 
        const min = -3;
        const max = 3;
        const options = {scales:{x:{ticks:{callback: function(val, index) { return createDateLabel(this.getLabelForValue(val)); }}}, y:{ suggestedMin: min, suggestedMax: max }},plugins:{tooltip:{callbacks:{title: function(ctx) { return ctx[0].dataset.label; },label: function(ctx){const timestamp = parseInt(ctx.label);return (ctx.parsed.y == 0.05 ? 0 : format(ctx.parsed.y)) + ' villages at ' + asString(timestamp);}}}}};
        const villsctx = document.getElementById('villagesgraph').getContext('2d');
        const villsgraphs = new Chart(villsctx,
            {
                type: 'bar',
                data:
                {
                    labels: villdata.timestamps,
                    datasets:
                    [
                        {
                            label: 'Same number of villages',
                            data: villdata.same,
                            backgroundColor: 'rgb(255, 205, 86)'
                        },
                        {
                            label: 'Gain villages',
                            data: villdata.gains,
                            backgroundColor: 'rgb(75, 192, 102)'
                        },
                        {
                            label: 'Lost villages',
                            data: villdata.losses,
                            backgroundColor: 'rgb(225, 66, 32)'
                        }
                    ]
                },
                options: options
            }
        );
    }
}

function updateStats(obj)
{
    //Constant pie color palette
    const colorpalette = ['rgb(255, 99, 132)', 'rgb(255, 159, 64)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(201, 203, 207)']
    
    //Get's "metadata"
    let prop = null;
    for(const property in obj)//Only one property
        prop = property;
    const subprop = prop.substring(4);

    const tdata = obj[prop];
    let total = 0;
    for(let i = 0; i < tdata.length; i++)
        total += parseInt(tdata[i][subprop]);
    //Set table's title
    document.getElementById(prop + '_title').innerText += ' (' + total + ' total)';

    //Data to filled during for loop
    const labels = [];//Labels for pie chart
    const datapoints = [];//Data for pie chart
    const colors = [];//Background colors for pie chart

    //Set table's content
    const tablectx = document.getElementById(prop);
    const table = tablectx.getElementsByClassName('table-contents')[0];
    table.innerHTML = '';

    for(let i = 0; i < tdata.length; i++)
    {
        const row = tdata[i];
        table.innerHTML += '<tr><th scope="col">' + row['num'] + '</th>\n' +
            '<td>' + row['name'] + '</td>\n' +
            '<td>' + row[subprop] + '</td>\n' +
            '<td>' + format(row[subprop] / total * 100) + '%</td>\n</tr>';
        
        //Fill data to use in pie later
        labels.push(row['name']);
        datapoints.push(row[subprop]);
        colors.push(colorpalette[i]);
    }

    //Setup pie
    const piectx = document.getElementById(prop+'_pie');
    const piegraph = new Chart(piectx, {
        'type': 'pie',
        'data':
        {
            'labels': labels,
            'datasets':
            [{
                'data': datapoints,
                'backgroundColor': colors
            }]
        },
        'options': { 'responsive': true, 'plugins': { 'legend': { 'position': 'top' } } },
        'actions':
        {
        }
    });
}

function parse_extra_params(params)
{
    //Boring stuff just seting up some values
    if(params.hasOwnProperty('page'))
        page = params.page;
    if(params.hasOwnProperty('filter'))
        filter = params.filter;
    if(params.hasOwnProperty('items'))
        items = params.items;
    
    if(view == 'history')
    {
        historyItems = items;
        historyFilter = filter;
        historyPage = page;
        document.getElementById('hpp'+historyItems).checked = true;
    }
    else if(view == 'conquers')
    {
        let prop = 'allPage';
        show = '';
        if(params.hasOwnProperty('show') && params.show != '')
        {
            conquers.show = params.show, show = params.show;
            conquers[params.show+'Page'] = page;
        }
        conquers.items = items;
        conquers.filter = filter;
        document.getElementById('cpp'+conquers.items).checked = true;
        document.getElementById(show == '' ? 'all' : show).checked = true;
    }
    else if(view == 'members')
    {
        membersItems = items;
        membersFilter = filter;
        membersPage = page;
        document.getElementById('mpp'+membersItems).checked = true;
    }
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
 
 /**
  * Creates a human readable string represantation for the given timestamp
  * @param {number} timestamp Unix like timestamp in miliseconds
  * @return {string} The date that was represented by the timestamp in human readable form
  */
 function asString(timestamp)
 {
     const strings = createDateLabel(timestamp);
     return strings[0] + ' ' + strings[1];
 }
 
 /**
  * Hides all subpage containers
  */
 function hideAll()
 {
     const containers = document.getElementsByClassName('subpage-container');
     for(let i = 0; i < containers.length; i++)
         containers[i].style.display = 'none';
 }

function build_url_params()
{
    const reqobj = { 'id': tribe_id };

    if(view != '' && view != 'overview')
        reqobj['view'] = view;
    if(view == 'conquers' && show != '' && show != 'all')
        reqobj['show'] = show;
    if(page!=1 && view != '' && view != 'overview')
        reqobj['page'] = page;
    if(items != 12 && view != '' && view != 'overview')
        reqobj['items'] = items;
    if(filter != '' && view != '' && view != 'overview')
        reqobj['filter'] = filter;

    const url = join(endpoint, reqobj);
    window.history.replaceState(null, null, url);
    return reqobj;
}

/* ------- Function for updating leaderboard like tables  ------- */
let page = 1, historyPage = 1, membersPage = 1, changesPage = 1;
let filter = '', membersFilter = '';
let items = 12, historyItems = 12, membersItems = 12, changesItems = 12;
const conquers =
{
    show: '',
    filter: '',
    items: 12,
    allPage: 1,
    lossesPage: 1,
    gainsPage: 1,
    internalsPage: 1
};

function historyChangeItems(newitems)
{
    if(historyItems != newitems)
    {
        items = newitems, historyItems = newitems;
        historyChangePage(1);
    }
}

function historyChangePage(newpage)
{
    page = newpage, historyPage = newpage;
    build_url_params();
    GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': view, 'page': historyPage, 'items': historyItems }, updateHistory);
}

function updateHistory(obj)
{
    const data ={
        'page': historyPage,
        'total': obj.total,
        'rows': []
    };

    const rows = obj.data;
    const offset = (historyPage - 1) * historyItems + 1;
    for(let i = 0; i < rows.length-1; i++)
    {
        const row = rows[i];
        const nextrow = rows[i+1];

        const offsets = {
            members: row.members - nextrow.members,
            villages: row.villages - nextrow.villages,
            points: row.points - nextrow.points,
            offbash: row.offbash - nextrow.offbash,
            defbash: row.defbash - nextrow.defbash,
            totalbash: row.totalbash - nextrow.totalbash,
            rankno: row.rankno - nextrow.rankno,
            vp: row.vp - nextrow.vp
        };

        let villstr = '<a>' + format(row.villages) + '</a><a class="';
        if(offsets.villages>0)
            villstr += 'text-success">(+' + format(offsets.villages) + ')</a>';
        else if(offsets.villages < 0)
            villstr += 'text-danger">(' + format(offsets.villages) + ')</a>';
        else
            villstr += 'text-warning">(=' + format(offsets.villages) + ')</a>';
        
        let memberstr = '<a>' + format(row.members) + '</a><a class="';
        if(offsets.members>0)
            memberstr += 'text-success">(+' + format(offsets.members) + ')</a>';
        else if(offsets.members < 0)
            memberstr += 'text-danger">(' + format(offsets.members) + ')</a>';
        else
            memberstr += 'text-warning">(=' + format(offsets.members) + ')</a>';
        
        let pointstr = '<a>' + format(row.points) + '</a><a class="';
        if(offsets.points>0)
            pointstr += 'text-success">(+' + format(offsets.points) + ')</a>';
        else if(offsets.points<0)
            pointstr += 'text-danger">(' + format(offsets.points) + ')</a>';
        else
            pointstr += 'text-warning">(=' + format(offsets.points) + ')</a>';
            
        let vpstr = '<a>' + format(row.vp) + '</a><a class="';
        if(offsets.vp>0)
            vpstr += 'text-success">(+' + format(offsets.vp) + ')</a>';
        else if(offsets.vp<0)
            vpstr += 'text-danger">(' + format(offsets.vp) + ')</a>';
        else
            vpstr += 'text-warning">(=' + format(offsets.vp) + ')</a>';

        let offbashstr = '<a>' + format(row.offbash) + '</a><a class="';
        if(offsets.offbash>0)
            offbashstr += 'text-success">(+' + format(offsets.offbash) + ')</a>';
        else if(offsets.offbash<0)
            offbashstr += 'text-danger">(' + format(offsets.offbash) + ')</a>';
        else
            offbashstr += 'text-warning">(=' + format(offsets.offbash) + ')</a>';

        let defbashstr = '<a>' + format(row.defbash) + '</a><a class="';
        if(offsets.defbash>0)
            defbashstr += 'text-success">(+' + format(offsets.defbash) + ')</a>';
        else if(offsets.defbash<0)
            defbashstr += 'text-danger">(' + format(offsets.defbash) + ')</a>';
        else
            defbashstr += 'text-warning">(=' + format(offsets.defbash) + ')</a>';

        let totalbashstr = '<a>' + format(row.totalbash) + '</a><a class="';
        if(offsets.totalbash>0)
            totalbashstr += 'text-success">(+' + format(offsets.totalbash) + ')</a>';
        else if(offsets.totalbash<0)
            totalbashstr += 'text-danger">(' + format(offsets.totalbash) + ')</a>';
        else
            totalbashstr += 'text-warning">(=' + format(offsets.totalbash) + ')</a>';

        data.rows.push({
            'id': offset + i,
            'members': memberstr,
            'villages': villstr,
            'points': pointstr,
            'offbash': offbashstr,
            'defbash': defbashstr,
            'totalbash': totalbashstr,
            'rankno': format(row.rankno),
            'vp': vpstr,
            'timestamp': row.timestamp
        });
    }
    historyTable.update(data);
}

function conquersChangeItems(newitems)
{
    if(conquers.items != newitems)
    {
        items = newitems, conquers.items = newitems;
        conquersChangePage(1);
    }
}

function conquersChangeFilter(newfilter)
{
    const filterstr = newfilter.toLowerCase();
    if(conquers.filter != filterstr)
    {
        filter = filterstr, conquers.filter = filterstr;
        conquersChangePage(1);
    }
}

function conquersChangePage(newpage)
{
    page = newpage;
    const prop = show == '' ? 'allPage' : show + 'Page'; 
    conquers[prop] = newpage;
    const reqobj = build_url_params();
    GET('/api/' + world + '/tribe', reqobj, updateConquers);
}

function updateConquers(obj)
{   
    for(const prop in obj)
        data.conquers[prop] = obj[prop];

    let total; 
    let rows;
    if(show == '' || show == 'all')
    {
        total = obj.all.total;
        rows = obj.all.data;
    }
    else if(show == 'losses')
    {
        total = obj.losses.total;
        rows = obj.losses.data;
    }
    else if(show == 'gains')
    {
        total = obj.gains.total;
        rows = obj.gains.data;
    }
    else if(show == 'internals')
    {
        total = obj.internals.total;
        rows = obj.internals.data;
    }

    const tdata = {
        'page': conquers[(show == '' ? 'all' : show) + 'Page'],
        'total': total,
        'rows': []
    };

    for(let i=0; i<rows.length; i++)
    {
        const row = rows[i];
        const self = '<strong>'+ tribe_name +'</strong>';
        const name = '<a href="/' + world + '/village?id=' + row.vid + '">' + row.name + ' (' + row.x + '|' + row.y +')</a>';
        const prevowner = row.prevpid == 0 ? row['old owner'] : '<a href="/' + world + '/player?id=' + row.prevpid + '">' + row['old owner'] + '</a>';
        const prevtribe = row.prevtid == 0 ? row['old tribe'] : (row.prevtid == tribe_id ? self : '<a href="/' + world + '/tribe?id=' + row.prevtid + '">' + row['old tribe'] + '</a>');
        const nexttribe = row.nexttid == tribe_id ? self : '<a href="/' + world + '/tribe?id=' + row.nexttid + '">' + row['new tribe'] + '</a>';
        tdata.rows.push({
            'id': row.vid,
            'name': name,
            'old owner': prevowner,
            'new owner': '<a href="/' + world + '/player?id=' + row.nextpid + '">' + row['new owner'] + '</a>',
            'old tribe': prevtribe,
            'new tribe': nexttribe,
            'points': format(row.points),
            'timestamp': row.timestamp
        });
    }
    conquersTable.update(tdata);
}

function membersChangeItems(newitems)
{
    if(membersItems != newitems)
    {
        items = newitems, membersItems = newitems;
        membersChangePage(1);
    }
}

function membersChangeFilter(newfilter)
{
    const filterstr = newfilter.toLowerCase();
    if(membersFilter != filterstr)
    {
        filter = filterstr, membersFilter = filterstr;
        membersChangePage(1);
    }
}

function membersChangePage(newpage)
{
    page = newpage, membersPage = newpage;
    build_url_params();
    GET('/api/' + world + '/tribe', {'id': tribe_id, 'view': view, 'page': membersPage, 'items': membersItems, 'filter': membersFilter }, updateMembers);
}

function updateMembers(obj)
{
    data.members = obj;
    const tdata = {
        'page': membersPage,
        'total': obj.total,
        'rows': [] 
    };

    const rows = obj.data;
    const offset = (membersPage - 1) * membersItems + 1;
    for(let i = 0; i < rows.length; i++)
    {
        const row = rows[i];
        tdata.rows.push({
            '#': format(offset + i),
            'name': '<a href="/' + world + '/player?id=' + row.id + '">' + row.name + '</a>',
            'points': format(row.points),
            'offbash': format(row.offbash), 'defbash': format(row.defbash), 'totalbash': format(row.totalbash),
            'villages': format(row.villages),
            'rankno': format(row.rankno),
            'vp': format(row.vp)
        });
    } 
    membersTable.update(tdata);
}

function changesChangeItems(newitems)
{
    if(changesItems != newitems)
    {
        items = newitems, changesItems = newitems;
        changesChangePage(1);
    }
}

function changesChangePage(newpage)
{
    page = newpage;
    const prop = show == '' ? 'allPage' : show + 'Page'; 
    changesPage = newpage;
    const reqobj = build_url_params();
    GET('/api/' + world + '/tribe', reqobj, updateChanges);
}

function updateChanges(obj)
{
    data.changes = obj;
    const tdata = {
        'page': changesPage,
        'total': obj.total,
        'rows': []
    };

    const rows = obj.data;
    const offset = (changesPage - 1) * changesItems + 1;
    for(let i = 0; i < rows.length; i++)
    {
        const row = rows[i];

        const playername = row.pid == 0 ? row['player'] : '<a href="/' + world + '/player?id=' + row.pid + '">' + row['player'] + '</a>';
        const action = row.nexttid == tribe_id ? '<b><a class="text-success">join</a></b>' : '<b><a class="text-danger">left</a></b>';
        
        tdata.rows.push({
            '#': offset + i,
            'player': playername,
            'action': action,
            'villages': format(row['villages']),
            'points': format(row['points']),
            'offbash': format(row.offbash),
            'defbash': format(row.defbash),
            'totalbash': format(row.totalbash),
            'rankno': format(row.rankno),
            'vp': format(row.vp),
            'timestamp': row.timestamp
        });
    }
    console.log(tdata);
    changesTable.update(tdata);
}
