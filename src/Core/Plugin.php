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

namespace Dotfiles\Core;

use Dotfiles\Core\Util\Toolkit;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class Plugin.
 */
abstract class Plugin extends Extension
{
    public function getName(): string
    {
        $class = get_class($this);
        $exp = explode('\\', $class);
        $baseClassName = $exp[count($exp) - 1];
        $pluginName = strtolower(str_replace('Plugin', '', $baseClassName));

        return $pluginName;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $r = new \ReflectionClass(get_class($this));
        $resourceDir = dirname($r->getFileName()).'/Resources';

        if (is_file($serviceConfig = $resourceDir.DIRECTORY_SEPARATOR.'services.yaml')) {
            $locator = new FileLocator($resourceDir);
            $loader = new YamlFileLoader($locator, $container);
            $loader->load($serviceConfig);
        }

        $configuration = $this->getConfiguration($config, $container);
        $configs = $this->processConfiguration($configuration, $config);
        Toolkit::flattenArray($configs, $this->getName());
        $container->getParameterBag()->add($configs);
    }
}
