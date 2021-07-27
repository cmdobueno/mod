<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Str;

class MakeModel extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:model {name} {--module=} {--migration} {--pivot} {--factory} {--factorypath}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a model in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'Models';
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
        $factoryPath = $this->option('factorypath');
        if (!$this->getModule()) {
            return 0;
        }
        //Does it Exist??
        if ($this->alreadyExists($this->file_path)) {
            return 0;
        }
        
        //Get our stub
        if (!$this->option('pivot')) {
            if ($this->option('factory')) {
                $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.factory.stub'));
            } else {
                $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.stub'));
            }
        } else {
            $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'model.pivot.stub'));
        }
        
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\Models' . $this->extra_namespace;
        if ($this->option('factory')) {
            $factoryPath = 'App\\Modules\\' . $this->module . '\\database\\factories\\' . $this->extra_namespace . $this->name . 'Factory';
        }
        if (is_null($factoryPath)) {
            $factory = '';
        } else {
            $factory_parts = explode('\\', $factoryPath);
            $factory = $factory_parts[count($factory_parts) - 1];
        }
        
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ class }}', $this->name, $stub)
            ->replacePhrase('{{ class }}', $this->name, $stub)
            ->replacePhrase('{{ factoryNamespace }}', $factoryPath, $stub)
            ->replacePhrase('{{ factory }}', $factory, $stub)
            ->place($this->file_path, $stub);
        
        $this->info('Model has been generated successfully.');
        
        if ($this->option('migration')) {
            $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));
            if ($this->option('pivot')) {
                $table = Str::singular($table);
            }
            
            $this->call('mod:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
                '--module' => $this->module
            ]);
        }
        
        
        if ($this->option('factory')) {
            $this->call('mod:factory', [
                'name' => $this->extra_path . $this->name . 'Factory',
                'model' => $namespace . '\\' . $this->name,
                '--module' => $this->module,
                '--ignoremodel' => true
            ]);
        }
        
        return 0;
    }
}
