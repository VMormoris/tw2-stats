<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlayerService;

/**
 * Controller for handling Players io
 */
class PlayersController extends Controller
{
    protected PlayerService $service;
    private string $page = 'Players';

    //Default Constructor
    public function __construct() { $this->service = new PlayerService; }

    /**
     * Creates an index pages for players
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing players' index page
     */
    public function index(string $world) { return view('players', ['world' => $world, 'page' => $this->page]); }
    
    /**
     * Creates a page for a specific player
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing a page for specific player 
     */
    public function show(string $world) { return view('player', ['world' => $world, 'page' => $this->page]); }

    /**
     * Handles request for subsets of players' leaderboards
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request that made to the server
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
     * Handles all requests for player's details
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
        $show = $req->input('show', 'all');
        $filter = $req->input('filter', '');
        $items = $req->input('items', 12);
        
        //Transforms inputs
        $filter = '%' . $filter . '%';
        $offset = ($page - 1) * $items;

        //Respond according to the given view input
        if($view == 'overview')
            return $this->service->overview($world, $id);
        else if($view == 'history')
            return $this->service->history($world, $id, $offset, $items);
        else if($view == 'conquers')
            return $this->service->conquers($world, $id, $show, $filter, $offset, $items);
        else if($view == 'villages')
            return $this->service->villages($world, $id, $filter, $offset, $items);
        else if($view == 'name')
            return $this->service->name($world, $id);
        else
            return array('error' => 'Use of unrecognized view parameter');
    }

}