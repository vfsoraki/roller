<?php

namespace VFSoraki\Roller;

use Illuminate\Support\ServiceProvider;

/**
 * Roller service provider
 */
class RollerServiceProvider extends ServiceProvider
{

    /**
     * Roller service boot method
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/roller.php' => config_path('roller.php'),
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/../config/roller.php', 'roller'
        );

        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('migrations'),
        ]);

    }

    /**
     * Roller service register method
     */
    public function register()
    {

    }

}
