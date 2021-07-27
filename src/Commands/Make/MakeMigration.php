<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeMigration extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:migration {name} {--module=} {--create=} {--table=}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a migration in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'database' . DIRECTORY_SEPARATOR . 'migrations';
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
        //Get and validate Module
        if (!$this->getModule()) {
            return 0;
        }
        //Does it Exist??
        if ($this->alreadyExists($this->file_path)) {
            return 0;
        }
        
        $path = 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . $this->module . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
        if ($create = $this->option('create')) {
            $this->call('make:migration', [
                'name' => $this->name,
                '--create' => $create,
                '--path' => $path
            ]);
        } else {
            $this->call('make:migration', [
                'name' => $this->name,
                '--table' => $this->option('table'),
                '--path' => $path
            ]);
        }
        return 0;
    }
}
