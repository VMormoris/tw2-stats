<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Queries on a history table that only stores changes
 *
 * A history row holds from its timestamp until the entity's next row, so the
 * value at a time T is the last row at or before T. Every query is limited to
 * the world, the inner ones included, so only its partitions are scanned.
 *
 * The SQL in the comments is what History::players() builds; tribes and
 * villages are the same with tid and vid.
 */
class History
{
    /**
     * @param string $world Name of the world (in game ID)
     * @param string $entities Table with the entities' current values
     * @param string $table The entities' history table
     * @param string $key Column of the history table with the entity's id
     */
    private function __construct(
        private string $world,
        private string $entities,
        private string $table,
        private string $key
    ) {}

    public static function tribes(string $world): self { return new self($world, 'tribes', 'tribes_history', 'tid'); }

    public static function players(string $world): self { return new self($world, 'players', 'players_history', 'pid'); }

    public static function villages(string $world): self { return new self($world, 'villages', 'villages_history', 'vid'); }

    /**
     * History rows that held at some point between two times: the row carried
     * into $start, if the entity was still in the dumps then, and the rows after
     * it, up to $end
     * @param Builder $ids Query selecting the ids of the entities
     * @param array $columns History columns to select, besides the entity's id
     * @param string $start Start of the period
     * @param string $end End of the period
     * @return Builder Query on the rows, with the entity's id in the history table's id column (e.g. tid)
     */
    public function held(Builder $ids, array $columns, string $start, string $end): Builder
    {
        // SELECT * FROM (
        //     (SELECT pid, <columns> FROM players_history
        //      WHERE players_history.wid = :world AND pid IN (<ids>)
        //        AND timestamp > :start AND timestamp <= :end)
        //     UNION ALL
        //     (SELECT h.pid, h.<columns>
        //      FROM (<ids>) AS ids
        //      JOIN players AS e ON e.id = ids.id AND e.wid = :world
        //      JOIN LATERAL (<at(e.id)> AND players_history.timestamp <= :start) AS h ON true
        //      -- e.timestamp is the last dump the player was in
        //      WHERE e.timestamp >= :start)
        // ) AS held
        $columns = array_merge([$this->key], $columns);
        $changes = $this->db()->table($this->table)->select($columns)
            ->where("{$this->table}.wid", $this->world)
            ->whereIn($this->key, $ids)
            ->where('timestamp', '>', $start)
            ->where('timestamp', '<=', $end);
        $carried = $this->db()->query()->fromSub($ids, 'ids')
            ->join("{$this->entities} AS e", fn ($join) => $join->on('e.id', '=', 'ids.id')->where('e.wid', '=', $this->world))
            ->where('e.timestamp', '>=', $start)
            ->joinLateral($this->at('e.id', $columns)->where('timestamp', '<=', $start), 'h')
            ->select(array_map(fn ($column) => "h.$column", $columns));

        return $this->db()->query()->fromSub($changes->unionAll($carried), 'held');
    }

    /**
     * Entities' values at every hour after $after until the last dump each was in
     * @param Builder|array $ids Ids of the entities
     * @param array $columns History columns to select, "column AS alias" included
     * @param CarbonInterface $after Start of the period, not included
     * @return Builder Query on the values, with the hour as "timestamp"
     */
    public function hourly(Builder|array $ids, array $columns, CarbonInterface $after): Builder
    {
        // series() from the next full hour after $after, an hour apart:
        // generate_series(:start::timestamp, e.timestamp, interval '1 hour')
        $start = $after->copy()->startOfHour()->addHour()->format('Y-m-d H:i:s');
        return $this->series($ids, $columns, '?::timestamp', [$start], "interval '1 hour'");
    }

    /**
     * Entity's values at the last midnights up to the last dump it was in
     * @param int $id Entity's id
     * @param array $columns History columns to select
     * @param int $days Number of midnights
     * @return Collection Values in chronological order, with the midnight as "timestamp"
     */
    public function daily(int $id, array $columns, int $days): Collection
    {
        // series() over the last $days midnights up to the last dump's, a day apart:
        // generate_series(date_trunc('day', e.timestamp) - (:days - 1) * interval '1 day',
        //                 date_trunc('day', e.timestamp), interval '1 day')
        // ... ORDER BY timestamp
        $last = "date_trunc('day', e.timestamp)";
        return $this->series([$id], $columns, "$last - ? * interval '1 day'", [$days - 1], "interval '1 day'", $last)
            ->orderBy('timestamp')
            ->get();
    }

    /**
     * Values carried forward to the times of a series, which ends at the last
     * dump each entity was in. Times before an entity's first row are left out.
     * @param string $start SQL for the first time, with $bindings
     * @param string $step SQL for the interval between times
     * @param string $end SQL for the last time
     */
    private function series(Builder|array $ids, array $columns, string $start, array $bindings, string $step, string $end = 'e.timestamp'): Builder
    {
        // SELECT h.<columns>, times.time AS timestamp
        // FROM players AS e
        // JOIN LATERAL (SELECT time FROM generate_series(<start>, <end>, <step>) AS time) AS times ON true
        // JOIN LATERAL (<at(e.id)> AND players_history.timestamp <= times.time) AS h ON true
        // WHERE e.wid = :world AND e.id IN (<ids>)
        $times = $this->db()->query()->select('time')
            ->fromRaw("generate_series($start, $end, $step) AS time", $bindings);

        return $this->db()->table("{$this->entities} AS e")
            ->joinLateral($times, 'times')
            ->joinLateral($this->at('e.id', $columns)->whereColumn("{$this->table}.timestamp", '<=', 'times.time'), 'h')
            ->select(array_merge(array_map(fn ($column) => 'h.' . preg_replace('/^.* as /i', '', $column), $columns), ['times.time AS timestamp']))
            ->where('e.wid', $this->world)
            ->whereIn('e.id', $ids);
    }

    /**
     * Query on an entity's last history row, to be limited to a time
     * @param string $id Column with the entity's id
     * @param array $columns History columns to select
     */
    private function at(string $id, array $columns): Builder
    {
        // SELECT <columns> FROM players_history
        // WHERE players_history.wid = :world AND players_history.pid = <id>
        //   <the caller's time limit>
        // ORDER BY players_history.timestamp DESC LIMIT 1
        return $this->db()->table($this->table)->select($columns)
            ->where("{$this->table}.wid", $this->world)
            ->whereColumn("{$this->table}.{$this->key}", $id)
            ->orderByDesc("{$this->table}.timestamp")
            ->limit(1);
    }

    private function db() { return DB::connection(); }
}
