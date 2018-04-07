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

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\Definition;
use Dotfiles\Core\DI\Builder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

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
        $builder->getContainerBuilder()->getParameterBag()->add($config->getFlattened());
        foreach ($this->plugins as $plugin) {
            $plugin->configureContainer($builder->getContainerBuilder(), $config);
        }

        $builder->compile();
        $this->container = $builder->getContainer();
        $this->container->set(Config::class, $config);
    }

    private function loadPlugins()
    {
        $finder = Finder::create();
        $finder
            ->in(__DIR__.'/../Plugins')
            ->name('*Plugin.php')
        ;

        foreach ($finder->files() as $file) {
            $namespace = 'Dotfiles\\Plugins\\'.str_replace('Plugin.php', '', $file->getFileName());
            $className = $namespace.'\\'.str_replace('.php', '', $file->getFileName());
            if (class_exists($className)) {
                $plugin = new $className();
                $this->plugins[$plugin->getName()] = $plugin;
            }
        }
    }
}
