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

interface PluginInterface
{
    /**
     * Returns the name of plugin.
     *
     * @return string
     */
    public function getName();

    /**
     * @param Config $config
     *
     * @return mixed
     */
    public function setupConfiguration(Config $config);

    public function configureContainer(ContainerBuilder $container, Config $config);
}
