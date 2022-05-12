<?php

namespace App\Services;

use App\Models\Conquer;
use App\Models\Tribe;
use App\Models\TribeHistory;
use App\Models\Player;
use App\Models\PlayerHistory;
use App\Models\Village;
use App\Models\World;

/**
 * Class for handling player logic on our app
 */
class WorldService
{
    /**
     * Getter for all data that need from tw2-stats Db for world Overview
     * @param string $world Name of the world that we are intrested
     * @return array json object containing world's overview information
     */
    public function overview(string $world): array
    {
        $world = World::on('tw2-stats')->select(
            'win_condition', 'win_ammount',
            'tribes', 'players', 'villages',
            'start', 'end'
        )->where('wid', '=', $world)
            ->get()[0];
        
        return array('world' => $world);
    }

    /**
     * Getter for world's overview information from tribes
     * @param string $world Name of the world that we are intrested
     * @return array json object containing world's overview info from tribes
     */
    public function tribes(string $world): array
    {
        //Top 5 tribes
        $best5 = Tribe::on($world)->select(
            'rankno',
            'id',
            'name',
            'points',
            'members',
            'villages'
        )->where('active', '=', true)
            ->where('id', '<>', 0)
            ->orderBy('rankno')
            ->take(5)
            ->get();
        //History data for graphs
        $history = TribeHistory::on($world)->select('tid AS id', 'rankno', 'timestamp')
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-7 days', time())))
            ->whereIn('tid', function($query){
                $query->select('id')->from('tribes')->where('rankno', '<=', 5);
            })
            ->orderBy('timestamp')
            ->get();

        return array(
            'top5' => $best5,
            'history' => $history,
            'count' => $this->domination_villages($world)
        );
    }

    /**
     * Getter for world's overview information from players
     * @param string $world Name of the world that we are intrested
     * @return array json object containing world's overview info from players
     */
    public function players(string $world): array
    {
        //Top 5 tribes
        $best5 = Player::on($world)->select(
            'rankno',
            'id',
            'name',
            'points',
            'villages'
        )->where('id', '<>', 0)
            ->orderBy('rankno')
            ->take(5)
            ->get();
        //History data for graphs
        $history = PlayerHistory::on($world)->select('pid AS id', 'rankno', 'timestamp')
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-7 days', time())))
            ->whereIn('pid', function($query){
                $query->select('id')->from('players')->where('rankno', '<=', 5);
            })
            ->orderBy('timestamp')
            ->get();

        return array(
            'top5' => $best5,
            'history' => $history,
            'count' => $this->domination_villages($world)
        );
    }

    /**
     * Getter for world's overview information from villages
     * @param string $world Name of the world that we are intrested
     * @return array json object containing world's overview info from villages
     */
    public function villages(string $world): array
    {
        //Last 5 conquers
        $best5 = Conquer::on($world)->select(
            'conquers.vid', 'villages.name AS vname',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tr1.name AS old tribe',
            'conquers.nexttid', 'tr2.name AS new tribe',
            'conquers.timestamp',
        )->join('villages', 'villages.id', '=', 'conquers.vid')
            ->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->join('tribes AS tr1', 'tr1.id', '=', 'conquers.prevtid')
            ->join('tribes AS tr2', 'tr2.id', '=', 'conquers.nexttid')
            ->orderBy('timestamp', 'DESC')
            ->take(5)
            ->get();

        return array(
            'top5' => $best5
        );
    }

    /**
     * Getter for the village owned by the 10 top tribes on a specified world
     * @param string $world Name of the world (in game ID)
     * @return int Number of the villages
     */
    private function domination_villages(string $world)
    {
        return Village::on($world)->whereIn('tid', function($query){
            $query->select('id')->from('tribes')->where('rankno', '<=', 10)->where('active', '=', true);
        })->count();
    }

    /**
     * Getter for actual world name
     * @param string $world Name of the world (in game ID)
     * @return string object containing the actual world name
     */
    public function name(string $world): string { return World::on('tw2-stats')->select('name')->where('wid', '=', $world)->get()[0]['name']; }
}