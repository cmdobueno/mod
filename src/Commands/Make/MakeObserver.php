<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeObserver extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:observer {name} {--module=} {--model=}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a observer in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'Observers';
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
        
        $model = $this->option('model');
        //Get our stub
        if( $model ){
            $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'observer.stub'));
        }else{
            $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'observer.plain.stub'));
        }
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\Observers' . $this->extra_namespace;
        $command = \Str::kebab(strtolower($this->module)) . ':' . \Str::kebab(strtolower($this->name));
        
        //Process Model
        $model_parts = [''];
        if( $model ){
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
    
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ class }}', $this->name, $stub)
            ->replacePhrase('{{ command }}', $command, $stub)
            ->replacePhrase('{{ namespacedModel }}', $model, $stub)
            ->replacePhrase('{{ model }}', $model_parts[count($model_parts) - 1], $stub)
            ->replacePhrase('{{model}}', $model_parts[count($model_parts) - 1], $stub)
            ->replacePhrase('{{ modelVariable }}', $modelVariable, $stub)
            ->replacePhrase('{{ modelPluralVariable }}', \Str::plural($modelVariable), $stub)
            ->place($this->file_path, $stub);
        
        $this->info('Observer has been generated successfully.');
        return 0;
    }
}
