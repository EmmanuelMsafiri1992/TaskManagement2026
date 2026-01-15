<?php

namespace Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laranext\Span\Span;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerResources();

        // Temp solution, need to refactor.
        $optionsTable = Cache::rememberForever('is-database-exists', function () {
            try {
                if (Schema::hasTable('options')) {
                    return true;
                }
            } catch (\Exception $e) {
                //
            }
        });

        if ($optionsTable) {
            app()->setLocale(option('app_locale'));
        }
    }

    /**
     * Register the package resources such as routes, templates, etc.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->app->extend('view', function ($view, $app) {
            $view->getFinder()->setPaths([
                base_path('packages/admin/resources/views'),
                resource_path('views'),
            ]);

            return $view;
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'middleware' => ['web', 'auth'],
            'prefix' => Span::prefix().'/api',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });

        Route::group([
            'middleware' => ['web', 'auth'],
            'prefix' => Span::prefix(),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }
}
