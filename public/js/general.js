//Variable that holds all global variables to shared between components
const globals = {};

/**
 * Performs a get request with the specified arguments
 * @param {string} url The url of the endpoint that will handle the request
 * @param {object} args Arguments for the request
 * @param {callback} callback Function to handle ther response
 * @warning This function is meant to be use for our API and so it assumes that the response is a json object
 */
function GET(url, args, callback)
{
    let req = new XMLHttpRequest();
    req.responseType = 'json';
    req.onreadystatechange = function()
    {
        if(this.readyState == 4 && (this.status == 200 || this.status == 204))
            callback(this.response);
    };

    const fullurl = join(url, args);
    req.open('get', fullurl, true);
    req.send();
}

/**
 * Builds an encoded query string from the given arguments
 * @param {object} args Object containing the arguments to be encoded
 * @param {function} skip Optional callback that returns true for the arguments that should be left out
 * @returns {string} The encoded query string, prefixed with '?', or an empty string when no argument is left
 */
function query_string(args, skip = null)
{
    const query = new URLSearchParams();
    for(const prop in args)
    {
        if(skip && skip(prop, args[prop]))
            continue;
        query.append(prop, args[prop]);
    }
    const str = query.toString();
    return str === '' ? '' : '?' + str;
}

/**
 * Join the given url and arguments in new get like url
 * @param {string} url The url of the endpoint that will handle the request
 * @param {object} args Object containing the extra arguments to be appended on the url
 * @returns {string} The newly created get like url with the arguments appended to it
 */
function join(url, args) { return url + query_string(args, (name, value) => value === ''); }

/**
 * Checks whether the given to keycode corresponds to a valid character
 * @param {number} keycode The keycode to be checked
 * @returns {boolean} True if the keycode coresponds to a valid character, false otherwise
 */
function ischar(keycode) { return (keycode > 47 && keycode < 58)   || keycode == 32 || (keycode > 64 && keycode < 91)   || (keycode > 95 && keycode < 112)  || (keycode > 185 && keycode < 193) || (keycode > 218 && keycode < 223); }

/**
 * Extracts get parameters from a given url
 * @param {String} url The url (or just the query string) from which the get parameters will be extracted
 * @returns {object} Returns an object containing the parameters
 * @note Only the id, page and items parameters are converted to numbers (when they hold an integer),
 *  every other value (e.g. filter, view, show) is kept as the string that was decoded from the url
 */
function get_params(url)
{
    const start = url.indexOf('?');
    const raw = start === -1 ? '' : url.substring(start + 1).split('#')[0];
    if(raw === '')//No get params
        return { 'page': 1 };

    const numeric = ['id', 'page', 'items'];
    let obj = {};
    for(const [name, value] of new URLSearchParams(raw))
        obj[name] = numeric.includes(name) && /^-?\d+$/.test(value) ? parseInt(value) : value;
    return obj;
}

/**
 * Extracts world from a given url
 * @param {String} url Thre url from which the world will be extracted
 * @returns {String} World name
 */
function extract_world(url)
{
    const start = url.indexOf('/', 9) + 1;
    const endindex = url.indexOf('/', start);
    const end = endindex === -1 ? url.length : endindex;
    return url.substring(start, end);
}

/**
 * Format a number to a human friendly form
 * @param {number} num Number to be formatted
 * @returns {string} String containing the number in a human friendly form 
 */
function format(num) { return (num).toLocaleString('en-us'); }

/**
 * Transforms a given string to it's formal equivalent. It basically Capitalizes
 *  the first letter of the string.
 * @param {string} str String to be formalized 
 * @returns {string} The formal equivalent string
 */
function formalize(str) { return str.charAt(0).toUpperCase() + str.substring(1); }

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
 * Sets the url parameters for the given user input
 */
function build_url_params(reqobj)
{
    const params = query_string(reqobj, (prop, value) =>
        //Ingore default values
        (prop === 'page' && value === 1 ) ||
        (prop === 'filter' && value === '') ||
        (prop === 'items' && value === 12) ||
        (prop === 'view' && value === 'overview') ||
        (prop === 'show' && value === 'all')
    );

    const fullurl = document.location.pathname + params;
    window.history.replaceState(null, null, fullurl);
}