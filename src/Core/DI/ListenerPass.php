<?php

namespace Dotfiles\Core\DI;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(Dispatcher::class)) {
            return;
        }

        $definition = $container->findDefinition(Dispatcher::class);

        // find all service IDs with the event_subscribers tag
        $taggedServices = $container->findTaggedServiceIds('event_subscribers');
        foreach($taggedServices as $id => $tags){
            $definition->addMethodCall('addSubscriber',[new Reference($id)]);
        }
    }
}
