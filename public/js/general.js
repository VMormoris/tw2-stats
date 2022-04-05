
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
 * Join the given url and arguments in new get like url
 * @param {string} url The url of the endpoint that will handle the request
 * @param {object} args Object containing the extra arguments to be appended on the url
 * @returns {string} The newly created get like url with the arguments appended to it
 */
function join(url, args)
{
    let first = true;
    let fullurl = url;
    for(const prop in args)
    {
        if(args[prop] === '')
            continue;
        fullurl += (first ? '?' : '&') + `${prop}=${args[prop]}`; 
        if(first) first = false;
    }
    return fullurl;
}

/**
 * Checks whether the given to keycode corresponds to a valid character
 * @param {number} keycode The keycode to be checked
 * @returns {boolean} True if the keycode coresponds to a valid character, false otherwise
 */
function ischar(keycode) { return (keycode > 47 && keycode < 58)   || keycode == 32 || (keycode > 64 && keycode < 91)   || (keycode > 95 && keycode < 112)  || (keycode > 185 && keycode < 193) || (keycode > 218 && keycode < 223); }

/**
 * Extracts get parameters from a given url
 * @param {String} url The url from which the get parameters will be extracted
 * @returns {object} Returns an object containing the parameters
 */
function get_params(url)
{
    if(!url.includes('?'))//No get params
        return { 'page': 1 };
    const raw = url.split('?')[1];//Raw parameters string
    const tokens = raw.split('&');
    let obj = {};
    for(let i = 0; i < tokens.length; i++)
    {
        const token = tokens[i];
        const name = token.split('=')[0];
        const value = token.split('=')[1];
        obj[name] = isNaN(value) ? value : parseInt(value);
    }
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
    const end = url.indexOf('/', start);
    const length = end === -1 ? url.length : end-start;
    return url.substr(start, length);
}

/**
 * Format a number to a human friendly form
 * @param {number} num Number to be formatted
 * @returns {string} String containing the number in a human friendly form 
 */
function format(num) { return (num).toLocaleString('en-us'); }