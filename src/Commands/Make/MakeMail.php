<?php

namespace Cmdobueno\Mod\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;

class MakeMail extends Command
{
    use ModCommands;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:mail {name} {--module=} {--markdown}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a mail in a specific package.';
    /**
     * @var Filesystem
     */
    private $pathway = 'Mail';
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
        
        if ($this->option('markdown')) {
            $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'markdown-mail.stub'));
            //Write Markdown Template
            $view_stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'markdown.stub'));
            $view_path = $this->module_path .
                DIRECTORY_SEPARATOR . 'resources' .
                DIRECTORY_SEPARATOR . 'views'  .
                DIRECTORY_SEPARATOR . 'mail' .
                DIRECTORY_SEPARATOR . 'markdown' .
                DIRECTORY_SEPARATOR .
                $this->extra_path . \Str::snake($this->name) . '.blade.php';
    
            $this->place($view_path, $view_stub);
            
            $view = $this->module . '::mail.markdown.' . $this->getView();
        } else {
            $stub = $this->files->get(base_path($this->vendor . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'mail.stub'));
            //Write blank mailable
            $view_path = $this->module_path .
                DIRECTORY_SEPARATOR . 'resources' .
                DIRECTORY_SEPARATOR . 'views'  .
                DIRECTORY_SEPARATOR . 'mail' .
                DIRECTORY_SEPARATOR .
                $this->extra_path . \Str::snake($this->name) . '.blade.php';
            $this->place($view_path, '<div>
                <!-- '.Inspiring::quote().' -->
            </div>');
            $view = $this->module . '::mail.' . $this->getView();
        }
        
        //Get variables for replacement
        $namespace = 'App\\Modules\\' . $this->module . '\\Mail' . $this->extra_namespace;
        
        //Replace and store stub
        $this->replaceNamespace($stub, $namespace)
            ->replacePhrase('{{ class }}', $this->name, $stub)
            ->replacePhrase('{{ view }}', $view, $stub)
            ->place($this->file_path, $stub);
        
        $this->info('Mail has been generated successfully.');
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
