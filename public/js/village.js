const endpoint = 'village';
const data = { overview: null, history: null, conquers: null };

let village_id = 0;
let village_name = '';
let view = '';
let world = '';

let historyTable = null;
let conquersTable = null;

var onload = function()
{
    const url = document.location.href;
    world = extract_world(url);

    const params = get_params(url);
    village_id = parseInt(params.id);

    if(params.hasOwnProperty('view'))
    {
        view = params.view;
        parse_extra_params(params);
    }

    
    const historyctx = document.getElementById('historyTable');
    historyTable = new Table(historyctx, {
        'ipp': historyItems,
        'page': historyPage,
        'onItemsChange': historyChangeItems,
        'onPageChange': historyChangePage
    });

    const conquersctx = document.getElementById('conquersTable');
    conquersTable = new Table(conquersctx, {
        'ipp': conquersItems,
        'page': conquersPage,
        'onItemsChange': conquersChangeItems,
        'onPageChange': conquersChangePage,
        'onFilterChange': conquersChangeFilter,
    });

    const reqobj = build_url_params();
    GET('/api/' + world + '/village', { id: village_id, 'view': 'name'}, (obj) =>{
        village_name = obj['name'];
        document.getElementById('overview-title').innerText = village_name + '\'s Overview';
        document.getElementById('history-title').innerText = village_name + '\'s History';
        document.getElementById('conquers-title').innerText = village_name + '\'s Conquers';
    });

    GET('/api/' + world + '/village', reqobj, updateView);
};

/**
 * Change the pages view
 * @param {string} newview The new view that will be changing to 
 * @param {string} newshow The show option only usefull during conquers
 */
function changeView(newview, newshow = '')
{
    hideAll();
    view = newview;

    const prop = view == '' ? 'overview' : view;
     
    if(view == 'history') { page = historyPage, items = historyItems; }
    else if(view == 'conquers') { page = conquersPage, items = conquersItems; }
    else if(view == 'villages') { page = villPage, items = villItems; }
 
    const reqobj = build_url_params();
    if(data[prop] == null)//Load view data from server
        GET('/api/' + world + '/village', reqobj, updateView);
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
        data.conquers = obj;
        updateConquers(data.conquers);
        document.getElementById('conquers').style.display = 'inherit';
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
    document.getElementById('identifier').innerText = details.id;
    document.getElementById('name').innerText = details.name;
    document.getElementById('coords').innerText = '(' + details.x + '|' + details.y + ')';
    document.getElementById('player').innerHTML = details.pid == 0 ? details.owner : '<a href="/' + world + '/player?id=' + details.pid + '">' + details.owner + '</a>';
    document.getElementById('points').innerText = format(details.points);
    document.getElementById('dconquers').innerHTML = '<a href="javascript:void(0);" onclick="changeView(\'conquers\')">' + format(details.conquers) + '</a>';
    document.getElementById('provname').innerText = details.provname;
}

/**
 * Updates the graphs
 * @param {object} history Object containing tribe's history
 */
function updateGraphs(history)
{
    const data = { points: [], timestamps: [] };
    for(let i = history.length - 1; i >= 0; i--)
    {
        const record = history[i];
        const timestamp = new Date(record.timestamp).getTime();
        data.timestamps.push(timestamp);
        data.points.push({ x: timestamp, y: record.points });
    }

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
        conquersPage = page;
        conquersIitems = items;
        conquersFilter = filter;
        document.getElementById('cpp'+conquersItems).checked = true;
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
    const reqobj = { 'id': village_id };

    if(view != '' && view != 'overview')
        reqobj['view'] = view;
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
let page = 1, historyPage = 1, conquersPage = 1;
let filter = '', conquersFilter = '';
let items = 12, historyItems = 12, conquersItems = 12;

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
    page = newpage, historyPage = page;
    build_url_params();
    GET('/api/' + world + '/village', { 'id': village_id, 'view': view, 'page': historyPage, 'items': historyItems }, updateHistory);
}

function updateHistory(obj)
{
    data.history = obj;
    const tdata = {
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

        const pointoffset = row.points - nextrow.points;
        const name = row.name + ' (' + row.x + '|' + row.y +')';
        let pointstr = '<a>' + format(row.points) + '</a><a class="';
        if(pointoffset>0)
            pointstr += 'text-success">(+' + format(pointoffset) + ')</a>';
        else if(pointoffset<0)
            pointstr += 'text-danger">(' + format(pointoffset) + ')</a>';
        else
            pointstr += 'text-warning">(=' + format(pointoffset) + ')</a>';

        tdata.rows.push({
            '#': format(offset + i),
            'name': name,
            'owner': row.pid == 0 ? row['owner'] : '<a href="/' + world + '/player?id=' + row.pid + '">' + row.owner + '</a>',
            'points': pointstr,
            'timestamp': row.timestamp
        });
    }

    historyTable.update(tdata);
}

function conquersChangeItems(newitems)
{
    if(conquers.items != newitems)
    {
        items = newitems, conquersItems = newitems;
        conquersChangePage(1);
    }
}

function conquersChangeFilter(newfilter)
{
    const filterstr = newfilter.toLowerCase();
    if(conquers.filter != filterstr)
    {
        filter = filterstr, conquersFilter = filterstr;
        conquersChangePage(1);
    }
}

function conquersChangePage(newpage)
{
    page = newpage, conquersPage = newpage;
    const reqobj = build_url_params();
    GET('/api/' + world + '/village', reqobj, updateConquers);
}

function updateConquers(obj)
{
    data.conquers = obj;
    const tdata = {
        'page': conquersPage,
        'total': obj.total,
        'rows': []
    };

    const rows = obj.data;
    const offset = (conquersPage - 1) * conquersItems + 1;
    for(let i = 0; i < rows.length; i++)
    {
        const row = rows[i];
        const name = row.name + ' (' + row.x + '|' + row.y + ')';
        const oldowner = row.prevpid == 0 ? row['old owner'] : '<a href="/' + world + '/player?id=' + row.prevpid + '">' + row['old owner'] + '</a>';
        const newowner = row.nextpid == 0 ? row['new onwer'] : '<a href="/' + world + '/player?id=' + row.nextpid + '">' + row['new owner'] + '</a>';
        const oldtribe = row.prevtid == 0 ? row['old tribe'] : '<a href="/' + world + '/tribe?id=' + row.prevtid + '">' + row['old tribe'] + '</a>';
        const newtribe = row.nexttid == 0 ? row['new tribe'] : '<a href="/' + world + '/tribe?id=' + row.nexttid + '">' + row['new tribe'] + '</a>';
    
        tdata.rows.push({
            '#': offset + i,
            'name': name,
            'old owner': oldowner,
            'new owner': newowner,
            'old tribe': oldtribe,
            'new tribe': newtribe,
            'points': row.points,
            'timestamp': row.timestamp
        });
    }

    conquersTable.update(tdata);
}