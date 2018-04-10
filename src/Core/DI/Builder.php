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

namespace Dotfiles\Core\DI;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\DI\Compiler\CommandPass;
use Dotfiles\Core\DI\Compiler\ListenerPass;
use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Builder
{
    /**
     * @var string
     */
    private $cacheFileName;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var DumperInterface
     */
    private $dumper;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     * @return Builder
     */
    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheFileName():string
    {
        return $this->config->get('dotfiles.cache_dir').'/container.php';
    }

    /**
     * @throws \Exception
     */
    public function compile(): void
    {
        $cachePath = $this->getCacheFileName();
        $cache = new ConfigCache($cachePath, true);
        $env = getenv('DOTFILES_ENV');

        if (!$cache->isFresh() || 'dev' === $env) {
            $builder = $this->getContainerBuilder();
            $this->configureCoreServices($builder);
            $builder->addCompilerPass(new CommandPass());
            $builder->addCompilerPass(new ListenerPass());
            $builder->compile();

            $dumper = $this->getDumper();
            //file_put_contents($target,$dumper->dump(['class'=>'CachedContainer']), LOCK_EX);
            $cache->write($dumper->dump(array('class' => 'CachedContainer')), $builder->getResources());
        }
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        if (null === $this->container) {
            if (!class_exists('CachedContainer')) {
                include_once $this->getCacheFileName();
            }
            $this->container = new \CachedContainer();
        }

        return $this->container;
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder(): ContainerBuilder
    {
        if (null === $this->containerBuilder) {
            $this->containerBuilder = new ContainerBuilder();
        }

        return $this->containerBuilder;
    }

    /**
     * @return DumperInterface
     */
    public function getDumper(): DumperInterface
    {
        if (null === $this->dumper) {
            $this->dumper = new PhpDumper($this->containerBuilder);
        }

        return $this->dumper;
    }

    /**
     * @param string $cacheFileName
     *
     * @return self
     */
    public function setCacheFileName(string $cacheFileName): self
    {
        if (!is_dir($dir = dirname($cacheFileName))) {
            mkdir($dir, 0755, true);
        }
        $this->cacheFileName = $cacheFileName;

        return $this;
    }

    /**
     * @param ContainerBuilder $builder
     *
     * @return self
     */
    public function setContainerBuilder(ContainerBuilder $builder): self
    {
        $this->containerBuilder = $builder;

        return $this;
    }

    /**
     * @param DumperInterface $dumper
     *
     * @return Builder
     */
    public function setDumper(DumperInterface $dumper): self
    {
        $this->dumper = $dumper;

        return $this;
    }

    /**
     * @param ContainerBuilder $builder
     *
     * @throws \Exception
     */
    private function configureCoreServices(ContainerBuilder $builder): void
    {
        $locator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new YamlFileLoader($builder, $locator);
        $loader->load('services.yaml');
    }
}
