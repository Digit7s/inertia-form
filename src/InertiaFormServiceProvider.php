<?php

namespace Digit7s\InertiaForm;

use Digit7s\InertiaForm\Commands\MakeInertiaFormCommand;
use Illuminate\Support\ServiceProvider;

class InertiaFormServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeInertiaFormCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/InertiaForm'),
            ], 'inertia-form-components');
        }
    }

    public function register(): void
    {
        //
    }
}
