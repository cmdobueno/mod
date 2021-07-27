<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class MakeController extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:controller {name} {--module=} {--api} {--force} {--invokable} {--model=} {--parent} {--resource}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a controller in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'Http' . DIRECTORY_SEPARATOR . 'Controllers';
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
        
        $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.plain.stub');
        
        if ($this->option('api')) {
            if ($this->option('model')) {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.model.stub');
            } elseif ($this->option('invokable')) {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.invokable.stub');
            } else {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.api.stub');
            }
        } else {
            if ($this->option('invokable')) {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.invokable.stub');
            } elseif ($this->option('resource')) {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.stub');
            } elseif ($this->option('model')) {
                $stub = base_path($this->vendor . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'controller.model.stub');
            }
        }
        
        //Get our stub
        $stub = $this->files->get($stub);
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\Http\\Controllers' . $this->extra_namespace;
        $model_parts = [''];
        if ($model = $this->option('model')) {
            if (!\Str::startsWith($model, 'App')) {
                $model = 'App\\Modules\\' . $this->module . '\\Models\\' . $model;
                //We have the namespace.
            }
            $model_parts = explode('\\', $model);
            
            if (@!class_exists($model)) {
                $this->call('mod:model', [
                    'name' => $model_parts[count($model_parts) - 1],
                    '--module' => $this->module,
                    '--migration' => true,
                    '--factory' => true
                ]);
            }
            
        }
        $modelVariable = \Str::camel( $model_parts[count($model_parts) - 1]);
        if( !\Str::endsWith('Controller',$this->name)){
            $controllerName = $this->name . 'Controller';
            $this->file_path = $this->module_path . DIRECTORY_SEPARATOR . $this->pathway . DIRECTORY_SEPARATOR . $this->extra_path . $controllerName . '.php';
        }else{
            $controllerName = $this->name;
        }
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ class }}', $controllerName, $stub)
            ->replacePhrase('{{ namespacedModel }}', $model, $stub)
            ->replacePhrase('{{ model }}', $model_parts[count($model_parts) - 1], $stub)
            ->replacePhrase('{{model}}', $model_parts[count($model_parts) - 1], $stub)
            ->replacePhrase('{{ modelVariable }}', $modelVariable, $stub)
            ->replacePhrase('{{ modelPluralVariable }}', \Str::plural($modelVariable), $stub)
            ->place($this->file_path, $stub);
        
        $this->info('Controller has been generated successfully.');
        return 0;
    }
    
    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
        ];
    }
}
