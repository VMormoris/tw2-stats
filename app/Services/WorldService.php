<?php

namespace App\Services;

use App\Models\Conquer;
use App\Models\Tribe;
use App\Models\Player;
use App\Models\Village;
use App\Models\World;

use Illuminate\Support\Facades\DB;

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
        $best5 = Tribe::where('tribes.wid', $world)->select(
            'rankno',
            'id',
            'name',
            'points',
            'members',
            'villages',
            'vp'
        )->where('active', '=', true)
            ->where('id', '<>', 0)
            ->orderBy('rankno')
            ->take(5)
            ->get();
        //History data for graphs
        $top5 = DB::table('tribes')->select('id')->where('tribes.wid', $world)->where('rankno', '<=', 5);
        $history = History::tribes($world)->hourly($top5, ['tid AS id', 'rankno'], now()->subDays(7))
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
        $best5 = Player::where('players.wid', $world)->select(
            'rankno',
            'id',
            'name',
            'points',
            'villages',
            'vp'
        )->where('id', '<>', 0)
            ->orderBy('rankno')
            ->take(5)
            ->get();
        //History data for graphs
        $top5 = DB::table('players')->select('id')->where('players.wid', $world)->where('rankno', '<=', 5);
        $history = History::players($world)->hourly($top5, ['pid AS id', 'rankno'], now()->subDays(7))
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
        $best5 = Conquer::where('conquers.wid', $world)->select(
            'conquers.vid', 'villages.name AS vname',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tr1.name AS old tribe',
            'conquers.nexttid', 'tr2.name AS new tribe',
            'conquers.timestamp',
        )->join('villages', fn ($join) => $join->on('villages.id', '=', 'conquers.vid')->where('villages.wid', '=', $world))
            ->join('players AS pl1', fn ($join) => $join->on('pl1.id', '=', 'conquers.prevpid')->where('pl1.wid', '=', $world))
            ->join('players AS pl2', fn ($join) => $join->on('pl2.id', '=', 'conquers.nextpid')->where('pl2.wid', '=', $world))
            ->join('tribes AS tr1', fn ($join) => $join->on('tr1.id', '=', 'conquers.prevtid')->where('tr1.wid', '=', $world))
            ->join('tribes AS tr2', fn ($join) => $join->on('tr2.id', '=', 'conquers.nexttid')->where('tr2.wid', '=', $world))
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
        return Village::where('villages.wid', $world)->whereIn('tid', function($query) use ($world){
            $query->select('id')->from('tribes')->where('tribes.wid', $world)->where('rankno', '<=', 10)->where('active', '=', true);
        })->count();
    }

    /**
     * Getter for actual world name
     * @param string $world Name of the world (in game ID)
     * @return string object containing the actual world name
     */
    public function name(string $world): string { return World::on('tw2-stats')->select('name')->where('wid', '=', $world)->get()[0]['name']; }
}