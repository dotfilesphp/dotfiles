<?php

namespace Dotfiles\Core\Tests\DI;

use PHPUnit\Framework\TestCase;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BuilderTest extends TestCase
{
    public function testCompile()
    {
        $definition = $this->createMock(Definition::class);

        $cb = $this->createMock(ContainerBuilder::class);
        $definition->expects($this->any())
            ->method('setPublic')
            ->will($this->returnSelf())
        ;
        $cb->expects($this->exactly(3))
            ->method('register')
            ->willReturn($definition)
            ->withConsecutive(
                [Dispatcher::class],
                [Config::class],
                [Application::class]
            )
        ;
        $builder = new Builder();
        $builder->setContainerBuilder($cb);
        $builder->compile();
    }
}
