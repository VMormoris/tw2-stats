/**
 * Class for handling the behaviour of tables (leaderboards)
 *  on our websiter
 */
 class Table{

    #ctx;// Context of the html table element
    #ipp;// Number of items per page that are currently displayed
    #page;// Current page
    #onItemsChange;// Callback for when items per page changes
    #onPageChange;
    #onFilterChange;
    #clockHandle

    /**
     * Creates a new table objects
     * @param {object} ctx Table html context
     * @param {object} settings Starter settings for our table
     */
    constructor(ctx, settings)
    {
        this.#ctx = ctx;
        this.#ipp = settings['ipp'];
        this.#page = settings['page'];
        this.#onItemsChange = settings['onItemsChange'];
        this.#onPageChange = settings['onPageChange'];
        const itemsButtons = this.#ctx.getElementsByClassName('ipp');
        for(let i = 0; i < itemsButtons.length; i++)
        {
            itemsButtons[i].addEventListener('click', (event) => { 
                const ipp = parseInt(event.target.id.substring(3));
                this.#ipp = ipp;
                this.#onItemsChange(ipp);
            });
        }
        
        if(settings.hasOwnProperty('onFilterChange'))
        {
            this.#onFilterChange = settings['onFilterChange'];
            
            this.#ctx.getElementsByClassName('searchbar')[0].addEventListener('keyup', (event) =>{
                clearTimeout(this.#clockHandle);
                this.#clockHandle = setTimeout(()=>{
                    this.#onFilterChange(event.target.value);
                }, 500);
            });
        }
        else
            this.#onFilterChange = null;
    }

    /**
     * Updates table
     * @param {object} data 
     */
    update(data)
    {
        const totalpages = Math.ceil(data.total / this.#ipp);
        this.#page = data.page;
        {//Pages creation
            let pages = this.#ctx.getElementsByClassName('pages')[0];
            pages.innerHTML = '';//Clear old pages

            const prev = this.#createArrow(false, this.#page != 1);
            const next = this.#createArrow(true, this.#page != totalpages);
            
            pages.appendChild(prev);
            pages.appendChild(this.#createPage(1));
            if(this.#page - 2 > 2)
                pages.appendChild(this.#createPage(0));
            for(let i = this.#page - 2; i <= this.#page + 2; i++)
            {
                if(i<=1 || i>=totalpages)
                    continue;
                pages.appendChild(this.#createPage(i));
            }
            if(this.#page + 2 < totalpages - 1)
                pages.appendChild(this.#createPage(0));
            if(totalpages>1)
                pages.appendChild(this.#createPage(totalpages));
            pages.appendChild(next);
        }

        {//Table population
            let contents = this.#ctx.getElementsByClassName('table-contents')[0];
            contents.innerHTML = '';//Clear table contents
            const rows = data.rows;
            let th = document.createElement('th');
            th.scope = 'col';
            for(let i = 0; i < rows.length; i++)
            {
                const row = rows[i];    
                let identifier = true;
                let rowel = document.createElement('tr');
                for(const prop in row)
                {
                    if(identifier)
                    {
                        let header = th.cloneNode(true);
                        header.innerText = row[prop];
                        rowel.appendChild(header);
                        identifier = false;
                    }
                    else
                    {
                        let td = document.createElement('td');
                        td.innerHTML = row[prop];
                        rowel.appendChild(td);
                    }
                }
                contents.appendChild(rowel);
            }
        }
    }

    /**
     * Creates an element for a pages arrows (next or previous)
     * @param {boolean} next  Flag for whether is left or right
     * @param {boolean} enable Flag for whether can be pressed or not
     */
    #createArrow(next, enable = true)
    {
        //Boring stuff just building a custom html element
        let symbol = document.createElement('span');
        symbol.ariaHidden = true;
        symbol.innerText= next ? '\u00BB' : '\u00AB';

        let label = document.createElement('span');
        label.className = 'sr-only';
        label.innerText = next ? 'Next' : 'previous';

        const topage = next ? this.#page + 1 : this.#page - 1; 
        let plink = document.createElement('a');
        plink.className = 'page-link';
        plink.setAttribute('href', '#');
        if(enable)
            plink.addEventListener('click', (event)=>{
                event.preventDefault();
                this.#page = topage;
                this.#onPageChange(topage);
            });
        
        plink.appendChild(symbol);
        plink.appendChild(label);

        let el = document.createElement('li');
        el.className = 'page-item';
        if(!enable)
            el.classList.add('disabled');

        el.appendChild(plink);
        return el;
    }

    /**
     * Creates an HTML Element for page button button 
     * @param {number} page Number of the page button to be created, 0 for ... 
     * @returns {HTMLLIElement} The newly created HTML element
     */
    #createPage(page)
    {
        //Boring stuff just building a custom html element
        let span = document.createElement('span');
        span.className = 'sr-only';
        span.innerText = '(current)';

        let plink = document.createElement('a');
        plink.className = 'page-link';
        plink.setAttribute('href', '#');
        plink.innerText = page == 0 ? '...' : page.toString();

        let el = document.createElement('li');
        el.className = 'page-item';

        if(this.#page == page)
        {
            el.classList.add('active');
            plink.appendChild(span);
        }   
        else if(page == 0)
            el.classList.add('disabled');
        else
            plink.addEventListener('click', (event) =>{
                event.preventDefault();
                this.#page = page;
                this.#onPageChange(page);
            });
        el.appendChild(plink);
        return el;
    }

}