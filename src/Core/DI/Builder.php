<?php

declare(strict_types=1);

namespace Dotfiles\Core\DI;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;

class Builder
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function __construct()
    {
        $this->containerBuilder = new ContainerBuilder();
    }

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
        return $this->containerBuilder;
    }

    public function compile()
    {
        $builder = $this->containerBuilder;

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
        $builder->addCompilerPass(new ListenerPass());
        $builder->addCompilerPass(new CommandPass());
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
    }
}
