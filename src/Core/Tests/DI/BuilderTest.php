<?php

namespace Dotfiles\Core\Tests\DI;

use PHPUnit\Framework\TestCase;
use Dotfiles\Core\DI\Builder;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BuilderTest extends TestCase
{
    public function testCompile()
    {
        $cb = $this->createMock(ContainerBuilder::class);

        $cb->expects($this->exactly(3))
            ->method('register')
            ->withConsecutive([
                [Dispatcher::class, null],
                [Config::class, null],
                [Application::class, null]
            ])
            ->willReturn($cb)
        ;
        $builder = new Builder();
        $builder->setContainerBuilder($cb);
        $builder->compile();
    }
}
