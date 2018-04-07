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

use Dotfiles\Core\DI\Compiler\CommandPass;
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
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var DumperInterface
     */
    private $dumper;

    /**
     * @var string
     */
    private $cacheFileName;

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
     * @return string
     */
    public function getCacheFileName(): string
    {
        if (null === $this->cacheFileName) {
            $this->setCacheFileName(getcwd().'/var/cache/container.php');
        }

        return $this->cacheFileName;
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

    public function compile()
    {
        $cachePath = $this->getCacheFileName();
        $cache = new ConfigCache($cachePath, true);
        if (!$cache->isFresh()) {
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
            include_once $this->getCacheFileName();
            $this->container = new \CachedContainer();
        }

        return $this->container;
    }

    /**
     * @param ContainerBuilder $builder
     *
     * @throws \Exception
     */
    private function configureCoreServices(ContainerBuilder $builder)
    {
        $locator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new YamlFileLoader($builder, $locator);
        $loader->load('services.yaml');
    }
}
