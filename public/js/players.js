const endpoint = 'players';

let world = '';
let page = 1;
let filter = '';
let items = 12;
let leaderboard = null;

var onload = function()
{
    const url = document.location.href;
    const params = get_params(url);
    if(params.hasOwnProperty('items'))
    {
        items = params.items;
        document.getElementById('ipp'+items).checked = true;
    }
    if(params.hasOwnProperty('filter'))
        filter = params.filter;
    world = extract_world(url);

    const ctx = document.getElementById('table');
    leaderboard = new Table(ctx,{
        'ipp': items,
        'page': page,
        'onItemsChange': changeItems,
        'onPageChange': changePage,
        'onFilterChange': changeFilter,
    });
    changePage(page);
};

/**
 * Callback for changing page
 * @param {number} newpage The new page
 */
function changePage(newpage)
{
    page = newpage;
    build_url_params();
    GET('/api/' + world + '/players', {'page': page, 'items': items, 'filter': filter}, update);
}

/**
 * Callback for change the number of items that are displayed on table
 * @param {number} newitems The number of items that will be displayed
 */
function changeItems(newitems)
{
    if(items != newitems)
    {
        items = newitems
        changePage(1);
    }
}

/**
 * Callback for when the filter changes
 * @param {string} newfilter The new assigned filter
 */
function changeFilter(newfilter)
{
    const filterstr = newfilter.toLowerCase();
    if(filter != filterstr)
    {
        filter = filterstr;
        changePage(1);       
    }
}

/**
 * Updates the table's content base on the received object
 * @param {object} obj Response to be transform for table population
 */
function update(obj)
{
    const data = {
        'page': page,
        'total': obj.total,
        'rows': []
    };
    
    const rows = obj.data;
    for(let i=0; i<rows.length; i++)
    {
        const row = rows[i];

        const tribestr = row.tid == 0 ? row.tname : '<a href="/' + world + '/tribe?id=' + row.tid + '">' + row.tname + '</a>';
        const villstr = row.villages == 0 ? row.villages : '<a href="/' + world + '/player?id=' + row.id + '&view=villages">' + format(row.villages) + '</a>';
        
        data.rows.push({
            'rank': format(row.rankno),
            'name': '<a href="/' + world + '/player?id=' + row.id + '">' + row.name + '</a>',
            'tribe': tribestr,
            'villages': villstr,
            'points': format(row.points),
            'defbash': format(row.defbash),
            'offbash': format(row.offbash),
            'totalbash': format(row.totalbash),
            'vp': format(row.vp)
        });
    }
    leaderboard.update(data);
}

function build_url_params()
{
    let params = '';
    if(page!=1)
        params += 'page=' + page;
    if(filter != '')
        params += (params == '' ? '' : '&') + 'filter=' + filter;
    if(items != 12)
        params += (params == '' ? '' : '&') + 'items=' + items;
    if(params != '')
        window.history.replaceState(null, null, endpoint + '?' + params);
    else
        window.history.replaceState(null, null, endpoint);
}