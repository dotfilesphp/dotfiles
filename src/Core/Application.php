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

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\DispatcherPass;
use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\DI\Container;
use Dotfiles\Core\DI\ListenerPass;
use Dotfiles\Core\DI\CommandPass;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    const RELEASE_DATE = '@release_date@';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        parent::__construct('dotfiles', static::VERSION);
        $this->loadPlugins();
        $this->compileContainer();
    }

    public function getLongVersion()
    {
        return implode(' ',[
            static::VERSION,
            static::BRANCH_ALIAS_VERSION,
            static::RELEASE_DATE
        ]);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     * @return Application
     */
    public function setContainer(Container $container): Application
    {
        $this->container = $container;
        return $this;
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

    private function compileContainer()
    {
        $builder = new ContainerBuilder();
        $builder->register(Dispatcher::class)
            ->setPublic(true)
        ;
        $builder->register(Config::class)
            ->setPublic(true)
            ;
        $builder->set(Application::class,$this);
        $builder->register(Application::class)
            ->setPublic(true)
            ->setSynthetic(true)
        ;
        #$builder->register('Dotfiles\\Core\\Command\\')
        #    ->resource(__DIR__.'/Command')
        #;

        $builder->addCompilerPass(new ListenerPass());
        $builder->addCompilerPass(new CommandPass());

        foreach($this->plugins as $plugin){
            $plugin->configureContainer($builder);
        }

        $builder->compile();

        $dumper = new PhpDumper($builder);
        $target = getcwd().'/var/cache/container.php';
        if(!is_dir($dir = dirname($target))){
            mkdir($dir,0755,true);
        }
        file_put_contents($target,$dumper->dump(['class'=>'CachedContainer']));

        require_once($target);
        $container = new \CachedContainer();
        $this->container = $container;

        $this->initListeners();
    }

    private function initListeners()
    {
        $container = $this->container;
    }
}
