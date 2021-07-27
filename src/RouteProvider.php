<?php

namespace Cmdobueno\Mod;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteProvider extends ServiceProvider
{
    private array $modules = [];
    
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Grab our modules and map their routes properly:
         */
        $skipped = [
            '.',
            '..',
        ];
        /**
         * First we will populate a list of our modules... all of them.
         */
        $modules = array_filter(scandir(base_path('app/Modules')), function ($name) use ($skipped) {
            return !in_array($name, $skipped);
        });
        $this->modules = array_values($modules);
        $this->registerModuleRoutes();
    }
    
    private function registerModuleRoutes()
    {
        $api_routes = [];
        $web_routes = [];
        foreach ($this->modules as $module) {
            $api_route = app_path('Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php');
            $web_route = app_path('Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php');
    
            if (is_file($api_route)) {
                $api_routes[] = $api_route;
            }
            if (is_file($web_route)) {
                $web_routes[] = $web_route;
            }
        }
        
        $this->routes(function () use ($api_routes, $web_routes) {
            foreach ($api_routes as $route) {
                Route::prefix('api')
                    ->middleware('api')
                    ->namespace($this->namespace)
                    ->group($route);
            }
            
            foreach ($web_routes as $route) {
                Route::middleware('web')
                    ->namespace($this->namespace)
                    ->group($route);
            }
        });
    }
    
    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
