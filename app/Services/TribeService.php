<?php

namespace App\Services;

use App\Models\Conquer;
use App\Models\Player;
use App\Models\Tribe;
use App\Models\TribeHistory;
use App\Models\TribeChange;
use App\Models\Village;
use App\Models\VillageHistory;

use Illuminate\Support\Facades\DB;

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
        $tribes = Tribe::where('tribes.wid', $world)->select(
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
            ->where('active', '=', true)
            ->where('id', '!=', 0)
            ->orderBy('rankno', 'ASC');
        
        //Execute query and get the required results
        $count = $tribes->count();
        $tribes = $tribes->skip($offset)->take($items)->get();
        
        //Yesterday's comparison values: every field is its own MAX over the values the
        //tribe held from yesterday 00:00 to 23:00, the value carried in from before 00:00
        //included, so the fields are not one snapshot
        $start = now()->subDays(1)->format('Y-m-d 00:00:00');
        $end = now()->subDays(1)->format('Y-m-d 23:00:00');
        $ids = DB::table('tribes')->select('id')->where('tribes.wid', $world)
            ->where('nname', 'like', $filter)
            ->where('active', '=', true)
            ->where('id', '!=', 0)
            ->orderBy('rankno', 'ASC')
            ->skip($offset)
            ->take($items);
        $details = History::tribes($world)->held($ids, ['rankno', 'points', 'members', 'villages', 'offbash', 'defbash', 'totalbash', 'vp'], $start, $end)->select(
            'tid AS id',
            DB::raw('MAX(rankno) AS oldrank, MAX(points) AS oldpoints, MAX(members) AS oldmembers, MAX(villages) AS oldvillages, MAX(offbash) AS oldoffbash, MAX(defbash) AS olddefbash, MAX(totalbash) AS oldtotalbash, MAX(vp) AS oldvp')
        )->groupBy('tid')
            ->get();

        return array('total' => $count, 'data' => $tribes, 'details' => $details);
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
        $tribe = Tribe::where('tribes.wid', $world)->select(
            'rankno',
            'name',
            'points',
            'members',
            'villages',
            'offbash', 'defbash', 'totalbash',
            'vp'
        )->where('id', '=', $id)->get()[0];

        //Extra information
        $tribe['tchanges'] = TribeChange::where('tribe_changes.wid', $world)->whereColumn('prevtid', '!=', 'nexttid')
            ->where(function($query) use ($id){
                $query->where('prevtid', '=', $id)
                ->orWhere('nexttid', '=', $id);
            })->count();

        $losses = Conquer::where('conquers.wid', $world)
            ->where('prevtid', '=', $id)
            ->count();
        $gains = Conquer::where('conquers.wid', $world)
            ->where('nexttid', '=', $id)
            ->count();
        $internals = Conquer::where('conquers.wid', $world)
            ->where('prevtid', '=', $id)
            ->where('nexttid', '=', $id)
            ->count();
        
        //AUB = A+B-(A^B)
        $all = $gains + $losses - $internals;
        
        //History data for graphs: the values at every hour of the past 3 days, the last held
        //value carried forward up to the last dump the tribe was in
        $history = History::tribes($world)->hourly([$id], [
            'points',
            'rankno',
            'offbash', 'defbash', 'totalbash'
        ], now()->subDays(3))
            ->orderBy('timestamp')
            ->get();

        //The villages at the last 8 midnights, carried forward the same way
        $villages = History::tribes($world)->daily($id, ['villages'], 8);
        
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
        $history = TribeHistory::where('tribes_history.wid', $world)->select(
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
        if($show === 'all')
            return $this->all($world, $id, $filter, $offset, $items);
        else if($show === 'losses')
            return $this->losses($world, $id, $filter, $offset, $items);
        else if($show === 'gains')
            return $this->gains($world, $id, $filter, $offset, $items);
        else if($show === 'internals')
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
        $members = Player::where('players.wid', $world)->select(
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

        //Yesterday's comparison values: every field is its own MAX over the values the
        //player held from yesterday 00:00 to 23:00, the value carried in from before 00:00
        //included, so the fields are not one snapshot
        $start = now()->subDays(1)->format('Y-m-d 00:00:00');
        $end = now()->subDays(1)->format('Y-m-d 23:00:00');
        $ids = DB::table('players')->select('id')->where('players.wid', $world)
            ->where('nname', 'like', $filter)
            ->where('id', '!=', 0)
            ->orderBy('rankno', 'ASC')
            ->skip($offset)
            ->take($items);
        $details = History::players($world)->held($ids, ['rankno', 'points', 'villages', 'offbash', 'defbash', 'totalbash', 'vp'], $start, $end)->select(
            'pid AS id',
            DB::raw('MAX(rankno) AS oldrank, MAX(points) AS oldpoints, MAX(villages) AS oldvillages, MAX(offbash) AS oldoffbash, MAX(defbash) AS olddefbash, MAX(totalbash) AS oldtotalbash, MAX(vp) AS oldvp')
        )->groupBy('pid')
            ->get();

        return array('total' => $count, 'data' => $members, 'details' => $details);
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
        $villages = Village::where('villages.wid', $world)->select(
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
        $all = Conquer::where('conquers.wid', $world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tr1.name AS old tribe',
            'conquers.nexttid', 'tr2.name AS new tribe',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', fn ($join) => $join->on('pl1.id', '=', 'conquers.prevpid')->where('pl1.wid', '=', $world))
            ->join('villages', fn ($join) => $join->on('villages.id', '=', 'conquers.vid')->where('villages.wid', '=', $world))
            ->join('players AS pl2', fn ($join) => $join->on('pl2.id', '=', 'conquers.nextpid')->where('pl2.wid', '=', $world))
            ->join('tribes AS tr1', fn ($join) => $join->on('tr1.id', '=', 'conquers.prevtid')->where('tr1.wid', '=', $world))
            ->join('tribes AS tr2', fn ($join) => $join->on('tr2.id', '=', 'conquers.nexttid')->where('tr2.wid', '=', $world))
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
        $losses = Conquer::where('conquers.wid', $world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid',
            'conquers.nexttid', 'tribes.name AS new tribe',
            'conquers.points',
            'conquers.timestamp',
        )->join('players AS pl1', fn ($join) => $join->on('pl1.id', '=', 'conquers.prevpid')->where('pl1.wid', '=', $world))
            ->join('villages', fn ($join) => $join->on('villages.id', '=', 'conquers.vid')->where('villages.wid', '=', $world))
            ->join('players AS pl2', fn ($join) => $join->on('pl2.id', '=', 'conquers.nextpid')->where('pl2.wid', '=', $world))
            ->join('tribes', fn ($join) => $join->on('tribes.id', '=', 'conquers.nexttid')->where('tribes.wid', '=', $world))
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
        $gains = Conquer::where('conquers.wid', $world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid', 'tribes.name AS old tribe',
            'conquers.nexttid',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', fn ($join) => $join->on('pl1.id', '=', 'conquers.prevpid')->where('pl1.wid', '=', $world))
            ->join('villages', fn ($join) => $join->on('villages.id', '=', 'conquers.vid')->where('villages.wid', '=', $world))
            ->join('players AS pl2', fn ($join) => $join->on('pl2.id', '=', 'conquers.nextpid')->where('pl2.wid', '=', $world))
            ->join('tribes', fn ($join) => $join->on('tribes.id', '=', 'conquers.prevtid')->where('tribes.wid', '=', $world))
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
        $internals = Conquer::where('conquers.wid', $world)->select(
            'conquers.vid',
            'conquers.name',
            'villages.x', 'villages.y',
            'conquers.prevpid', 'pl1.name AS old owner',
            'conquers.nextpid', 'pl2.name AS new owner',
            'conquers.prevtid',
            'conquers.nexttid',
            'conquers.points',
            'conquers.timestamp'
        )->join('players AS pl1', fn ($join) => $join->on('pl1.id', '=', 'conquers.prevpid')->where('pl1.wid', '=', $world))
            ->join('villages', fn ($join) => $join->on('villages.id', '=', 'conquers.vid')->where('villages.wid', '=', $world))
            ->join('players AS pl2', fn ($join) => $join->on('pl2.id', '=', 'conquers.nextpid')->where('pl2.wid', '=', $world))
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
        $changes = TribeChange::where('tribe_changes.wid', $world)->select(
            'tribe_changes.pid', 'pl.name AS player',
            'tribe_changes.nexttid', 'tr.name AS new tribe',
            'tribe_changes.villages',
            'tribe_changes.points',
            'tribe_changes.offbash', 'tribe_changes.defbash', 'tribe_changes.totalbash',
            'tribe_changes.rankno',
            'tribe_changes.vp',
            'tribe_changes.timestamp'
        )->join('players AS pl', fn ($join) => $join->on('tribe_changes.pid', '=', 'pl.id')->where('pl.wid', '=', $world))
            ->join('tribes AS tr', fn ($join) => $join->on('tribe_changes.nexttid', '=', 'tr.id')->where('tr.wid', '=', $world))
            ->where(function($query) use ($id){
                $query->where('tribe_changes.prevtid', '=', $id)
                    ->orWhere('tribe_changes.nexttid', '=', $id);
            })
            ->orderBy('tribe_changes.timestamp', 'DESC');
        
        //Execute queries
        $count = $changes->count();
        $changes = $changes->skip($offset)->take($items)->get();

        return array('total' => $count, 'data'=> $changes);
    }

    /**
     * Getter for tribe's conquers stats
     * @param string $world Name of the world we intrested
     * @param int $id Tribe's id
     * @param string $spec Specification for the stats we are intrested
     * @return array json object containing stats
     */
    public function stats(string $world, int $id, string $spec)
    {
        if($spec === 'gvt')
            return $this->tvt_gains($world, $id);
        else if($spec === 'lvt')
            return $this->tvt_losses($world, $id);
        else if($spec === 'gvp')
            return $this->tvp_gains($world, $id);
        else if($spec === 'lvp')
            return $this->tvp_losses($world, $id);
        else
            return array('error' => 'Use of unrecognized stats specification');
    }

    /**
     * Getter for Tribe VS Tribe gains stats
     * @param string $world Name of the world we intrested
     * @param int $id Tribe's id
     * @return array json object containing stats
     */
    private function tvt_gains(string $world, int $id)
    {
        /**
         * Actual queries we want to execute, with :world and :id the bound values.
         * Ranks the tribes the tribe conquered villages from by number of conquers
         * (one CTE row each):
         *
         *   WITH tvt_gains AS
         *   (
         *       SELECT
         *           ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num,
         *           prevtid AS tid,
         *           COUNT(*) AS gains
         *       FROM conquers
         *       WHERE conquers.wid = :world AND nexttid = :id
         *       GROUP BY prevtid
         *       ORDER BY gains DESC
         *   )
         *
         * A separate "SELECT COUNT(*) FROM tvt_gains" (with the same CTE) gives the number of rows.
         *
         * With 6 rows or fewer, the best 6 are the result:
         *   WITH tvt_gains AS (...)
         *   SELECT tvt_gains.num AS num, tr.id AS id, tr.name AS name, tvt_gains.gains AS gains
         *   FROM tvt_gains
         *   INNER JOIN tribes AS tr ON tr.id = tid AND tr.wid = :world
         *   WHERE num BETWEEN 1 AND 6
         *   ORDER BY num;
         *
         * With more than 6, the remaining rows are summed into an "Other" row,
         * used as the first UNION operand (each side has its own copy of the CTE).
         * The final ORDER BY num puts "Other" after the best 6:
         *   WITH tvt_gains AS (...)
         *   SELECT 7 AS num, 0 AS id, 'Other' AS name, SUM(tvt_gains.gains) AS gains
         *   FROM tvt_gains
         *   WHERE num > 6
         *   UNION
         *   <the best 6 query above, without its ORDER BY>
         *   ORDER BY num;
         */

        $tvt_gains = Conquer::where('conquers.wid', $world)->select(
            DB::raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'prevtid AS tid',
            DB::raw('COUNT(*) AS gains')
        )->where('nexttid', '=', $id)
            ->groupBy('prevtid')
            ->orderBy('gains', 'DESC');

        $best6 = DB::table('tvt_gains')
        ->select(
            'tvt_gains.num AS num',
            'tr.id AS id',
            'tr.name AS name',
            'tvt_gains.gains AS gains'
        )->withExpression('tvt_gains', $tvt_gains)
            ->join('tribes AS tr', fn ($join) => $join->on('tr.id', '=', 'tid')->where('tr.wid', '=', $world))
            ->whereBetween('num', [1, 6]);
        
        $total = DB::table('tvt_gains')->withExpression('tvt_gains', $tvt_gains)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::table('tvt_gains')
                ->select(
                    DB::raw('
                        7 AS num,
                        0 AS id,
                        \'Other\' AS name,
                        SUM(tvt_gains.gains) AS gains
                    ')
                )->withExpression('tvt_gains', $tvt_gains)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }

        return array('gvt' => $result->get());
    }

    /**
     * Getter for Tribe VS Tribe losses stats
     * @param string $world Name of the world we intrested
     * @param int $id Tribe's id
     * @return array json object containing stats
     */
    private function tvt_losses(string $world, int $id)
    {
        /**
         * Actual queries we want to execute, with :world and :id the bound values.
         * Ranks the tribes that conquered the tribe's villages by number of conquers
         * (one CTE row each):
         *
         *   WITH tvt_losses AS
         *   (
         *       SELECT
         *           ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num,
         *           nexttid AS tid,
         *           COUNT(*) AS losses
         *       FROM conquers
         *       WHERE conquers.wid = :world AND prevtid = :id
         *       GROUP BY nexttid
         *       ORDER BY losses DESC
         *   )
         *
         * A separate "SELECT COUNT(*) FROM tvt_losses" (with the same CTE) gives the number of rows.
         *
         * With 6 rows or fewer, the best 6 are the result:
         *   WITH tvt_losses AS (...)
         *   SELECT tvt_losses.num AS num, tr.id AS id, tr.name AS name, tvt_losses.losses AS losses
         *   FROM tvt_losses
         *   INNER JOIN tribes AS tr ON tr.id = tid AND tr.wid = :world
         *   WHERE num BETWEEN 1 AND 6
         *   ORDER BY num;
         *
         * With more than 6, the remaining rows are summed into an "Other" row,
         * used as the first UNION operand (each side has its own copy of the CTE).
         * The final ORDER BY num puts "Other" after the best 6:
         *   WITH tvt_losses AS (...)
         *   SELECT 7 AS num, 0 AS id, 'Other' AS name, SUM(tvt_losses.losses) AS losses
         *   FROM tvt_losses
         *   WHERE num > 6
         *   UNION
         *   <the best 6 query above, without its ORDER BY>
         *   ORDER BY num;
         */

        $tvt_losses = Conquer::where('conquers.wid', $world)->select(
            DB::raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'nexttid AS tid',
            DB::raw('COUNT(*) AS losses')
        )->where('prevtid', '=', $id)
            ->groupBy('nexttid')
            ->orderBy('losses', 'DESC');

        $best6 = DB::table('tvt_losses')
        ->select(
            'tvt_losses.num AS num',
            'tr.id AS id',
            'tr.name AS name',
            'tvt_losses.losses AS losses'
        )->withExpression('tvt_losses', $tvt_losses)
            ->join('tribes AS tr', fn ($join) => $join->on('tr.id', '=', 'tid')->where('tr.wid', '=', $world))
            ->whereBetween('num', [1, 6]);
        
        $total = DB::table('tvt_losses')->withExpression('tvt_losses', $tvt_losses)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::table('tvt_losses')
                ->select(
                    DB::raw('
                        7 AS num,
                        0 AS id,
                        \'Other\' AS name,
                        SUM(tvt_losses.losses) AS losses
                    ')
                )->withExpression('tvt_losses', $tvt_losses)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }        
        return array('lvt' => $result->get());
    }

    /**
     * Getter for Tribe VS Player gains stats
     * @param string $world Name of the world we intrested
     * @param int $id Tribe's id
     * @return array json object containing stats
     */
    private function tvp_gains(string $world, int $id)
    {
        /**
         * Actual queries we want to execute, with :world and :id the bound values.
         * Ranks the players the tribe conquered villages from by number of conquers
         * (one CTE row each):
         *
         *   WITH tvp_gains AS
         *   (
         *       SELECT
         *           ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num,
         *           prevpid AS pid,
         *           COUNT(*) AS gains
         *       FROM conquers
         *       WHERE conquers.wid = :world AND nexttid = :id
         *       GROUP BY prevpid
         *       ORDER BY gains DESC
         *   )
         *
         * A separate "SELECT COUNT(*) FROM tvp_gains" (with the same CTE) gives the number of rows.
         *
         * With 6 rows or fewer, the best 6 are the result:
         *   WITH tvp_gains AS (...)
         *   SELECT tvp_gains.num AS num, pl.id AS id, pl.name AS name, tvp_gains.gains AS gains
         *   FROM tvp_gains
         *   INNER JOIN players AS pl ON pl.id = pid AND pl.wid = :world
         *   WHERE num BETWEEN 1 AND 6
         *   ORDER BY num;
         *
         * With more than 6, the remaining rows are summed into an "Other" row,
         * used as the first UNION operand (each side has its own copy of the CTE).
         * The final ORDER BY num puts "Other" after the best 6:
         *   WITH tvp_gains AS (...)
         *   SELECT 7 AS num, 0 AS id, 'Other' AS name, SUM(tvp_gains.gains) AS gains
         *   FROM tvp_gains
         *   WHERE num > 6
         *   UNION
         *   <the best 6 query above, without its ORDER BY>
         *   ORDER BY num;
         */
        
        $tvp_gains = Conquer::where('conquers.wid', $world)->select(
            DB::raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'prevpid AS pid',
            DB::raw('COUNT(*) AS gains')
        )->where('nexttid', '=', $id)
            ->groupBy('prevpid')
            ->orderBy('gains', 'DESC');

        $best6 = DB::table('tvp_gains')
            ->select(
                'tvp_gains.num AS num',
                'pl.id AS id',
                'pl.name AS name',
                'tvp_gains.gains AS gains'
            )->withExpression('tvp_gains', $tvp_gains)
                ->join('players AS pl', fn ($join) => $join->on('pl.id', '=', 'pid')->where('pl.wid', '=', $world))
                ->whereBetween('num', [1, 6]);
        
        $total = DB::table('tvp_gains')->withExpression('tvp_gains', $tvp_gains)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::table('tvp_gains')
                ->select(
                    DB::raw('
                        7 AS num,
                        0 AS id,
                        \'Other\' AS name,
                        SUM(tvp_gains.gains) AS gains
                    ')
                )->withExpression('tvp_gains', $tvp_gains)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }
        return array('gvp' => $result->get());
    }
    
    /**
     * Getter for Tribe VS Player losses stats
     * @param string $world Name of the world we intrested
     * @param int $id Tribe's id
     * @return array json object containing stats
     */
    private function tvp_losses(string $world, int $id)
    {
        /**
         * Actual queries we want to execute, with :world and :id the bound values.
         * Ranks the players that conquered the tribe's villages by number of conquers
         * (one CTE row each):
         *
         *   WITH tvp_losses AS
         *   (
         *       SELECT
         *           ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num,
         *           nextpid AS pid,
         *           COUNT(*) AS losses
         *       FROM conquers
         *       WHERE conquers.wid = :world AND prevtid = :id
         *       GROUP BY nextpid
         *       ORDER BY losses DESC
         *   )
         *
         * A separate "SELECT COUNT(*) FROM tvp_losses" (with the same CTE) gives the number of rows.
         *
         * With 6 rows or fewer, the best 6 are the result:
         *   WITH tvp_losses AS (...)
         *   SELECT tvp_losses.num AS num, pl.id AS id, pl.name AS name, tvp_losses.losses AS losses
         *   FROM tvp_losses
         *   INNER JOIN players AS pl ON pl.id = pid AND pl.wid = :world
         *   WHERE num BETWEEN 1 AND 6
         *   ORDER BY num;
         *
         * With more than 6, the remaining rows are summed into an "Other" row,
         * used as the first UNION operand (each side has its own copy of the CTE).
         * The final ORDER BY num puts "Other" after the best 6:
         *   WITH tvp_losses AS (...)
         *   SELECT 7 AS num, 0 AS id, 'Other' AS name, SUM(tvp_losses.losses) AS losses
         *   FROM tvp_losses
         *   WHERE num > 6
         *   UNION
         *   <the best 6 query above, without its ORDER BY>
         *   ORDER BY num;
         */

        $tvp_losses = Conquer::where('conquers.wid', $world)->select(
            DB::raw('ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) AS num'),
            'nextpid AS pid',
            DB::raw('COUNT(*) AS losses')
        )->where('prevtid', '=', $id)
            ->groupBy('nextpid')
            ->orderBy('losses', 'DESC');
        
        $best6 = DB::table('tvp_losses')
            ->select(
                'tvp_losses.num AS num',
                'pl.id AS id',
                'pl.name AS name',
                'tvp_losses.losses AS losses'
            )->withExpression('tvp_losses', $tvp_losses)
                ->join('players AS pl', fn ($join) => $join->on('pl.id', '=', 'pid')->where('pl.wid', '=', $world))
                ->whereBetween('num', [1, 6]);

        $total = DB::table('tvp_losses')->withExpression('tvp_losses', $tvp_losses)->count();
        if($total<=6)
            $result = $best6->orderBy('num');
        else
        {
            $result = DB::table('tvp_losses')
                ->select(
                    DB::raw('
                        7 AS num,
                        0 AS id,
                        \'Other\' AS name,
                        SUM(tvp_losses.losses) AS losses
                    ')
                )->withExpression('tvp_losses', $tvp_losses)
                    ->where('num', '>', 6)
                    ->union($best6)
                    ->orderBy('num');
        }
        return array('lvp' => $result->get());
    }

    /**
     * Getter for tribe's name
     * @param string $world Name of the world we are intrested
     * @param int $id Tribe's id
     * @return array json object containing tribe's name
     */
    public function name(string $world, int $id)
    {
        $result = Tribe::where('tribes.wid', $world)->select('name')->where('id', '=', $id)->get()[0];
        return array('name' => $result['name']);
    }
}