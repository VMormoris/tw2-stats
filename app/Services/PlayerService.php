<?php

namespace App\Services;

use App\Models\Conquer;
use App\Models\Player;
use App\Models\PlayerHistory;
use App\Models\Village;
use App\Models\TribeChange;

use Illuminate\Support\Facades\DB;

/**
 * Class for handling player logic on our app
 */
class PlayerService
{

    /**
     * Getter for a subset of players' leaderboard
     * @param string $world Name of the world we are intrested
     * @param string $filter The filter that will be use to check for matching name
     * @param int $offset Number of top players that will be skipped
     * @param int $items Number of players that will be showned on the leaderboard
     * @return array json object containing the leaderboards records
     */
    public function leaderboard(string $world, string $filter, int $offset, int $items): array
    {
        //"Prepare" query for future use
        $players = Player::on($world)->select(
            'players.rankno',
            'players.id',
            'players.name AS name',
            'players.tid', 'tribes.name AS tname',
            'players.villages',
            'players.points',
            'players.offbash', 'players.defbash', 'players.totalbash',
            'players.vp'
        )->join('tribes', 'tribes.id', '=', 'players.tid')
            ->where('players.id', '!=', 0)
            ->where('players.nname', 'like', $filter)
            ->orderBy('players.rankno', 'ASC');
        
        //Execute query and get the required results
        $count = $players->count();
        $players = $players->skip($offset)->take($items)->get();
        
        return array('total' => $count, 'data' => $players);
    }

    /**
     * Getter for world's overview information from players
     * @param string $world Name of the world that we are intrested
     * @return array json object containing world's overview info from players
     */
    public function world(string $world): array
    {
        //Total number of active tribes
        $total = Player::on($world)->count();
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
        $history = PlayerHistory::on($world)->select('pid', 'rankno', 'timestamp')
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-7 days', time())))
            ->whereIn('pid', function($query){
                $query->select('id')->from('players');
            })
            ->orderBy('timestamp')
            ->get();

        return array('players' => array(
            'total' => $total,
            'top5' => $best5,
            'history' => $history
        ));
    }

    /**
     * Getter for all the data need it for player's overview page
     * @param string $world Name of the world that we are intrested
     * @param int $id Player's id
     * @return array json object containing player's overview
     */
    public function overview(string $world, int $id): array
    {
        //Basic players information
        $player = Player::on($world)->select(
            'players.rankno',
            'players.name AS name',
            'players.tid', 'tribes.name AS tname',
            'players.points',
            'players.villages',
            'players.offbash', 'players.defbash', 'players.totalbash',
            'players.vp'
        )->join('tribes', 'tribes.id', '=', 'players.tid')
            ->where('players.id', '=', $id)->get()[0];
        
        //Extra information
        $player['tchanges'] = TribeChange::on($world)->where('pid', '=', $id)->whereColumn('prevtid', '!=', 'nexttid')->count();
        $losses = Conquer::on($world)->where('prevpid', '=', $id)->count();
        $gains = Conquer::on($world)->where('nextpid', '=', $id)->count();
        $player['conquers'] = array('losses' => $losses, 'gains' => $gains);

        //Get history data for graphs
        $history = PlayerHistory::on($world)->select(
            'points',
            'rankno',
            'offbash', 'defbash', 'totalbash',
            'timestamp'
        )->where('pid', '=', $id)
            ->where('timestamp', '>', date('Y-m-d H:i:s', strtotime('-3 days', time())))
            ->orderBy('timestamp', 'DESC')->get();
        
        $villages = PlayerHistory::on($world)->select(
            'villages',
            'timestamp'
        )->where('pid', '=', $id)
            ->whereRaw('EXTRACT(HOUR FROM "timestamp") = 0')
            ->orderBy('timestamp', 'DESC')->take(8)->get();

        return array(
            'details' => $player, 
            'graphs_data' => array(
                'general' => $history,
                'villages' => $villages
            )
        );
    }

    /**
     * Getter for a subset of player's history
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param int $offset Number of history records that will be skipped
     * @param int $items Number of history records that will be returned
     * @return array json object containing records of player's history
     */
    public function history(string $world, int $id, int $offset, int $items): array
    {
        //"Prepare" query for player's history
        $history = PlayerHistory::on($world)->select(
            'players_history.tid', 'tribes.name AS new tribe',
            'players_history.villages',
            'players_history.points',
            'players_history.offbash', 'players_history.defbash', 'players_history.totalbash',
            'players_history.rankno',
            'players_history.vp',
            'players_history.timestamp' 
        )->join('tribes', 'tribes.id', '=', 'players_history.tid')
            ->where('players_history.pid', '=', $id)
            ->orderBy('players_history.timestamp', 'DESC');
        
        //Execute query
        $count = $history->count();
        $history = $history->skip($offset)->take($items + 1)->get();

        return array('total' => $count, 'data' => $history);
    }

    /**
     * Getter for player's conquers
     * @param string $world Name of the world that we are intrested for
     * @param int $id Player's id
     * @param string $show Type of conquers that must be returned
     * @param string $filter Filter for to check for matching village's or player's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned
     * @return array json object containing player's conquers
     */
    public function conquers(string $world, int $id, string $show, string $filter, int $offset, int $items)
    {        
        if($show == 'all')
            return $this->all($world, $id, $filter, $offset, $items);
        else if($show == 'losses')
            return $this->losses($world, $id, $filter, $offset, $items);
        else if($show == 'gains')
            return $this->gains($world, $id, $filter, $offset, $items);
        else
            return array('error' => 'Use of unrecognized show parameter');
    }

    /**
     * Getter for a subset of player's villages
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param string $filter Filter that will be use to check for matching village name
     * @param int $offset Number of villages that will be skipped
     * @param int $items Number of villages that will be returned
     * @return array json object containing villages records
     */
    public function villages(string $world, int $id, string $filter, int $offset, int $items) : array
    {
        //"Prepare" query for player's villages
        $villages = Village::on($world)->select(
            'id',
            'name',
            'x', 'y',
            'points',
            'provname'
        )->where('nname', 'like', $filter)
            ->where('pid', '=', $id)
            ->orderBy('id', 'ASC');
        
        //Execute queries
        $total = $villages->count();
        $villages = $villages->skip($offset)->take($items)->get();
    
        return array('total' => $total, 'data' => $villages);
    }

    /**
     * Getter for player's conquers of all types
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param string $filter Filter to check for matching village's or player's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing conquers of all types
     */
    private function all(string $world, int $id, string $filter, int $offset, int $items): array
    {
        //"Prepare" conquers query
        $conquers = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', 'pl1.id', '=', 'conquers.prevpid')
            ->join('players AS pl2', 'pl2.id', '=', 'conquers.nextpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->where('conquers.nextpid', '!=', 0)
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                ->orWhere('pl1.nname', 'like', $filter)
                ->orWhere('pl2.nname', 'like', $filter);
            })
            ->whereColumn('conquers.prevpid', '!=', 'conquers.nextpid')
            ->where(function($query) use ($id){
                $query->where('conquers.nextpid', '=', $id)
                    ->orWhere('conquers.prevpid', '=', $id);
            })
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');
        
        //Execute queries
        $count = $conquers->count();
        $conquers = $conquers->skip($offset)->take($items)->get();

        return array('all' => array('total' => $count, 'data' => $conquers));
    }

    /**
     * Getter for player's losses
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param string $filter Filter to check for matching village's or player's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing losses
     */
    private function losses(string $world, int $id, string $filter, int $offset, int $items): array
    {
        //"Prepare" losses query
        $losses = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid',
            'conquers.nextpid', 'players.name AS new owner',
            'conquers.points',
            'conquers.timestamp'
        )->join('players', 'players.id', '=', 'conquers.nextpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->where('conquers.nextpid', '!=', 0)
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('players.nname', 'like', $filter);
            })
            ->where('conquers.prevpid', '=', $id)
            ->where('conquers.nextpid', '!=', $id)
            ->where('conquers.nextpid', '!=', 0)
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');
        
        //Executre queries
        $count = $losses->count();
        $losses = $losses->skip($offset)->take($items)->get();

        return array('losses' => array('total' => $count, 'data' => $losses));
    }

    /**
     * Getter for player's gains
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param string $filter Filter to check for matching village's or player's name
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned 
     * @return array json object containing gains
     */
    private function gains(string $world, int $id, string $filter, int $offset, int $items): array
    {
        //"Prepare" gains query
        $gains = Conquer::on($world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'players.name AS old owner',
            'conquers.nextpid',
            'conquers.points',
            'conquers.timestamp'
        )->join('players', 'players.id', '=', 'conquers.prevpid')
            ->join('villages', 'villages.id', '=', 'conquers.vid')
            ->where(function($query) use ($filter){
                $query->where('conquers.nname', 'like', $filter)
                    ->orWhere('players.nname', 'like', $filter);
            })
            ->where('conquers.prevpid', '!=', $id)
            ->where('conquers.nextpid', '=', $id)
            ->orderBy('conquers.timestamp', 'DESC')
            ->orderBy('conquers.id', 'ASC');
        
        //Execute queries
        $count = $gains->count();
        $gains = $gains->skip($offset)->take($items)->get();

        return array('gains' => array('total' => $count, 'data' => $gains));
    }

    /**
     * Getter for tribe changes
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @param int $offset Number of records that will be skipped
     * @param int $items Number of records that will be returned
     * @return array json object containing Tribe Change's of players
     */
    public function changes(string $world, int $id, int $offset, int $items)
    {
        //"Prepare" tribe changes query
        $changes = TribeChange::on($world)->select(
            'tribe_changes.prevtid', 'tr1.name AS old tribe',
            'tribe_changes.nexttid', 'tr2.name AS new tribe',
            'tribe_changes.villages',
            'tribe_changes.points',
            'tribe_changes.offbash', 'tribe_changes.defbash', 'tribe_changes.totalbash',
            'tribe_changes.rankno',
            'tribe_changes.vp',
            'tribe_changes.timestamp'
        )->join('tribes AS tr1', 'tribe_changes.prevtid', '=', 'tr1.id')
            ->join('tribes AS tr2', 'tribe_changes.nexttid', '=', 'tr2.id')
            ->where('tribe_changes.pid', '=', $id)
            ->orderBy('tribe_changes.timestamp', 'DESC');
        
        //Execute queries
        $count = $changes->count();
        $changes = $changes->skip($offset)->take($items)->get();

        return array('total' => $count, 'data' => $changes);
    }

    /**
     * Getter for player's conquers stats
     * @param string $world Name of the world we intrested
     * @param int $id Player's id
     * @param string $spec Specification for the stats we are intrested
     * @return array json object containing stats
     */
    public function stats(string $world, int $id, string $spec)
    {
        if($spec == 'pvt_gains')
            return $this->pvt_gains($world, $id);
        else if($spec == 'pvt_losses')
            return $this->pvt_losses($world, $id);
        else if($spec == 'pvp_gains')
            return $this->pvp_gains($world, $id);
        else if($spec == 'pvp_losses')
            return $this->pvp_losses($world, $id);
        else
            return array('error' => 'Use of unrecognized stats specification');
    }

    /**
     * Getter for Player VS Tribe gains stats
     * @param string $world Name of the world we intrested
     * @param int $id Player's id
     * @return array json object containing stats
     */
    private function pvt_gains(string $world, int $id)
    {
        /**
         * Actual query we want to execute:
         *   WITH pvt_gains AS
         *   (
         *       SELECT
         *          ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS "num",
         *          prevtid AS "tid",
         *          COUNT(*) AS "gains"
         *       FROM conquers
         *       WHERE nextpid = $id
         *       GROUP BY prevtid
         *       ORDER BY "gains" DESC
         *   )
         *   SELECT pvt_gains."num", tr.name AS "name", pvt_gains."gains" FROM pvt_gains
         *   INNER JOIN tribes AS tr ON tr.id = pvt_gains."tid"
         *   WHERE num BETWEEN 1 AND 6
         *   UNION
         *   SELECT 7, 'Other', SUM("gains") AS "gains" FROM pvt_gains WHERE num > 6
         *   ORDER BY "num";
         */

        $pvt_gains = Conquer::on($world)->select(
            DB::connection($world)->raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'prevtid AS tid',
            DB::connection($world)->raw('COUNT(*) AS gains')
        )->where('nextpid', '=', $id)
            ->groupBy('prevtid')
            ->orderBy('gains', 'DESC');

        $best6 = DB::connection($world)->table('pvt_gains')
        ->select(
            'pvt_gains.num AS num',
            'tr.name AS name',
            'pvt_gains.gains AS gains'
        )->withExpression('pvt_gains', $pvt_gains)
            ->join('tribes AS tr', 'tr.id', '=', 'tid')
            ->whereBetween('num', [1, 6]);
        
        $total = DB::connection($world)->table('pvt_gains')->withExpression('pvt_gains', $pvt_gains)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::connection($world)->table('pvt_gains')
                ->select(
                    DB::connection($world)->raw('
                        7 AS num,
                        \'Other\' AS name,
                        SUM(pvt_gains.gains) AS gains
                    ')
                )->withExpression('pvt_gains', $pvt_gains)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }

        return array('pvt_gains' => $result->get());
    }

    /**
     * Getter for Player VS Tribe losses stats
     * @param string $world Name of the world we intrested
     * @param int $id Player's id
     * @return array json object containing stats
     */
    private function pvt_losses(string $world, int $id)
    {
        /**
         * Actual query we want to execute:
         *   WITH pvt_losses AS
         *   (
         *       SELECT
         *          ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS "num",
         *          nexttid AS "tid",
         *          COUNT(*) AS "losses"
         *       FROM conquers
         *       WHERE prevpid = $id
         *       GROUP BY nexttid
         *       ORDER BY "losses" DESC
         *   )
         *   SELECT pvt_losses."num", tr.name AS "name", pvt_losses."losses" FROM pvt_losses
         *   INNER JOIN tribes AS tr ON tr.id = pvt_losses."tid"
         *   WHERE num BETWEEN 1 AND 6
         *   UNION
         *   SELECT 7, 'Other', SUM("losses") AS "losses" FROM pvt_losses WHERE num > 6
         *   ORDER BY "num";
         */

        $pvt_losses = Conquer::on($world)->select(
            DB::connection($world)->raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'nexttid AS tid',
            DB::connection($world)->raw('COUNT(*) AS losses')
        )->where('prevpid', '=', $id)
            ->groupBy('nexttid')
            ->orderBy('losses', 'DESC');

        $best6 = DB::connection($world)->table('pvt_losses')
        ->select(
            'pvt_losses.num AS num',
            'tr.name AS name',
            'pvt_losses.losses AS losses'
        )->withExpression('pvt_losses', $pvt_losses)
            ->join('tribes AS tr', 'tr.id', '=', 'tid')
            ->whereBetween('num', [1, 6]);
        
            $total = DB::connection($world)->table('pvt_losses')->withExpression('pvt_losses', $pvt_losses)->count();
            if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::connection($world)->table('pvt_losses')
                ->select(
                    DB::connection($world)->raw('
                        7 AS num,
                        \'Other\' AS name,
                        SUM(pvt_losses.losses) AS losses
                    ')
                )->withExpression('pvt_losses', $pvt_losses)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }        
        return array('pvt_losses' => $result->get());
    }

    /**
     * Getter for Player VS Player gains stats
     * @param string $world Name of the world we intrested
     * @param int $id Player's id
     * @return array json object containing stats
     */
    private function pvp_gains(string $world, int $id)
    {
        /**
         * Actual query we want to execute:
         * WITH pvp_gains AS
         *   (
         *       SELECT
         *           ROW_NUMBER OVER (ORDER BY COUNT(*) DESC) AS "num",
         *           prevpid AS "pid",
         *           COUNT(*) AS "gains"
         *       WHERE nextpid = $id
         *       GROUP BY prevpid
         *       ORDER BY "gains" DESC
         *   )
         *   SELECT pvp_gains."num", pl.name AS "name", pvp_gains."gains" FROM pvp_gains
         *   INNER JOIN players AS pl ON pl.id = pvp_gains."pid"
         *   WHERE num BETWEEN 1 AND 6
         *   UNION
         *   SELECT 7, 'Other', SUM("gains") AS "gains" FROM pvp_gains WHERE num > 6
         *   ORDER BY "num";
         */
        
        $pvp_gains = Conquer::on($world)->select(
            DB::connection($world)->raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'prevpid AS pid',
            DB::connection($world)->raw('COUNT(*) AS gains')
        )->where('nextpid', '=', $id)
            ->groupBy('prevpid')
            ->orderBy('gains', 'DESC');

        $best6 = DB::connection($world)->table('pvp_gains')
            ->select(
                'pvp_gains.num AS num',
                'pl.name AS name',
                'pvp_gains.gains AS gains'
            )->withExpression('pvp_gains', $pvp_gains)
                ->join('players AS pl', 'pl.id', '=', 'pid')
                ->whereBetween('num', [1, 6]);
        
        $total = DB::connection($world)->table('pvp_gains')->withExpression('pvp_gains', $pvp_gains)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::connection($world)->table('pvp_gains')
                ->select(
                    DB::connection($world)->raw('
                        7 AS num,
                        \'Other\' AS name,
                        SUM(pvp_gains.gains) AS gains
                    ')
                )->withExpression('pvp_gains', $pvp_gains)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }
        return array('pvp_gains' => $result->get());
    }
    
    /**
     * Getter for Player VS Player losses stats
     * @param string $world Name of the world we intrested
     * @param int $id Player's id
     * @return array json object containing stats
     */
    private function pvp_losses(string $world, int $id)
    {
        /**
         * Actual query we want to execute:
         * WITH pvp_losses AS
         *   (
         *       SELECT
         *           ROW_NUMBER OVER (ORDER BY COUNT(*) DESC) AS "num",
         *           nextpid AS "pid",
         *           COUNT(*) AS "losses"
         *       WHERE prevpid = $id
         *       GROUP BY nextpid
         *       ORDER BY "losses" DESC
         *   )
         *   SELECT pvp_losses."num" AS "num", pl.name AS "name", pvp_losses."losses" FROM pvp_losses
         *   INNER JOIN players AS pl ON pl.id = pvp_losses."pid"
         *   WHERE "num" BETWEEN 1 AND 6
         *   UNION
         *   SELECT 7, 'Other', SUM("losses") AS "losses" FROM pvp_losses WHERE num > 6
         *   ORDER BY "num";
         */

        $pvp_losses = Conquer::on($world)->select(
            DB::connection($world)->raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'nextpid AS pid',
            DB::connection($world)->raw('COUNT(*) AS losses')
        )->where('prevpid', '=', $id)
            ->groupBy('nextpid')
            ->orderBy('losses', 'DESC');
        
        $best6 = DB::connection($world)->table('pvp_losses')
            ->select(
                'pvp_losses.num AS num',
                'pl.name AS name',
                'pvp_losses.losses AS losses'
            )->withExpression('pvp_losses', $pvp_losses)
                ->join('players AS pl', 'pl.id', '=', 'pid')
                ->whereBetween('num', [1, 6]);

        $total = DB::connection($world)->table('pvp_losses')->withExpression('pvp_losses', $pvp_losses)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::connection($world)->table('pvp_losses')
                ->select(
                    DB::connection($world)->raw('
                        7 AS num,
                        \'Other\' AS name,
                        SUM(pvp_losses.losses) AS losses
                    ')
                )->withExpression('pvp_losses', $pvp_losses)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }
        return array('pvp_losses' => $result->get());
    }

    /**
     * Getter for player's name
     * @param string $world Name of the world we are intrested
     * @param int $id Player's id
     * @return array json object containing player's name
     */
    public function name(string $world, int $id)
    {
        $result = Player::on($world)->select('name')->where('id', '=', $id)->get()[0];
        return array('name' => $result['name']);
    }
}