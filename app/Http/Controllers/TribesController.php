<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TribeService;

class TribesController extends Controller
{
    protected TribeService $service;
    private string $page = 'Tribes';

    //Default Constructor
    public function __construct() { $this->service = new TribeService; }
    
    /**
     * Creates an index pages for tribes
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing tribes' index page
     */
    public function index($world) { return view('tribes', ['world' => $world, 'page' => $this->page]); }

    /**
     * Creates a page for a specific tribe
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request
     * @return Illuminate\Contracts\View\View A view containing a page for specific tribe 
     */
    public function show($world, Request $req)
    {
        $id = intval($req->input('id', 0));
        $name = $this->service->name($world, $id)['name'];
        return view('tribe', ['world' => $world, 'page' => $this->page, 'name' => $name]);
    }

    /**
     * Handles request for subset of tribes' leaderboard
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request
     * @return array json object containing the requested subset of the leaderboard
     */
    public function leaderboard(string $world, Request $req): array
    {
        //Parse all possible url arguments
        $page = intval($req->input('page', 1));
        $filter = $req->input('filter', '');
        $items = intval($req->input('items', 12));

        //Transform inputs
        $filter = '%' . $filter . '%';
        $offset = ($page - 1) * $items;

        return $this->service->leaderboard($world, $filter, $offset, $items);
    }

    /**
     * Handles all api requests for tribe's details
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request
     * @return array json object containing the apropriate response
     */
    public function details(string $world, Request $req)
    {
        //Parse all possible url arguments
        $view = $req->input('view', 'overview');
        $id = intval($req->input('id', 0));
        $page = intval($req->input('page', 1));
        $show = $req->input('show', 'all');
        $filter = $req->input('filter', '');
        $items = $req->input('items', 12);
        $spec = $req->input('spec', 'tvt_gains');

        //Transforms inputs
        $filter = '%' . $filter . '%';
        $offset = ($page - 1) * $items;

        if($view === 'overview')
            return $this->service->overview($world, $id);
        else if($view === 'history')
            return $this->service->history($world, $id, $offset, $items);
        else if($view === 'conquers')
            return $this->service->conquers($world, $id, $show, $filter, $offset, $items);
        else if($view === 'members')
            return $this->service->members($world, $id, $filter, $offset, $items);
        else if($view === 'changes')
            return $this->service->changes($world, $id, $offset, $items);
        else if($view === 'stats')
            return $this->service->stats($world, $id, $spec);
        //else if($view == 'villages')
        //    return $this->service->villages($world, $id, $filter, $offset, $items);
        else if($view === 'name')
            return $this->service->name($world, $id);
        else
            return array('error' => 'Use of unrecognized view');
    }

}