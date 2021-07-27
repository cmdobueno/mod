<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;

class MakeComponent extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:component {name} {--module=} {{--inline}}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a component in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'View' . DIRECTORY_SEPARATOR . 'Components';
    private $files;
    protected $name;
    private $vendor;
    private $modules_path;
    private $module_path;
    private $module;
    private $file_path;
    private $extra_path = '';
    private $extra_namespace = '';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->vendor = 'vendor' . DIRECTORY_SEPARATOR . 'cmdobueno' . DIRECTORY_SEPARATOR . 'mod' . DIRECTORY_SEPARATOR;
        $this->modules_path = app_path('Modules');
        
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Get Our Arguments
        $this->getNameArg();
//        $this->name = $this->argument('name');
        
        if (!$this->getModule()) {
            return 0;
        }
        //Does it Exist??
        if ($this->alreadyExists($this->file_path)) {
            return 0;
        }
        
        $class_stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'component.class.stub'));
        
        if ($this->option('inline')) {
            $view = "<<<'blade'\n<div>\n    <!-- " . Inspiring::quote() . " -->\n</div>\nblade";
        } else {
            $view = "view('$this->module::components.{$this->getView()}')";
            
            //Create Our View
            $view_path = $this->module_path .
                DIRECTORY_SEPARATOR . 'resources' .
                DIRECTORY_SEPARATOR . 'views'  .
                DIRECTORY_SEPARATOR . 'components' .
                DIRECTORY_SEPARATOR .
                $this->extra_path . \Str::snake($this->name) . '.blade.php';
            $this->place($view_path, '<div>
                <!-- '.Inspiring::quote().' -->
            </div>');
        }
        
        $namespace = 'App\\Modules\\' . $this->module . '\\View\\Components' . $this->extra_namespace;
        $this->replaceNamespace($class_stub, $namespace)
            ->replacePhrase('{{ class }}', $this->name, $class_stub)
            ->replacePhrase('{{ view }}', $view, $class_stub)
            ->place($this->file_path, $class_stub);
        
        $this->info('Component has been generated successfully.');
        return 0;
    }
    
    /**
     * Get the view name relative to the components directory.
     *
     * @return string view
     */
    protected function getView()
    {
        $name = str_replace('\\', '/', $this->argument('name'));
        
        return collect(explode('/', $name))
            ->map(function ($part) {
                return Str::kebab($part);
            })
            ->implode('.');
    }
}
