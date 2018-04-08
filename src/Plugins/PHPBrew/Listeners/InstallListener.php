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

namespace Dotfiles\Plugins\PHPBrew\Listeners;

use Dotfiles\Plugins\PHPBrew\Installer;

class InstallListener
{
    /**
     * @var Installer
     */
    private $installer;

    public function __construct(Installer $installer)
    {
        $this->installer = $installer;
    }

    public function onInstallEvent(): void
    {
        $installer = $this->installer;
        $installer->run();
    }
}
