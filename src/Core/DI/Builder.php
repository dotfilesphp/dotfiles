<?php

declare(strict_types=1);

namespace Dotfiles\Core\DI;

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
     * @return self
     */
    public function setContainerBuilder(ContainerBuilder $builder):Builder
    {
        $this->containerBuilder = $builder;

        return $this;
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder():ContainerBuilder
    {
        if(is_null($this->containerBuilder)){
            $this->containerBuilder = new ContainerBuilder();
        }
        return $this->containerBuilder;
    }

    /**
     * @return DumperInterface
     */
    public function getDumper(): DumperInterface
    {
        if(is_null($this->dumper)){
            $this->dumper = new PhpDumper($this->containerBuilder);
        }
        return $this->dumper;
    }

    /**
     * @param DumperInterface $dumper
     * @return Builder
     */
    public function setDumper(DumperInterface $dumper): Builder
    {
        $this->dumper = $dumper;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheFileName(): string
    {
        if(is_null($this->cacheFileName)){
            $this->setCacheFileName(getcwd().'/var/cache/container.php');
        }
        return $this->cacheFileName;
    }

    /**
     * @param string $cacheFileName
     * @return self
     */
    public function setCacheFileName(string $cacheFileName): Builder
    {
        if(!is_dir($dir = dirname($cacheFileName))){
            mkdir($dir,0755,true);
        }
        $this->cacheFileName = $cacheFileName;

        return $this;
    }

    public function compile()
    {
        $builder = $this->getContainerBuilder();
        $baseDir = realpath(dirname(__DIR__.'/../../../src'));
        if(false !== strpos($dir = \Phar::running(),'phar:///')){
            $baseDir = str_replace('/dotfiles.phar','',\Phar::running(false));
        }
        $builder->setParameter('dotfiles.base_dir',$baseDir);

        $this->configureCoreServices($builder);
        $builder->addCompilerPass(new ListenerPass());
        $builder->addCompilerPass(new CommandPass());
        $builder->compile();

        $dumper = $this->getDumper();
        $target = $this->getCacheFileName();
        file_put_contents($target,$dumper->dump(['class'=>'CachedContainer']), LOCK_EX);
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        if(is_null($this->container)){
            include_once $this->getCacheFileName();
            $this->container = new \CachedContainer();
        }

        return $this->container;
    }

    /**
     * @param ContainerBuilder $builder
     * @throws \Exception
     */
    private function configureCoreServices(ContainerBuilder $builder)
    {
        $locator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new YamlFileLoader($builder,$locator);
        $loader->load('services.yaml');
    }
}
