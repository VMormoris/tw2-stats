<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WorldService;

/**
 * Controller for handling Worlds io
 */
class WorldsController extends Controller
{
    protected WorldService $service;

    //Default Constructor
    public function __construct() { $this->service = new WorldService; }

    /**
     * Creates an index pages for players
     * @param string $world Name of the world
     * @return Illuminate\Contracts\View\View A view containing players' index page
     */
    public function index(string $world) { return view('world', ['world' => $world, 'page' => $world]); }

     /**
     * Handles all api requests for world's details
     * @param string $world Name of the world
     * @param Illuminate\Http\Request $req Object containing the http request that made to the server
     * @return array json object containing the apropriate response
     */
    public function details(string $world, Request $req): array
    {
        //Parse all possible url arguments
        $view = $req->input('view', 'overview');
        //Respond according to the given view input
        if($view == 'overview')
            return $this->service->overview($world);
        else if($view == 'tribes')
            return $this->service->tribes($world);
        else if($view == 'players')
            return $this->service->players($world);
        else if($view == 'villages')
            return $this->service->villages($world);
        else
            return array('error' => 'Use of unrecognized view parameter');
    }
}
