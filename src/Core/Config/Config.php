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

namespace Dotfiles\Core\Config;

use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Config.
 *
 * @covers \Dotfiles\Core\Config\Config
 */
class Config implements \ArrayAccess
{
    private $configDirs = array();

    private $configs = array();

    private $defaults = array();

    /**
     * @var ConfigurationInterface[]
     */
    private $definitions = array();

    private $files = array();

    private $flattened = array();

    /**
     * @param $directory
     *
     * @return Config
     */
    public function addConfigDir($directory): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Directory ${directory} not exists");
        }
        if (!in_array($directory, $this->configDirs)) {
            $this->configDirs[] = $directory;
        }

        return $this;
    }

    public function addDefinition(ConfigurationInterface $config): self
    {
        $builder = $config->getConfigTreeBuilder();
        $name = $builder->buildTree()->getName();
        $this->definitions[$name] = $config;
        $this->defaults[$name] = array();

        return $this;
    }

    public function get($name)
    {
        if (array_key_exists($name, $this->configs)) {
            return $this->configs[$name];
        } elseif (array_key_exists($name, $this->flattened)) {
            return $this->flattened[$name];
        } else {
            throw new \InvalidArgumentException('Unknown config key: "'.$name.'"');
        }
    }

    public function getAll($flattened = false)
    {
        return $flattened ? $this->flattened : $this->configs;
    }

    /**
     * @return array
     */
    public function getConfigDirs(): array
    {
        return $this->configDirs;
    }

    /**
     * Load configuration from files and default value.
     */
    public function loadConfiguration(): void
    {
        $cachePath = Toolkit::getCachePathPrefix().'/config.php';
        $cache = new ConfigCache($cachePath, true);
        $env = getenv('DOTFILES_ENV');
        if (!$cache->isFresh() || 'dev' === $env) {
            $processor = new Processor();
            $configs = $this->processFiles();
            $generated = array();
            foreach ($configs as $rootKey => $values) {
                if (!isset($this->definitions[$rootKey])) {
                    continue;
                }
                $temp = array();
                $config = $this->definitions[$rootKey];
                $temp[$rootKey] = $values;
                $processed = $processor->processConfiguration($config, $temp);
                if (!isset($generated[$rootKey])) {
                    $generated[$rootKey] = array();
                }
                $generated[$rootKey] = array_merge_recursive($generated[$rootKey], $processed);
            }
            $expConfig = var_export($generated, true);
            $flattened = $generated;
            Toolkit::flattenArray($flattened);
            $expFlattened = var_export($flattened, true);

            /* provide a way to handle normalize config */
            $this->normalizeConfig($flattened, $expConfig);
            $this->normalizeConfig($flattened, $expConfig);
            $this->normalizeConfig($flattened, $expFlattened);
            $this->normalizeConfig($flattened, $expFlattened);

            $code = <<<EOC
<?php
\$this->configs = ${expConfig};
\$this->flattened = ${expFlattened};
EOC;
            $cache->write($code, $this->files);
        }
        require $cachePath;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->configs[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->configs[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->configs[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->configs[$offset]);
    }

    public function set($name, $value): void
    {
        $this->flattened[$name] = $value;
    }

    private function normalizeConfig($flattened, &$config): void
    {
        foreach ($flattened as $name => $value) {
            $format = '%'.$name.'%';
            $config = strtr($config, array($format => $value));
        }
    }

    private function processFiles()
    {
        $configs = $this->defaults;
        if (!count($this->configDirs) > 0) {
            return $configs;
        }
        $finder = Finder::create()
            ->name('*.yaml')
            ->name('*.yml')
        ;
        foreach ($this->configDirs as $dir) {
            $finder->in($dir);
        }
        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files() as $file) {
            $parsed = Yaml::parseFile($file->getRealPath());
            if (is_array($parsed)) {
                $configs = array_merge_recursive($configs, $parsed);
            }
            $this->files[] = new FileResource($file->getRealPath());
        }

        return $configs;
    }
}
