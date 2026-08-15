<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Scale;
use App\Models\Thermometer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');

        Relation::morphMap([
            'scale' => Scale::class,
            'thermometer' => Thermometer::class,
        ]);
    }
}