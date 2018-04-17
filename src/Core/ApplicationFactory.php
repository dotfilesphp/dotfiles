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
use Dotfiles\Core\DI\Compiler\DefaultPass;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class ApplicationFactory
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $env;

    /**
     * @var array
     */
    private $envFiles = array();

    /**
     * @var Plugin[]
     */
    private $plugins = array();

    public function __construct()
    {
        $files = array(__DIR__.'/Resources/default.env');

        // $PWD/.env always win
        $cwd = getcwd();
        if (is_file($file = $cwd.'/.env.dist')) {
            $files[] = $file;
        }
        if (is_file($file = $cwd.'/.env')) {
            $files[] = $file;
        }

        $this->envFiles = $files;
    }

    /**
     * @return $this
     * @codeCoverageIgnore
     */
    public function boot(): self
    {
        $this->loadDotEnv();
        $this->addAutoload();
        $this->loadPlugins();
        $this->compileContainer();
        $this->ensureDir();

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

    protected function compileContainer(): void
    {
        $configs = $this->getConfiguration();
        $builder = new ContainerBuilder();
        $this->processCoreConfig($configs, $builder);
        // processing core configuration

        /* @var Plugin $plugin */
        foreach ($this->plugins as $name => $plugin) {
            $pluginConfig[$name] = array_key_exists($name, $configs) ? $configs[$name] : array();

            $plugin->load($pluginConfig, $builder);
        }

        $cachePath = $this->getCachePathPrefix().'/container.php';
        $cache = new ConfigCache($cachePath, $this->debug);
        $className = 'CachedContainer'.$this->getContainerId();

        // always compile container in dev environment
        if (!$cache->isFresh() || 'prod' !== $this->env) {
            $builder->addCompilerPass(new DefaultPass());
            $builder->compile(true);
            $dumper = new PhpDumper($builder);
            $resources = $this->envFiles;
            array_walk($resources, function (&$item): void {
                $item = new FileResource($item);
            });
            $resources = array_merge($resources, $builder->getResources());
            $cache->write($dumper->dump(array('class' => $className)), $resources);
        }
        if (!class_exists($className)) {
            include $cachePath;
        }
        $container = new $className();

        $parameters = new Parameters();
        $parameters->setConfigs($container->getParameterBag()->all());
        $container->set('dotfiles.parameters', $parameters);
        $this->container = $container;
    }

    protected function getCachePathPrefix()
    {
        // using argv command to differ each dotfiles executable file
        global $argv;
        $command = $argv[0];
        $cacheDir = getenv('DOTFILES_CACHE_DIR');
        $env = getenv('DOTFILES_ENV');

        return $cacheDir.DIRECTORY_SEPARATOR.$this->getContainerId().DIRECTORY_SEPARATOR.$env;
    }

    protected function getContainerId()
    {
        global $argv;
        $command = $argv[0];

        return crc32($command);
    }

    private function addAutoload(): void
    {
        $baseDir = getenv('DOTFILES_BACKUP_DIR');
        $autoloadFile = $baseDir.'/vendor/autoload.php';

        // ignore if files is already loaded in phar file
        if (
            is_file($autoloadFile)
        ) {
            include_once $autoloadFile;
        }
    }

    private function ensureDir(): void
    {
        /* @var Parameters $parameters */
        $parameters = $this->container->get('dotfiles.parameters');
        Toolkit::ensureDir($parameters->get('dotfiles.install_dir'));
        Toolkit::ensureDir($parameters->get('dotfiles.bin_dir'));
        Toolkit::ensureDir($parameters->get('dotfiles.vendor_dir'));
    }

    private function getConfiguration()
    {
        $configDir = getenv('DOTFILES_CONFIG_DIR');
        if (!is_dir($configDir)) {
            return array();
        }
        $cacheFile = $this->getCachePathPrefix().'/config.php';
        $cache = new ConfigCache($cacheFile, $this->debug);
        if (!$cache->isFresh() || 'dev' === $this->env) {
            $finder = Finder::create()
                ->name('*.yaml')
                ->name('*.yml')
                ->in($configDir)
            ;
            $configs = array();
            $configFiles = array();
            /* @var SplFileInfo $file */
            foreach ($finder->files() as $file) {
                $parsed = Yaml::parseFile($file->getRealPath());
                if (is_array($parsed)) {
                    $configs = array_merge_recursive($configs, $parsed);
                }
                $configFiles[] = new FileResource($file->getRealPath());
            }
            $template = "<?php\n/* ParameterBag Cache File Generated at %s */\nreturn %s;\n";
            $time = new \DateTime();
            $contents = sprintf(
                $template,
                $time->format('Y-m-d H:i:s'),
                var_export($configs, true)
            );
            $cache->write($contents, $configFiles);
        }

        return include $cacheFile;
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

    private function loadDotEnv(): void
    {
        global $argv;
        // set temp dir based on OS
        putenv('DOTFILES_TEMP_DIR='.sys_get_temp_dir().'/dotfiles');
        $dryRun = in_array('--dry-run', $argv) ? true : false;
        putenv('DOTFILES_DRY_RUN='.$dryRun);

        $files = $this->envFiles;
        $env = new Dotenv();
        if (count($files) > 0) {
            call_user_func_array(array($env, 'load'), $files);
        }

        $dev = getenv('DOTFILES_ENV');
        if (
            'dev' !== $dev && is_file($file = getenv('HOME').'/.dotfiles_profile')) {
            $env->load($file);
        }

        $this->debug = (bool) getenv('DOTFILES_DEBUG');
        $this->env = getenv('DOTFILES_ENV');

        $homeDir = getenv('DOTFILES_HOME_DIR');
        $backupDir = getenv('DOTFILES_BACKUP_DIR');
        if (!getenv('DOTFILES_INSTALL_DIR')) {
            putenv('DOTFILES_INSTALL_DIR='.$homeDir.'/.dotfiles');
        }

        if (!getenv('DOTFILES_CONFIG_DIR')) {
            putenv('DOTFILES_CONFIG_DIR='.getenv('DOTFILES_BACKUP_DIR').'/config');
        }

        if (!getenv('DOTFILES_CACHE_DIR')) {
            putenv('DOTFILES_CACHE_DIR='.$backupDir.'/var/cache');
        }
        if (!getenv('DOTFILES_LOG_DIR')) {
            putenv('DOTFILES_LOG_DIR='.$backupDir.'/var/log');
        }
    }

    /**
     * Load available plugins.
     */
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

    private function processCoreConfig(array $configs, ContainerBuilder $builder): void
    {
        $dotfileConfig = array_key_exists('dotfiles', $configs) ? $configs['dotfiles'] : array();
        $processor = new Processor();
        $parameters = $processor->processConfiguration(new Configuration(), $dotfileConfig);
        $parameters = array('dotfiles' => $parameters);
        Toolkit::flattenArray($parameters);

        $builder->getParameterBag()->add($parameters);

        $locator = new FileLocator(__DIR__.'/Resources/config');
        $loader = new YamlFileLoader($builder, $locator);
        $loader->load('services.yaml');
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
    }
}
