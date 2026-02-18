<?php

namespace App\Http\Middleware;

use App\Models\Group;
use App\Support\CurrentGroup;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveGroupFromSlug
{
    public function __construct(private readonly CurrentGroup $currentGroup)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('group_slug');

        if ($slug) {
            $group = Group::query()->where('slug', $slug)->first();

            if (!$group) {
                abort(404, 'Group not found.');
            }

            $this->currentGroup->set($group);
            $request->attributes->set('current_group', $group);
            View::share('currentGroup', $group);
        }

        return $next($request);
    }
}