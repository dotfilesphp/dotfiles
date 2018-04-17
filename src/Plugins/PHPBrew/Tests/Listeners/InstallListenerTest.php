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

namespace Dotfiles\Plugins\PHPBrew\Tests\Listeners;

use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Plugins\PHPBrew\Installer;
use Dotfiles\Plugins\PHPBrew\Listeners\InstallListener;

class InstallListenerTest extends BaseTestCase
{
    public function testOnInstallEvent(): void
    {
        $installer = $this->createMock(Installer::class);
        $installer->expects($this->once())
            ->method('run')
        ;

        $listener = new InstallListener($installer);
        $listener->onInstallEvent();
    }
}
