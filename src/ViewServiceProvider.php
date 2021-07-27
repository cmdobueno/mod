<?php

namespace Cmdobueno\Mod;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register view locations for modules
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
        
        foreach ($modules as $module) {
            $path = base_path('app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views');
            if (is_dir($path)) {
                $this->loadViewsFrom($path, $module);
            }
        }
    }
}
