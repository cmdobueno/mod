<?php

namespace Cmdobueno\Mod;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider as Provider;
use ReflectionClass;


class ServiceProvider extends Provider
{
    private array $modules = [];
    private array $providers = [];
    
    public function __construct($app)
    {
        parent::__construct($app);
        
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
    }
    
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mod.php', '');
        $this->registerCommands();
        
        //Register Module Register Providers
        foreach ($this->modules as $module) {
            $provider = 'App\\Modules\\' . $module . '\\Providers\\ModuleServiceProvider';
            if (class_exists($provider)) {
                $p = new $provider($this->app);
                if (method_exists($p, 'register')) {
                    $p->register();
                }
            }
        }
    }
    
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //Make sure we have our package's folder
        $path = app_path('Modules');
        if (!\File::exists($path)) {
            \File::makeDirectory($path);
        }
        
        $this->publishes([
            __DIR__ . '/../config/.php' => $this->app->configPath('.php'),
        ], 'config');
        
        foreach ($this->modules as $module) {
            $provider = 'App\\Modules\\' . $module . '\\Providers\\ModuleServiceProvider';
            if (class_exists($provider)) {
                $p = new $provider($this->app);
                if (method_exists($p, 'boot')) {
                    $p->boot();
                }
            }
        }
    }
    
    protected function registerCommands()
    {
        $this->registerCoreCommands();
    }
    
    /**
     * @throws \ReflectionException
     */
    protected function registerCoreCommands()
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'Commands';
        $namespace = 'Cmdobueno\\Mod\\';
        $remove = __DIR__ . DIRECTORY_SEPARATOR;
        foreach (scandir($path) as $name) {
            $directory_path = $path . DIRECTORY_SEPARATOR . $name;
            $this->processCommandDirectory($name, $directory_path, $namespace, $remove);
        }
    }
    
    /**
     * @param string $path_name
     * @param string $path
     * @param string $namespace
     * @param string $remove
     * @param array $skipped
     * @throws \ReflectionException
     */
    private function processCommandDirectory(
        string $path_name, string $path, string $namespace, string $remove, array $skipped = [
        '.',
        '..'
    ]
    ) {
        if (!in_array($path_name, $skipped)) {
            if (is_dir($path)) {
                foreach (scandir($path) as $name) {
                    $directory_path = $path . DIRECTORY_SEPARATOR . $name;
                    $this->processCommandDirectory($name, $directory_path, $namespace, $remove);
                }
            } else {
                $file = str_replace([$remove, '.php'], '', $path);
                $file = str_ireplace('/', '\\', $file);
                $namespaced = $namespace  . $file;
                if (!class_exists($namespaced) && !trait_exists($namespaced)) {
                    dd($namespaced, $path);
                }else {
                    if (is_subclass_of($namespaced, Command::class) && !(new ReflectionClass($namespaced))->isAbstract()) {
                        Artisan::starting(function ($artisan) use ($namespaced) {
                            try{
                                $artisan->resolve($namespaced);
                            }catch(\Exception $e){
                                dd($e->getMessage());
                            }
                        });
                    }
                }
            }
        }
    }
    
}
