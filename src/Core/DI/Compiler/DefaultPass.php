<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\DI\Compiler;

use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Console\Application;
use Dotfiles\Core\Event\Dispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DefaultPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $ids = $container->getServiceIds();
        $commands = array();
        $subscribers = array();
        foreach ($ids as $id) {
            $definition = $container->findDefinition($id);
            $definition
                ->setPublic(true)
                ->setAutoconfigured(true)
                ->setAutowired(true)
            ;
            $class = $definition->getClass();
            if (!class_exists($class)) {
                continue;
            }
            if (!$container->hasDefinition($class) && class_exists($class)) {
                if ($class !== $id) {
                    $container
                        ->setAlias($class, $id)
                        ->setPublic(true)
                    ;
                }
            }

            $r = new \ReflectionClass($class);
            if (
                $r->implementsInterface(CommandInterface::class)
                && !in_array($class, $commands)
            ) {
                $commands[] = $class;
            }

            if (
                $r->implementsInterface(EventSubscriberInterface::class)
                && !in_array($class, $subscribers)
            ) {
                $subscribers[] = $class;
            }
        }

        $application = $container->findDefinition(Application::class);
        $dispatcher = $container->findDefinition(Dispatcher::class);
        foreach ($commands as $class) {
            $application->addMethodCall('add', array(new Reference($class)));
        }

        foreach ($subscribers as $class) {
            $dispatcher->addMethodCall('addSubscriber', array(new Reference($class)));
        }
    }
}
