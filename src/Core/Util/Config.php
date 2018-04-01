<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Util;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Class Config
 */
class Config implements \ArrayAccess
{
    private $config = [
        'dotfiles' => [],
        'bash_exports' => [],
        'bash_aliases' => [],
        'git' => []
    ];

    final public function __construct()
    {
        $this->loadDefaultConfig();
    }

    final static public function create()
    {
        static $instance;
        if(!is_object($instance)){
            $instance = new self();
        }
        return $instance;
    }

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset,$value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function getCacheDir()
    {
        $cacheDir = getcwd().'/var/cache';
        if(!is_dir($cacheDir)){
            mkdir($cacheDir,0775,true);
        }
        return $cacheDir;
    }

    public function getTempDir($suffix = null)
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'dotfiles';
        if($suffix){
            $dir = $dir.DIRECTORY_SEPARATOR.$suffix;
        }
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
        return $dir;
    }

    public function getSection($section,$default = array())
    {
        if(!isset($this->config[$section])){
            throw new RuntimeException("Invalid config section name ${section}");
        }
        return isset($this->config[$section]) ? $this->config[$section]:$default;
    }

    public function getDotfiles($key)
    {
        return $this->get('dotfiles.',$key);
    }

    public function get($name=null)
    {
        $exp        = explode('.',$name);
        $section    = $exp[0];
        $key        = $exp[1];
        if(!isset($this->config[$section][$key])){
            throw new RuntimeException("Can't find config with this ${name} name.");
        }
        return $this->config[$section][$key];
    }

    public function loadFromFiles($files)
    {
        if(!is_array($files)){
            $files = [$files];
        }

        $config = [];
        foreach($files as $file){
            if(!is_file($file)){
                continue;
            }
            $parsed = parse_ini_file($file,true,INI_SCANNER_TYPED);
            $config = array_merge_recursive($config, $parsed);
        }
        $this->config = array_merge_recursive($this->config,$this->normalizeConfig($config));
    }

    private function loadDefaultConfig()
    {
        $files = [
            __DIR__ . '/../Resources/default.ini',
            realpath(getenv('HOME').'/.dotfiles.ini'),
        ];
        $finder = Finder::create()
            ->in(__DIR__ . '/../../Plugins/*/Resources')
            ->name('default.ini')
            ->files()
            ;
        foreach($finder->files() as $file){
            $files[] = $file;
        }
        $this->loadFromFiles($files);
    }

    private function normalizeConfig($config)
    {
        foreach($config as $section=>$contents){
            foreach($contents as $key=>$value){
                $config[$section][$key] = $this->normalizeValue($value);
            }
        }
        return $config;
    }

    private function normalizeValue($value)
    {
        // replace environment variables
        $pattern = '/%%([A-Z]*)%%/i';
        preg_match($pattern,$value,$match);
        if(isset($match[1])){
            $value = str_replace($match[0],getenv($match[1]),$value);
        }
        return $value;
    }
}
