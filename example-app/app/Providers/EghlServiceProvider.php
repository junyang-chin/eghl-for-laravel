<?php

namespace App\Providers;

use App\Modules\Eghl\Eghl;
use Illuminate\Support\ServiceProvider;

class EghlServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('eghl', function () {
            return new Eghl();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
