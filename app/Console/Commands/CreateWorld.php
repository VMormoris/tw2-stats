<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Adds a world to the worlds table and creates its partitions of the world
 * tables, with create_world_partitions() from database/initdb.d/schema.sql
 *
 * Connects with the world-admin connection, as the dumper's role by default,
 * which owns the tables, and asks for its password unless DB_ADMIN_PASSWORD
 * is set. Details of the world that aren't given as options are asked for.
 */
class CreateWorld extends Command
{
    protected $signature = 'world:create
        {wid : World\'s id in game, e.g. en120}
        {--name= : World\'s name in game}
        {--server= : World\'s server, e.g. en}
        {--url= : URL for the game connection, e.g. en.tribalwars2.com/game.php}
        {--win-condition= : Domination or VP}
        {--win-amount= : Percentage of villages for Domination, victory points for VP}
        {--moral= : Active or Inactive}
        {--relocation= : Active or Inactive}
        {--night-bonus= : Active or Inactive}
        {--night-start= : Time the night bonus starts, e.g. 00:00}
        {--night-end= : Time the night bonus ends, e.g. 08:00}
        {--time-offset= : Hours from GMT}
        {--start= : Date the world started, e.g. 2026-10-24}';

    protected $description = 'Add a world to the worlds table and create its partitions';

    public function handle(): int
    {
        $wid = $this->argument('wid');
        if (!preg_match('/^[a-z0-9]{1,5}$/', $wid)) {
            $this->error('The world id must be up to 5 lowercase letters and digits');
            return self::FAILURE;
        }

        $admin = config('database.connections.world-admin');
        $admin['password'] ??= $this->secret("Password of the {$admin['username']} database role");
        config(['database.connections.world-admin' => $admin]);
        $db = DB::connection('world-admin');

        if ($db->table('worlds')->where('wid', $wid)->exists()) {
            $this->error("World $wid is already in the worlds table");
            return self::FAILURE;
        }

        $world = $this->details($wid);

        // The worlds row first, since the partitions' rows reference it
        $db->transaction(function () use ($db, $world) {
            $db->table('worlds')->insert($world);
            $db->select('SELECT create_world_partitions(?)', [$world['wid']]);
        });
        Cache::forget('worlds.ids');

        $this->info("Created world $wid");
        return self::SUCCESS;
    }

    /**
     * Row of the worlds table, from the options or asked for
     * @param string $wid World's id in game
     * @return array
     */
    private function details(string $wid): array
    {
        $activity = ['Active', 'Inactive'];
        $condition = $this->option('win-condition') ?? $this->choice('Win condition', ['Domination', 'VP']);
        $server = $this->option('server') ?? $this->ask('Server', preg_replace('/\d+$/', '', $wid));
        $nightBonus = $this->option('night-bonus') ?? $this->choice('Night bonus', $activity);
        $night = fn (string $option, string $question) => $this->option($option) ?? ($nightBonus === 'Active' ? $this->ask($question, '00:00') : '00:00');

        return [
            'wid' => $wid,
            'name' => $this->option('name') ?? $this->ask('Name'),
            'server' => $server,
            'url' => $this->option('url') ?? $this->ask('URL for the game connection', "$server.tribalwars2.com/game.php"),
            'win_condition' => $condition,
            'win_ammount' => (int) ($this->option('win-amount') ?? $this->ask($condition === 'VP' ? 'Victory points to win' : 'Percentage of villages to win')),
            'tribes' => 0,
            'players' => 0,
            'villages' => 0,
            'finished' => false,
            'running' => true,
            'moral' => $this->option('moral') ?? $this->choice('Moral', $activity),
            'relocation' => $this->option('relocation') ?? $this->choice('Relocation', $activity),
            'night_bonus' => $nightBonus,
            'night_start' => $night('night-start', 'Night bonus starts at'),
            'night_end' => $night('night-end', 'Night bonus ends at'),
            'time_offset' => (int) ($this->option('time-offset') ?? $this->ask('Hours from GMT', '0')),
            'start' => $this->option('start') ?? $this->ask('Start date', now()->format('Y-m-d')),
        ];
    }
}
