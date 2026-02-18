<?php

namespace App\Support;

use App\Models\Group;

class CurrentGroup
{
    private ?Group $group = null;

    public function set(?Group $group): void
    {
        $this->group = $group;
    }

    public function get(): ?Group
    {
        return $this->group;
    }
}