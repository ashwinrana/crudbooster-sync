<?php

namespace Ashwinrana\CrudboosterSync;

use Illuminate\Support\ServiceProvider;

class CrudboosterSyncServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Load custom routes to the laravel.
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'crudboostersync');
        $this->publishes([
            __DIR__ . '/public' => public_path('vendor/crudboostersync/data'),
        ], 'public');
    }

    public function register()
    {

    }

}
