<?php

namespace App\Http\Middleware;

use App\Models\World;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Registers a database connection named after the requested world, so
 * services can use Model::on($world). Every world database shares the
 * credentials of the tw2-stats connection; its url is dropped, since it
 * would override the database name.
 */
class UseWorldConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $world = $request->route('world');
        if (is_string($world) && in_array($world, $this->worlds(), true) && !config()->has("database.connections.$world")) {
            config(["database.connections.$world" => array_merge(config('database.connections.tw2-stats'), ['url' => null, 'database' => $world])]);
        }
        return $next($request);
    }

    /**
     * Ids of all known worlds, cached for a few minutes
     * @return string[]
     */
    private function worlds(): array
    {
        return Cache::remember('worlds.ids', 300, function () {
            return World::on('tw2-stats')->pluck('wid')->all();
        });
    }
}
