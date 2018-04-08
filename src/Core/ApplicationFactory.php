<?php

declare(strict_types=1);

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core;

use Composer\Autoload\ClassLoader;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\Definition;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

class ApplicationFactory
{
    /**
     * @var PluginInterface[]
     */
    private $plugins = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return $this
     */
    public function boot():self
    {
        $this->addAutoload();
        $this->loadPlugins();
        $this->compileContainer();

        return $this;
    }

    public function hasPlugin(string $name):bool
    {
        return array_key_exists($name,$this->plugins);
    }

    private function addAutoload(): void
    {
        $baseDir = Toolkit::getBaseDir();
        $autoloadFile = $baseDir.'/vendor/autoload.php';

        // ignore if files is already loaded in phar file
        if (
            is_file($autoloadFile) &&
            (false === strpos($autoloadFile,'phar:///'))
        ) {

            include_once $autoloadFile;
        }

    }

    private function compileContainer(): void
    {
        $config = new Config();
        $phar = \Phar::running(false);
        if (is_file($phar) && is_dir($dir = dirname($phar).'/config')) {
            $config->addConfigDir($dir);
        }
        $config->addDefinition(new Definition());
        foreach ($this->plugins as $plugin) {
            $plugin->setupConfiguration($config);
        }
        $config->loadConfiguration();

        $builder = new Builder();
        $builder->getContainerBuilder()->getParameterBag()->add($config->getAll(true));
        foreach ($this->plugins as $plugin) {
            $plugin->configureContainer($builder->getContainerBuilder(), $config);
        }

        $builder->compile();
        $this->container = $builder->getContainer();
        $this->container->set(Config::class, $config);
    }

    private function loadPlugins(): void
    {
        $finder = Finder::create();
        $finder
            ->in(__DIR__.'/../Plugins')
            ->name('*Plugin.php')
        ;
        $dirs = $this->loadDirectoryFromAutoloader();
        $finder->in($dirs);
        foreach ($finder->files() as $file) {
            $namespace = 'Dotfiles\\Plugins\\'.str_replace('Plugin.php', '', $file->getFileName());
            $className = $namespace.'\\'.str_replace('.php', '', $file->getFileName());
            if (class_exists($className)) {
                /* @var \Dotfiles\Core\PluginInterface $plugin */
                $plugin = new $className();
                if(!$this->hasPlugin($plugin->getName())){
                    $this->plugins[$plugin->getName()] = $plugin;
                }
            }
        }
    }

    private function loadDirectoryFromAutoloader()
    {
        $spl = spl_autoload_functions();

        $dirs = array();
        foreach($spl as $item){
            $object = $item[0];
            if(!$object instanceof ClassLoader){
                continue;
            }
            $temp = array_merge($object->getPrefixes(),$object->getPrefixesPsr4());
            foreach($temp as $name => $dir){
                if(false === strpos($name,'Dotfiles')){
                    continue;
                }
                foreach($dir as $num => $path){
                    $path = realpath($path);
                    if($path && is_dir($path) && !in_array($path,$dirs)){
                        $dirs[] = $path;
                    }
                }
            }
        }

        return $dirs;
    }
}
