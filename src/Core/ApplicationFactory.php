<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core;

use Dotfiles\Core\DI\Builder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Dotfiles\Core\Config\Config;

class ApplicationFactory
{
    /**
     * @var PluginInterface[]
     */
    private $plugins;

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

    public function createApplication()
    {
        $this->loadPlugins();
        $this->compileContainer();
    }

    private function compileContainer()
    {
        $builder = new Builder();

        foreach($this->plugins as $plugin){
            $plugin->configureContainer($builder->getContainerBuilder());
        }

        $builder->compile();
        $this->container = $builder->getContainer();
    }

    private function loadPlugins()
    {
        $finder = Finder::create();
        $finder
            ->in(__DIR__.'/../Plugins')
            ->name('*Plugin.php')
        ;

        foreach($finder->files() as $file)
        {
            $namespace = 'Dotfiles\\Plugins\\'.str_replace('Plugin.php','',$file->getFileName());
            $className = $namespace.'\\'.str_replace('.php','',$file->getFileName());
            if(class_exists($className)){
                $plugin = new $className();
                $this->addPlugin($plugin);
                $this->plugins[$plugin->getName()] = $plugin;
            }
        }
    }

    public function addPlugin(PluginInterface $plugin)
    {
        $plugin->setupConfiguration(Config::factory());
    }
}
