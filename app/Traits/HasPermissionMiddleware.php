<?php

namespace App\Traits;

trait HasPermissionMiddleware
{
    public function applyPermissionMiddleware(string $prefix)
    {
        $this->middleware("permission:$prefix index")->only('index');
        $this->middleware("permission:$prefix create")->only(['create', 'store']);
        $this->middleware("permission:$prefix edit")->only(['edit', 'update']);
        $this->middleware("permission:$prefix delete")->only('destroy');
        $this->middleware("permission:$prefix access")->only(['manageAccess', 'updateAccess']);
    }
}