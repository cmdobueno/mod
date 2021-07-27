<?php

namespace Cmdobueno\Mod\Commands\Make;

use File;
use Illuminate\Console\Command;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mod:module {name}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create a package folder, and update our namespace data.';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $package_path = app_path('Modules' . DIRECTORY_SEPARATOR . $name);
        if (File::exists($package_path)) {
            $this->error('This package already exists');
            return 1;
        } else {
            File::makeDirectory($package_path);
        }
        return 0;
    }
}
