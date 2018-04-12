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

namespace Dotfiles\Core\DI\Compiler;

use Dotfiles\Core\Event\Dispatcher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(Dispatcher::class)) {
            return;
        }

        $definition = $container->findDefinition(Dispatcher::class);

        // find all service IDs with the event_subscriber tag
        $taggedServices = $container->findTaggedServiceIds('event_subscriber');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addSubscriber', array(new Reference($id)));
        }

        $taggedServices = $container->findTaggedServiceIds('event_listener');
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $index => $values) {
                $event = $values['event'];
                $method = array_key_exists('method', $values) ? $values['method'] : null;
                $priority = array_key_exists('priority', $values) ? $values['priority'] : 0;
                $definition->addMethodCall(
                    'addListener',
                    array(
                        $event,
                        array(new Reference($id), $method),
                        $priority,
                    )
                );
            }
        }
    }
}
