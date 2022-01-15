<?php

namespace App\Services;

use App\Models\Village;
use App\Models\VillageHistory;

/**
 * Class for handling village logic on our app
 */
class VillageService
{
    /**
     * Getter for a subset of all conquers in the world
     * @param string $world Name of the world we are intrested
     * @param string $filter The filter that will be use to check for matching village, player or tribe name
     * @param int $offset Number of last conquered villages that will be skipped
     * @param int $items Number of last conquered villages that will be showned on the table
     * @return array json object containing the conquers records
     */
    public function global_conquers(string $world, string $filter, int $offset, int $items)
    {
        //"Prepare" global conquers queries
        $conquers = VillageHistory::on($world)->select(
            'villages_history.vid',
            'villages_history.name AS name',
            'villages.x', 'villages.y',
            'villages_history.prevpid', 'pl1.name AS old owner',
            'villages_history.nextpid', 'pl2.name AS new owner',
            'villages_history.prevtid', 'tr1.name AS old tribe',
            'villages_history.nexttid', 'tr2.name AS new tribe',
            'villages_history.points',
            'villages_history.timestamp'
        )->join('players AS pl1', 'pl1.id', '=', 'villages_history.prevpid')
            ->join('villages', 'villages.id', '=', 'villages_history.vid')
            ->join('players AS pl2', 'pl2.id', '=', 'villages_history.nextpid')
            ->join('tribes AS tr1', 'tr1.id', '=', 'villages_history.prevtid')
            ->join('tribes AS tr2', 'tr2.id', '=', 'villages_history.nexttid')
            ->whereColumn('villages_history.prevpid', '!=', 'villages_history.nextpid')
            ->where('villages_history.nextpid', '!=', 0)
            ->where(function($query) use ($filter){
                $query->where('villages_history.nname', 'like', $filter)
                    ->orWhere('pl1.nname', 'like', $filter)
                    ->orWhere('pl2.nname', 'like', $filter)
                    ->orWhere('tr1.nname', 'like', $filter)
                    ->orWhere('tr2.nname', 'like', $filter);
            })
            ->orderBy('villages_history.timestamp', 'DESC')
            ->orderBy('villages_history.id', 'ASC');

        //Execute queries
        $count = $conquers->count();
        $conquers = $conquers->skip($offset)->take($items)->get();

        return array('total' => $count, 'data' => $conquers);
    }

    /**
     * Getter for all the data need it for village's overview page
     * @param string $world Name of the world that we are intrested
     * @param int $id Village's id
     * @return array json object containing village's overview
     */
    public function overview(string $world, int $id): array
    {
        $village = Village::on($world)->select(
            'villages.id',
            'villages.name',
            'villages.x', 'villages.y',
            'villages.pid', 'players.name AS owner',
            'villages.points',
            'villages.provname'
        )->join('players', 'players.id', '=', 'villages.pid')
            ->where('villages.id', '=', $id)->get()[0];
        
        //Extra information
        $village['conquers'] = VillageHistory::on($world)->where('vid', '=', $id)->whereColumn('prevpid', '!=', 'nextpid')->where('nextpid', '!=', 0)->count();
        $history = VillageHistory::on($world)->select(
            'points',
            'timestamp'
        )->where('vid', '=', $id)
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-3 days', time())))
            ->orderBy('timestamp', 'DESC')
            ->get();
        
        return array('details' => $village, 'graphs_data' => $history);
    }

    /**
     * Getter for a subset of village's history
     * @param string $world Name of the world we are intrested
     * @param int $id Village's id
     * @param int $offset Number of history records that will be skipped
     * @param int $items Number of history records that will be returned
     * @return array json object containing records of village's history
     */
    public function history(string $world, int $id, int $offset, int $items): array
    {
        //"Prepare" query for village's history
        $history = VillageHistory::on($world)->select(
            'villages_history.name',
            'villages.x', 'villages.y',
            'villages_history.nextpid', 'players.name AS owner',
            'villages_history.points',
            'villages_history.timestamp'
        )->join('players', 'players.id', '=', 'villages_history.nextpid')
            ->join('villages', 'villages.id', '=', 'villages_history.vid')
            ->where('villages_history.vid', '=', $id)
            ->orderBy('timestamp', 'DESC');
        
        //Execute query
        $count = $history->count();
        $history = $history->skip($offset)->take($items + 1)->get();

        return array('total' => $count, 'data' => $history);
    }

    /**
     * Getter for villages's conquers
     * @param string $world Name of the world that we are intrested for
     * @param int $id Village's id
     * @param string $filter Filter for to check for matching village's, player's or tribe's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned
     * @return array json object containing village's conquers
     */
    public function conquers(string $world, int $id, string $filter, int $offset, int $items)
    {        
        //"Prepare" village's conquers query
        $conquers = VillageHistory::on($world)->select(
            'villages_history.name',
            'villages.x', 'villages.y',
            'villages_history.prevpid', 'pl1.name AS old owner',
            'villages_history.nextpid', 'pl2.name AS new owner',
            'villages_history.prevtid', 'tr1.name AS old tribe',
            'villages_history.nexttid', 'tr2.name AS new tribe',
            'villages_history.points',
            'villages_history.timestamp'
        )->join('villages', 'villages.id', '=', 'villages_history.vid')
            ->join('players AS pl1', 'pl1.id', '=', 'villages_history.prevpid')
            ->join('players AS pl2', 'pl2.id', '=', 'villages_history.nextpid')
            ->join('tribes AS tr1', 'tr1.id', '=', 'villages_history.prevtid')
            ->join('tribes AS tr2', 'tr2.id', '=', 'villages_history.nexttid')
            ->where('villages_history.vid', '=', $id)
            ->whereColumn('villages_history.prevpid', '!=', 'villages_history.nextpid')
            ->orderBy('villages_history.timestamp', 'DESC')
            ->orderBy('villages_history.id', 'ASC');
        
        //Execute queries
        $count = $conquers->count();
        $conquers = $conquers->skip($offset)->take($items)->get();

        return array('total' => $count, 'data' => $conquers);
    }

    /**
     * Getter for village's name
     * @param string $world Name of the world we are intrested
     * @param int $id Village's id
     * @return array json object containing village's name
     */
    public function name(string $world, int $id)
    {
        $result = Village::on($world)->select('name', 'x', 'y')->where('id', '=', $id)->get()[0];
        $name = $result['name'] . ' (' . $result['x'] . '|' . $result['y'] . ')'; 
        return array('name' => $name);
    }

}