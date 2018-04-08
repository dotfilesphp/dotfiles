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

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Plugin;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Plugins\PHPBrew\Config\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PHPBrewPlugin extends Plugin
{
    public function getName()
    {
        return 'PHPBrew';
    }

    public function setupConfiguration(Config $config): void
    {
        $config->addDefinition(new Definition());
    }

    public function configureContainer(ContainerBuilder $container, Config $config): void
    {
    }
}
