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
use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Config\Config;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    const RELEASE_DATE = '@release_date@';

    /**
     * @var Emitter
     */
    private $emitter;

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
        $this->config = new Config();
        $this->emitter = new Emitter();
        $this->emitter->setConfig($this->config);
        $this->container = new Container();

        $this->loadPlugins();
        $this->buildCommands();
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
     * @return Emitter
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * @param Emitter $emitter
     * @return Application
     */
    public function setEmitter(Emitter $emitter): Application
    {
        $this->emitter = $emitter;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return Application
     */
    public function setConfig(Config $config): Application
    {
        $this->config = $config;
        $this->getEmitter()->setConfig($config);
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
     * @param Container $container
     * @return Application
     */
    public function setContainer(Container $container): Application
    {
        $this->container = $container;
        return $this;
    }

    public function buildCommands()
    {
        $commands = array();
        $files = Finder::create()
          ->in(__DIR__ . '/Command')
          //->in(__DIR__.'/../Plugins/*/Command')
          ->name('*Command.php')
          ->files()
        ;
        foreach($files as $file){
            $relpath = realpath(__DIR__ . '/../Plugins').DIRECTORY_SEPARATOR;
            $path = str_replace($relpath,"",$file->getRealPath());
            $class = strtr($path,[
                '/' => '\\',
                '.php' => ''
            ]);
            $class = 'Dotfiles\\Plugins\\'.$class;
            if(!class_exists($class)){
                $class = 'Dotfiles\\Core\\Command\\'.str_replace('.php','',$file->getFileName());
            }
            if(class_exists($class)){
                $r = new \ReflectionClass($class);
                if($r->implementsInterface(CommandInterface::class)){
                    $command = new $class();
                    $commands[] = $command;
                }
            }
        }
        $this->addCommands($commands);
        $this->getDefinition()->addOption(
          new InputOption('force', '-f', InputOption::VALUE_NONE, 'Force command to be executed')
        );
        $this->getDefinition()->addOption(
          new InputOption('reload', '-r', InputOption::VALUE_NONE, 'Only reload configuration')
        );
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
        $plugin->registerListeners($this->emitter);
        $plugin->setupConfiguration(Config::factory());
    }
}
