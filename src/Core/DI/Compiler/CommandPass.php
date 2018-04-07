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

use Dotfiles\Core\Application;
use Dotfiles\Core\Command\CommandInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $application = $container->findDefinition(Application::class);
        $definitions = $container->getDefinitions();

        foreach ($definitions as $definition) {
            $class = $definition->getClass();
            if (class_exists($class)) {
                $r = new \ReflectionClass($class);
                if ($r->implementsInterface(CommandInterface::class)) {
                    $application->addMethodCall('add', array(new Reference($class)));
                }
            }
        }
    }
}
