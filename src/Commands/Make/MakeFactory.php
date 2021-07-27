<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Str;

class MakeFactory extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:factory {name} {model} {--module=} {--ignoremodel}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a factory in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'database' . DIRECTORY_SEPARATOR . 'factories';
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
        if (!Str::endsWith($this->name, 'Factory')) {
            $this->name .= 'Factory';
        }
        
        if (!$this->getModule()) {
            return 0;
        }
        //Does it Exist??
        if ($this->alreadyExists($this->file_path)) {
            return 0;
        }
        
        //Get our stub
        $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'factory.stub'));
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\database\\factories' . $this->extra_namespace;
        
        $model = $this->argument('model');
        if (!Str::startsWith($model, 'App')) {
            $model = 'App\\Modules\\' . $this->module . '\\Models\\' . $model;
            //We have the namespace.
        }
        $model_parts = explode('\\', $model);
        
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ factory }}', $this->name, $stub)
            ->replacePhrase('{{ namespacedModel }}', $model, $stub)
            ->replacePhrase('{{ model }}', $model_parts[count($model_parts) - 1], $stub)
            ->place($this->file_path, $stub);
    
        if (!class_exists($model) && !$this->option('ignoremodel')) {
            $this->call('mod:model', [
                'name' => $model_parts[count($model_parts) - 1],
                '--module' => $this->module,
                '--migration' => true,
                '--factorypath' => $namespace . '\\' . $this->name
            ]);
        }
        
        $this->info('Factory has been generated successfully.');
        return 0;
    }
}
