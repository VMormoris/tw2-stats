<?php

namespace App\Http\Controllers;

use App\Services\VillageService;
use Illuminate\Http\Request;

/**
 * Controller for handling Villages io
 */
class VillagesController extends Controller
{
    protected VillageService $service;
    private string $page = 'Villages';

    //Default Contructor
    public function __construct() { $this->service = new VillageService; }
    
    /**
     * Creates an index pages for villages
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing villages' index page
     */
    public function index(string $world) { return view('villages', ['world' => $world, 'page' => $this->page]); }

    /**
     * Creates a page for a specific village
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing a page for specific village 
     */
    public function show(string $world) { return view('village', ['world' => $world, 'page' => $this->page]); }

    /**
     * Handles request for a subset of world conquers
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request
     * @return array json object conctaining the subset of wolrd conquers 
     */
    public function global_conquers(string $world, Request $req): array
    {
        //Parse all possible url arguments
        $page = intval($req->input('page', 1));
        $filter = $req->input('filter', '');
        $items = intval($req->input('items', 12));

        //Transform inputs
        $filter = '%' . $filter . '%';
        $offset = ($page - 1) * $items;

        return $this->service->global_conquers($world, $filter, $offset, $items);
    }

    /**
     * Handles all api requests for village's details
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request that made to the server
     * @return array json object containing the apropriate response
     */
    public function details(string $world, Request $req): array
    {
        //Parse all possible url arguments
        $view = $req->input('view', 'overview');
        $id = intval($req->input('id', 0));
        $page = intval($req->input('page', 1));
        $filter = $req->input('filter', '');
        $items = $req->input('items', 12);
    
        //Transforms inputs
        $filter = '%' . $filter . '%';
        $offset = ($page - 1) * $items;

        //Respond according to the given view input
        if($view === 'overview')
            return $this->service->overview($world, $id);
        else if($view === 'history')
            return $this->service->history($world, $id, $offset, $items);
        else if($view === 'conquers')
            return $this->service->conquers($world, $id, $filter, $offset, $items);
        else if($view === 'name')
            return $this->service->name($world, $id);
        else
            return array('error' => 'Use of unrecognized view parameter');
    }

}