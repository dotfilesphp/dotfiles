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
     * @var ConfigInterface[]
     */
    private $definitions = array();

    private $configs = array();

    /**
     * @return Config
     */
    static public function factory()
    {
        static $instance;
        if(!$instance instanceof self){
            $instance = new self();
        }
        return $instance;
    }

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


    public function addConfigDefinition(ConfigInterface $config)
    {
        $builder = $config->getConfigTreeBuilder();
        $name = $builder->buildTree()->getName();
        $this->definitions[$name] = $config;
    }

    public function loadConfiguration()
    {
        $files = Finder::create()
            ->name('*.yaml')
            ->name('*.yml')
            ->in(getcwd().'/config')
            ->files()
        ;
        $configs = array();
        foreach($files as $file){
            $parsed = Yaml::parseFile($file);
            if(is_array($parsed)){
                $configs = array_merge_recursive($configs,$parsed);
            }
        }

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

    public function get()
    {
        return $this->configs;
    }
}
