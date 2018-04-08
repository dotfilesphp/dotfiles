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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

abstract class Plugin extends Extension implements PluginInterface
{
    public function getName(): string
    {
        $class = get_class($this);
        $exp = explode('\\', $class);
        $baseClassName = $exp[count($exp) - 1];
        $pluginName = strtolower(str_replace('Plugin', '', $baseClassName));

        return $pluginName;
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
    }
}
