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

use Dotfiles\Core\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class Plugin implements PluginInterface
{
    public function registerListeners(Emitter $emitter): void
    {
    }

    public function setupConfiguration(Config $config): void
    {
    }

    public function configureContainer(ContainerBuilder $container, Config $config): void
    {
    }
}
