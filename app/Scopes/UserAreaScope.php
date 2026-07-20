<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserAreaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin & Superadmin dapat melihat semua area
        if ($user->hasAnyRole(['admin', 'superadmin'])) {
            return;
        }

        // User lain hanya melihat area miliknya
        if (!empty($user->area_uuid)) {
            $builder->where('area_uuid', $user->area_uuid);
        }
    }
}