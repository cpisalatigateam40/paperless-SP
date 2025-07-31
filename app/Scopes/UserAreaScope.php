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
        // Pastikan user sudah login
        if (Auth::check()) {
            $user = Auth::user();

            // Bypass scope jika user punya role 'admin'
            if ($user->hasRole('admin')) {
                return;
            }

            // Jika user punya area_uuid, filter query
            if ($user->area_uuid) {
                $builder->where('area_uuid', $user->area_uuid);
            }
        }
    }
}