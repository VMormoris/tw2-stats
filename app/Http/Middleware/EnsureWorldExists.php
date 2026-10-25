<?php

namespace App\Http\Middleware;

use App\Models\World;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Responds with 404 when the requested world isn't in the worlds table. Every
 * world is in the tw2-stats database, so the services filter by its id.
 */
class EnsureWorldExists
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
        // The privacy page is served by the /{world} route (see WorldsController)
        if ($world !== null && $world !== 'privacy' && !in_array($world, $this->worlds(), true)) {
            abort(404);
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
