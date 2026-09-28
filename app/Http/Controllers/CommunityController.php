<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMembership;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($validated['q'] ?? '');

        $groups = Group::query()
            ->where('status', Group::STATUS_ACTIVE)
            ->where('visibility', Group::VISIBILITY_PUBLIC)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'memberships as approved_members_count' => fn ($query) => $query->where(
                    'status',
                    GroupMembership::STATUS_APPROVED
                ),
            ])
            ->with([
                'memberships' => fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->select(['id', 'group_id', 'user_id', 'status']),
            ])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('communities.index', [
            'groups' => $groups,
            'search' => $search,
        ]);
    }
}
