<?php

namespace App\Services;

use App\Models\Conquer;
use App\Models\Player;
use App\Models\Tribe;
use App\Models\TribeHistory;
use App\Models\PlayerHistory;
use App\Models\TribeChange;
use App\Models\Village;
use App\Models\VillageHistory;

/**
 * Class for handling tribe logic on our app
 */
class TribeService
{

    /**
     * Getter for a subset of tribes' leaderboard
     * @param string $world Name of the world we are intrested
     * @param string $filter The filter that will be use to check for matching name
     * @param int $offset Number of top tribes that will be skipped
     * @param int $items Number of tribes that will be showned on the leaderboard
     * @return array json object containing the leaderboards records
     */
    public function leaderboard(string $world, string $filter, int $offset, int $items): array
    {
        //"Prepare" query for future use
        $tribes = Tribe::on($world)->select(
            'rankno',
            'id',
            'name',
            'tag',
            'members',
            'villages',
            'points',
            'offbash', 'defbash', 'totalbash',
            'vp'
        )->where('nname', 'like', $filter)
            ->where('id', '!=', 0)
            ->orderBy('rankno', 'ASC');
        
        //Execute query and get the required results
        $count = $tribes->count();
        $tribes = $tribes->skip($offset)->take($items)->get();
        
        return array('total' => $count, 'data' => $tribes);
    }

    /**
     * Getter for all the data need it for tribe's overview page
     * @param string $world Name of the world that we are intrested
     * @param int $id Tribe's id
     * @return array json object containing tribe's overview
     */
    public function overview(string $world, int $id): array
    {
        //Basic tribe's information
        $tribe = Tribe::on($world)->select(
            'rankno',
            'name',
            'points',
            'members',
            'villages',
            'offbash', 'defbash', 'totalbash',
            'vp'
        )->where('id', '=', $id)->get()[0];

        //Extra information
        $tribe['tchanges'] = TribeChange::on($world)->whereColumn('prevtid', '!=', 'nexttid')
            ->where(function($query) use ($id){
                $query->where('prevtid', '=', $id)
                ->orWhere('nexttid', '=', $id);
            })->count();

        $losses = Conquer::on($world)->whereColumn('prevpid', '!=', 'nextpid')
            ->where('prevtid', '=', $id)
            ->where('nextpid', '!=', 0)
            ->count();
        $gains = Conquer::on($world)->whereColumn('prevpid', '!=', 'nextpid')
            ->where('prevtid', '!=', $id)
            ->where('nexttid', '=', $id)
            ->count();
        $internals = Conquer::on($world)->whereColumn('prevpid', '!=', 'nextpid')
            ->where('nextpid', '!=', 0)
            ->where('prevtid', '=', $id)
            ->where('nexttid', '=', $id)
            ->count();
        
        //AUB = A+B-(A^B)
        $all = $gains + $losses - $internals;
        
        $history= TribeHistory::on($world)->select(
            'points',
            'rankno',
            'offbash', 'defbash', 'totalbash',
            'timestamp'
        )->where('tid', '=', $id)
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-3 days', time())))
            ->orderBy('timestamp', 'DESC')
            ->get();

        $villages = TribeHistory::on($world)->select(
            'villages',
            'timestamp'
        )->where('tid', '=', $id)
            ->whereRaw('EXTRACT(HOUR FROM "timestamp") = 0')
            ->orderBy('timestamp', 'DESC')->take(8)->get();
        
        $tribe['conquers'] = array(
            'all' => $all,
            'losses' => $losses,
            'gains' => $gains,
            'internals' => $internals
        );
        
        return array(
            'details' => $tribe,
            'graphs_data' => array(
                'general' => $history,
                'villages' => $villages
            )
        );
    }

    /**
     * Getter for a subset of tribes's history
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param int $offset Number of history records that will be skipped
     * @param int $items Number of history records that will be returned
     * @return array json object containing records of tribe's history
     */
    public function history(string $world, int $id, int $offset, int $items): array
    {
        //"Prepare" query for tribe's history
        $history = TribeHistory::on($world)->select(
            'tid',
            'members',
            'villages',
            'points',
            'offbash', 'defbash', 'totalbash',
            'rankno',
            'vp',
            'timestamp'
        )->where('tid', '=', $id)
            ->orderBy('timestamp', 'DESC')
            ->orderBy('id', 'ASC');
        
        //Execute queries
        $count = $history->count();
        $history = $history->skip($offset)->take($items + 1)->get();

        return array('total' => $count, 'data' => $history);
    }

    /**
     * Getter for tribe's conquers
     * @param string $world Name of the world that we are intrested for
     * @param int $id Tribe's id
     * @param string $show Type of conquers that must be returned
     * @param string $filter Filter for to check for matching village's or player's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned
     * @return array json object containing tribe's conquers
     */
    public function conquers(string $world, int $id, string $show, string $filter, int $offset, int $items): array
    {
        if($show == 'all')
            return $this->all($world, $id, $filter, $offset, $items);
        else if($show == 'losses')
            return $this->losses($world, $id, $filter, $offset, $items);
        else if($show == 'gains')
            return $this->gains($world, $id, $filter, $offset, $items);
        else if($show == 'internals')
            return $this->internals($world, $id, $filter, $offset, $items);
        else
            return array('error' => 'Use of unrecognized show parameter');
    }

    /**
     * Getter for a subset of tribe's members
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param int $offset Number of members records that will be skipped
     * @param int $items Number of members records that will be returned
     * @return array json object containing records of tribe's members
     */
    public function members(string $world, int $id, string $filter, int $offset, int $items): array
    {
        //"Prepare" query for tribe's members
        $members = Player::on($world)->select(
            'id',
            'name',
            'points',
            'offbash', 'defbash', 'totalbash',
            'rankno',
            'villages',
            'vp' 
        )->where('tid', '=', $id)
            ->where('nname', 'like', $filter)
            ->orderBy('rankno', 'ASC');
        
        //Execute queries
        $count = $members->count();
        $members = $members->skip($offset)->take($items)->get();

        return array('total' => $count, 'data' => $members);
    }

    /**
     * Getter for member changes
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned
     * @return array json object containing members "history"
     */
    public function changes(string $world, int $id, int $offset, int $items)
    {
        //"Prepare" query for tribe's changes
        $changes = TribeChange::on($world)->select(
            'tribe_changes.pid', 'pl.name AS player',
            'tribe_changes.prevtid', 'tr1.name AS old tribe',
            'tribe_changes.nexttid', 'tr2.name AS new tribe',
            'tribe_changes.villages',
            'tribe_changes.points',
            'tribe_changes.offbash', 'tribe_changes.defbash', 'tribe_changes.totalbash',
            'tribe_changes.rankno',
            'tribe_changes.vp',
            'tribe_changes.timestamp'
        )->join('players AS pl', 'tribe_changes.pid', '=', 'pl.id')
            ->join('tribes AS tr1', 'tribe_changes.prevtid', '=', 'tr1.id')
            ->join('tribes AS tr2', 'tribe_changes.nexttid', '=', 'tr2.id')
            ->where('tribe_changes.prevtid', '=', $id)
            ->orWhere('tribe_changes.nexttid', '=', $id)
            ->orderBy('tribe_changes.timestamp', 'DESC');
        
        //Execute queries
        $count = $changes->count();
        $changes = $changes->skip($offset)->take($items)->get();

        return array('total' => $count, 'data'=> $changes);
    }

    /**
     * Getter for a subset of tribes's villages
     * @param string $world Name of the world we are intrested
     * @param int $id Tribes's id
     * @param string $filter Filter that will be use to check for matching village name
     * @param int $offset Number of villages that will be skipped
     * @param int $items Number of villages that will be returned
     * @return array json object containing villages records
     */
    public function villages(string $world, int $id, string $filter, int $offset, int $items) : array
    {
        //"Prepare" query for tribe's villages
        $villages = Village::on($world)->select(
            'id',
            'name',
            'x', 'y',
            'points',
            'provname'
        )->where('nname', 'like', $filter)
            ->where('tid', '=', $id)
            ->orderBy('id', 'ASC');
        
        //Execute queries
        $total = $villages->count();
        $villages = $villages->skip($offset)->take($items)->get();

        return array('total' => $total, 'data' => $villages);
    }

    /**
     * Getter for tribe's name
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @return array json object containing tribe's name
     */
    public function name(string $world, int $id)
    {
        $result = Tribe::on($world)->select('name')->where('id', '=', $id)->get()[0];
        return array('name' => $result['name']);
    }

    /**
     * Getter for tribes's conquers of all types
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param string $filter Filter to check for matching village's, player's or tribe's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing conquers of all types
     */
    private function all(string $world, int $id, string $filter, int $offset, int $items)
    {
        //"Prepare" query for tribe conquers
        $all = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tr1.name AS old tribe',
            'conquers.nexttid', 'tr2.name AS new tribe',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->join('tribes AS tr1', 'tr1.id', '=', 'conquers.prevtid')
            ->join('tribes AS tr2', 'tr2.id', '=', 'conquers.nexttid')
            ->whereColumn('conquers.prevpid', '!=', 'conquers.nextpid')
            ->where('conquers.nextpid', '!=', 0)
            ->where(function($query) use ($id) {
                $query->where('conquers.prevtid', '=', $id)
                    ->orWhere('conquers.nexttid', '=', $id);
            })
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('pl1.nname', 'like', $filter)
                    ->orWhere('pl2.nname', 'like', $filter)
                    ->orWhere('tr1.nname', 'like', $filter)
                    ->orWhere('tr2.nname', 'like', $filter);
            })
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');
        
        //Execute queries
        $count = $all->count();
        $all = $all->skip($offset)->take($items)->get();

        return array('all' => array('total' => $count, 'data' => $all));
    }

    /**
     * Getter for tribes's losses
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param string $filter Filter to check for matching village's, player's or tribe's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing losses
     */
    private function losses(string $world, int $id, string $filter, int $offset, int $items)
    {
        //"Prepare" query for tribe's losses
        $losses = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid',
            'conquers.nexttid', 'tribes.name AS new tribe',
            'conquers.points',
            'conquers.timestamp',
        )->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->join('tribes', 'tribes.id', '=', 'conquers.nexttid')
            ->whereColumn('conquers.prevpid', '!=', 'conquers.nextpid')
            ->where('conquers.nextpid', '!=', 0)
            ->where('conquers.prevtid', '=', $id)
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('pl1.nname', 'like', $filter)
                    ->orWhere('pl2.nname', 'like', $filter)
                    ->orWhere('tribes.nname', 'like', $filter);
            })
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');

        //Execute queries
        $count = $losses->count();
        $losses = $losses->skip($offset)->take($items)->get();

        return array('losses' => array('total' => $count, 'data' => $losses));
    }

    /**
     * Getter for tribes's gains
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param string $filter Filter to check for matching village's, player's or tribe's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing gains
     */
    private function gains(string $world, int $id, string $filter, int $offset, int $items)
    {
        //"Prepare" query for Tribe's gains
        $gains = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tribes.name AS old tribe',
            'conquers.nexttid',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->join('tribes', 'tribes.id', '=', 'conquers.prevtid')
            ->whereColumn('conquers.prevpid', '!=', 'conquers.nextpid')
            ->where('conquers.nextpid', '!=', 0)
            ->where('conquers.nexttid', '=', $id)
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('pl1.nname', 'like', $filter)
                    ->orWhere('pl2.nname', 'like', $filter)
                    ->orWhere('tribes.nname', 'like', $filter);
            })
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');

        //Execute queries
        $count = $gains->count();
        $gains = $gains->skip($offset)->take($items)->get();

        return array('gains' => array('total' => $count, 'data' => $gains));
    }

    /**
     * Getter for tribes's internals conquers
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @param string $filter Filter to check for matching village's, player's or tribe's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing internal conquers
     */
    private function internals(string $world, int $id, string $filter, int $offset, int $items)
    {
        //"Prepare" query for tribe's internals conquers
        $internals = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid',
            'conquers.nexttid',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->whereColumn('conquers.prevpid', '!=', 'conquers.nextpid')
            ->where('conquers.nextpid', '!=', 0)
            ->where('conquers.nexttid', '=', $id)
            ->where('conquers.prevtid', '=', $id)
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('pl1.nname', 'like', $filter)
                    ->orWhere('pl2.nname', 'like', $filter);
            })
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');

        //Execute queries
        $count = $internals->count();
        $internals = $internals->skip($offset)->take($items)->get();

        return array('internals' => array('total' => $count, 'data' => $internals));
    }

}