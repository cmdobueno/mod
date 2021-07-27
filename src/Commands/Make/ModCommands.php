<?php

namespace Cmdobueno\Mod\Commands\Make;

use File;

trait ModCommands
{
    private function getNameArg()
    {
        $name = $this->argument('name');
        $name_parts = explode('/', $name);
        if (count($name_parts) > 1) {
            $this->name = $name_parts[count($name_parts) - 1];
            unset($name_parts[count($name_parts) - 1]);;
            $this->extra_path = implode(DIRECTORY_SEPARATOR, $name_parts) . DIRECTORY_SEPARATOR;
            $this->extra_namespace = '\\' . implode('\\', $name_parts);
        } else {
            $this->name = $name_parts[0];
        }
        
    }
    
    
    /**
     * @return bool
     */
    private function getModule(): bool
    {
        if (!$this->module = $this->option('module')) {
            $skipped = [
                '.',
                '..',
            ];
            $modules = array_values(array_filter(scandir($this->modules_path), function ($name) use ($skipped) {
                return !in_array($name, $skipped);
            }));
            $this->module = $this->choice('Select Module:', $modules);
        }
        
        $this->module_path = $this->modules_path . DIRECTORY_SEPARATOR . $this->module;
        $this->file_path = $this->module_path . DIRECTORY_SEPARATOR . $this->pathway . DIRECTORY_SEPARATOR . $this->extra_path .$this->name . '.php';
        
        //Check if exists
        if (!File::exists($this->module_path)) {
            $this->error('Module unable to be located.');
            return false;
        }
        
        return true;
    }
    
    /**
     * @param $path
     * @return bool
     */
    private function alreadyExists($path): bool
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
        if ($this->files->exists($path)) {
            $this->error('File already exists');
            return true;
        }
        return false;
    }
    
    /**
     * @param $stub
     * @param $namespace
     * @return $this
     */
    private function replaceNamespace(&$stub, $namespace): self
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'],
        ];
        $this->replacePhrase($searches, $namespace, $stub);
        return $this;
    }
    
    /**
     * @param $phrases
     * @param $value
     * @param $stub
     * @return $this
     */
    private function replacePhrase($phrases, $value, &$stub): self
    {
        if (!is_array($phrases)) {
            $phrases = [$phrases];
        }
        
        foreach ($phrases as $phrase) {
            $stub = str_replace(
                $phrase,
                $value,
                $stub
            );
        }
        return $this;
    }
    
    /**
     * @param $path
     * @param $stub
     * @return ModCommands|MakeCommand
     */
    private function place($path, $stub): self
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
        $this->files->put($path, $stub);
        
        return $this;
    }
}
