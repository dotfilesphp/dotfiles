<?php

namespace Dotfiles\Core\DI;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // always first check if the primary service is defined
        if (!$container->has(Application::class)) {
            return;
        }

        $definition = $container->findDefinition(Application::class);

        // find all service IDs with the app.mail_transport tag
        $taggedServices = $container->findTaggedServiceIds('dotfile.command');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addCommand', array(new Reference($id)));
        }
    }
}
