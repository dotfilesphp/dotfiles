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

namespace Dotfiles\Plugins\Bash\Tests\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Plugins\Bash\Listeners\InstallListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InstallListenerTest extends TestCase
{
    public function testOnPatchEvent(): void
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $event = $this->createMock(PatchEvent::class);
        $config = $this->createMock(Config::class);
        $logger = $this->createMock(LoggerInterface::class);

        $tempDir = sys_get_temp_dir().'/dotfiles/tests/bash';
        $config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.home_dir', $tempDir.'/home'),
                array('dotfiles.install_dir', $tempDir.'/.dotfiles'),
            ))
        ;
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(ReloadBashConfigEvent::NAME, $this->isInstanceOf(ReloadBashConfigEvent::class))
        ;
        $listener = new InstallListener($dispatcher, $config, $logger);
        $listener->onPatchEvent($event);

        $this->assertFileExists($tempDir.'/.dotfiles/bashrc');
    }
}
