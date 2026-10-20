<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use App\Support\FormBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Support/AuditableShim.php'))) {
            require_once app_path('Support/AuditableShim.php');
        }
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }

        // Register the Form builder as a singleton so the Form facade works
        $this->app->singleton('form.builder', function ($app) {
            return new FormBuilder();
        });

        // Register the "Form" alias so blade templates can use Form::open(), Form::text(), etc.
        AliasLoader::getInstance()->alias('Form', \App\Support\Form::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
    }
}
