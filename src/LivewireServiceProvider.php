<?php

namespace Cmdobueno\Mod;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if (class_exists('\Livewire')) {
            $this->loadComponents();
        }
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
    
    protected function loadComponents()
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
        
        $files = new Filesystem();
        
        foreach ($modules as $module) {
            $livewire_path = app_path('Modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Livewire');
            
            if ($files->exists($livewire_path)) {
                $classes = collect($files->allFiles($livewire_path));
                $classes->map(function ($file) use ($livewire_path, $module) {
                    $localPath = str_ireplace('.php', '', \Str::after($file->getPathname(), $livewire_path . DIRECTORY_SEPARATOR));
                    $classString = 'App\\Modules\\' . $module . '\\Http\\Livewire';
                    foreach (explode(DIRECTORY_SEPARATOR, $localPath) as $part) {
                        $classString .= '\\' . $part;
                    }
                    $componentName = '';
                    foreach (explode(DIRECTORY_SEPARATOR, $localPath) as $part) {
                        if ($componentName !== '') {
                            $componentName .= '.';
                        }
                        $componentName .= strtolower(\Str::kebab($part));
                    }
                    $componentName = \Str::kebab($module) . '::' . $componentName;
                    $class = get_class(new $classString);
                    
                    Livewire::component($componentName, $class);
                });
            }
        }
    }
}
