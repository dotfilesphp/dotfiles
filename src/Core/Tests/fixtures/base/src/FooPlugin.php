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

namespace Dotfiles\Plugins\Foo;

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FooPlugin extends Plugin
{
    public function setupConfiguration(Parameters $config): void
    {
        // TODO: Implement setupConfiguration() method.
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        // TODO: Implement load() method.
    }
}
