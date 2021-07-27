<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeMiddleware extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:middleware {name} {--module=}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a middleware in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'Http' . DIRECTORY_SEPARATOR . 'Middleware';
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
        
        //Get our stub
        $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'middleware.stub'));
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\Http\\Middleware' . $this->extra_namespace;
        $command = \Str::kebab(strtolower($this->module)) . ':' . \Str::kebab(strtolower($this->name));
        
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ class }}', $this->name, $stub)
            ->replacePhrase('{{ command }}', $command, $stub)
            ->place($this->file_path, $stub);
        
        $this->info('Middleware has been generated successfully.');
        return 0;
    }
}
