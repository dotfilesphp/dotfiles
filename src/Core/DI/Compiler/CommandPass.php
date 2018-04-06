<?php
/**
 * Created by PhpStorm.
 * User: toni
 * Date: 4/6/18
 * Time: 1:56 PM
 */

namespace Dotfiles\Core\DI\Compiler;


use Dotfiles\Core\Application;
use Dotfiles\Core\Command\CommandInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $application = $container->findDefinition(Application::class);
        $definitions = $container->getDefinitions();

        foreach($definitions as $definition){
            $class = $definition->getClass();
            if(class_exists($class)){
                $r = new \ReflectionClass($class);
                if($r->implementsInterface(CommandInterface::class)){
                    $application->addMethodCall('add',[new Reference($class)]);
                }
            }
        }
    }

}
