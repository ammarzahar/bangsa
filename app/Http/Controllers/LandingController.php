<?php

namespace App\Http\Controllers;

use App\Models\Group;

class LandingController extends Controller
{
    public function __invoke()
    {
        $groups = Group::query()
            ->where('status', Group::STATUS_ACTIVE)
            ->when(!auth()->user()?->isAdmin(), fn ($query) => $query->where('visibility', Group::VISIBILITY_PUBLIC))
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return view('landing', [
            'groups' => $groups,
        ]);
    }
}
