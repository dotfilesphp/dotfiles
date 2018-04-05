<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Config;


use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Config implements \ArrayAccess
{
    /**
     * @var DefinitionInterface[]
     */
    private $definitions = array();

    private $configs = array();

    private $defaults = array();

    private $configDirs = array();

    public function offsetExists($offset)
    {
        return isset($this->configs[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->configs[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->configs[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->configs[$offset]);
    }


    public function addDefinition(DefinitionInterface $config)
    {
        $builder = $config->getConfigTreeBuilder();
        $name = $builder->buildTree()->getName();
        $this->definitions[$name] = $config;
        $this->defaults[$name] = [];
    }


    public function addConfigDir($directory)
    {
        if(!is_dir($directory)){
            throw new \InvalidArgumentException("Directory ${directory} not exists");
        }
        if(!in_array($directory,$this->configDirs)){
            $this->configDirs[] = $directory;
        }
    }

    public function loadConfiguration()
    {
        $configs = $this->processFiles();
        $processor = new Processor();
        $generated = [];
        foreach($configs as $rootKey => $values){
            if(!isset($this->definitions[$rootKey])){
                continue;
            }
            $config = $this->definitions[$rootKey];
            $temp[$rootKey] = $values;
            $processed = $processor->processConfiguration($config,$temp);
            if(!isset($generated[$rootKey])){
                $generated[$rootKey] = [];
            }
            $generated[$rootKey] = array_merge_recursive($generated[$rootKey],$processed);
        }

        $this->configs = $generated;
    }

    public function get($name=null)
    {
        if(is_null($name)){
            return $this->configs;
        }
        $exp = explode('.',$name);
    }

    private function processFiles()
    {
        $configs = $this->defaults;
        if(!count($this->configDirs) > 0){
            return $configs;
        }

        $finder = Finder::create()
            ->name('*.yaml')
            ->name('*.yml')
        ;
        foreach($this->configDirs as $dir){
            $finder->in($dir);
        }
        $configs = array();
        foreach($finder->files() as $file){
            $parsed = Yaml::parseFile($file);
            if(is_array($parsed)){
                $configs = array_merge_recursive($configs,$parsed);
            }
        }
        return $configs;
    }
}
