<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            $app->instance('routes', $routes);
            $url = new \App\Support\WorkspaceUrlGenerator($routes, $app->rebinding('request', function ($app, $request) {
                $app['url']->setRequest($request);
            }), $app['config']['app.asset_url']);
            $url->setSessionResolver(fn () => $app['session']);
            $url->setKeyResolver(fn () => [$app['config']['app.key'], ...($app['config']['app.previous_keys'] ?? [])]);
            return $url;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Auth::extend('workspace-session', function ($app, $name, $config) {
            return $app['auth']->createSessionDriver($name.'_'.$app['request']->attributes->get('workspace'), $config);
        });
    }
}
