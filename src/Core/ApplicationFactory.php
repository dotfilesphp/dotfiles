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
use Dotfiles\Core\Command\CompileCommand;
use Dotfiles\Core\Command\SubsplitCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Config\Definition;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

class ApplicationFactory
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var PluginInterface[]
     */
    private $plugins = array();

    /**
     * @return $this
     */
    public function boot(): self
    {
        $this->builder = new Builder();
        $this->config = new Config();

        $this->addAutoload();
        $this->loadPlugins();
        $this->compileContainer();

        return $this;
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPlugin(string $name): bool
    {
        return array_key_exists($name, $this->plugins);
    }

    private function addAutoload(): void
    {
        $baseDir = Toolkit::getBaseDir();
        $autoloadFile = $baseDir.'/vendor/autoload.php';

        // ignore if files is already loaded in phar file
        if (
            is_file($autoloadFile) &&
            (false === strpos($autoloadFile, 'phar:///'))
        ) {
            include_once $autoloadFile;
        }
    }

    private function compileContainer(): void
    {
        // begin loading configuration
        $config = $this->config;
        $phar = \Phar::running(false);
        if (is_file($phar) && is_dir($dir = dirname($phar).'/config')) {
            $config->addConfigDir($dir);
        }
        $config->addDefinition(new Definition());
        $config->loadConfiguration();

        // start build container
        $builder = $this->builder;
        $builder->getContainerBuilder()->getParameterBag()->add($config->getAll(true));

        /* @var Plugin $plugin */
        foreach ($this->plugins as $plugin) {
            $plugin->load($config->getAll(true), $builder->getContainerBuilder());
        }

        $builder->compile();
        $container = $builder->getContainer();
        $container->set(Config::class, $config);

        if ('dev' === getenv('DOTFILES_ENV')) {
            $app = $container->get('dotfiles.app');
            $app->add(new SubsplitCommand());
            $app->add(new CompileCommand());
        }
        $this->container = $container;
    }

    /**
     * Load available plugins directory.
     *
     * @return array
     */
    private function loadDirectoryFromAutoloader()
    {
        $spl = spl_autoload_functions();

        $dirs = array();
        foreach ($spl as $item) {
            $object = $item[0];
            if (!$object instanceof ClassLoader) {
                continue;
            }
            $temp = array_merge($object->getPrefixes(), $object->getPrefixesPsr4());
            foreach ($temp as $name => $dir) {
                if (false === strpos($name, 'Dotfiles')) {
                    continue;
                }
                foreach ($dir as $num => $path) {
                    $path = realpath($path);
                    if ($path && is_dir($path) && !in_array($path, $dirs)) {
                        $dirs[] = $path;
                    }
                }
            }
        }

        return $dirs;
    }

    private function loadPlugins(): void
    {
        $finder = Finder::create();
        $finder
            ->name('*Plugin.php')
        ;
        if (is_dir($dir = __DIR__.'/../Plugins')) {
            $finder->in(__DIR__.'/../Plugins');
        }
        $dirs = $this->loadDirectoryFromAutoloader();
        $finder->in($dirs);
        foreach ($finder->files() as $file) {
            $namespace = 'Dotfiles\\Plugins\\'.str_replace('Plugin.php', '', $file->getFileName());
            $className = $namespace.'\\'.str_replace('.php', '', $file->getFileName());
            if (class_exists($className)) {
                /* @var \Dotfiles\Core\Plugin $plugin */
                $plugin = new $className();
                $this->registerPlugin($plugin);
            }
        }
    }

    /**
     * Register plugin.
     *
     * @param Plugin $plugin
     */
    private function registerPlugin(Plugin $plugin): void
    {
        if ($this->hasPlugin($plugin->getName())) {
            return;
        }

        $this->plugins[$plugin->getName()] = $plugin;
        $config = $plugin->getConfiguration(array(), $this->builder->getContainerBuilder());
        if ($config instanceof ConfigurationInterface) {
            $this->config->addDefinition($config);
        }
    }
}
