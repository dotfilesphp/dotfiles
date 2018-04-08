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
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Plugins\Bash\Listeners\InstallListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InstallListenerTest extends TestCase
{
    public function testHandleEvent(): void
    {
        if (!is_dir($dir = '/tmp/dotfiles/test/home/.dotfiles')) {
            mkdir($dir, 0755, true);
        }

        $dispatcher = $this->createMock(Dispatcher::class);
        $event = $this->createMock(InstallEvent::class);
        $config = $this->createMock(Config::class);
        $logger = $this->createMock(LoggerInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(ReloadBashConfigEvent::NAME, new ReloadBashConfigEvent($logger))
        ;
        $listener = new InstallListener($dispatcher, $config, $logger);
        $listener->setInstallDir($dir);
        $listener->onInstallEvent($event);

        $this->assertFileExists($dir.'/bashrc');
        $this->assertFileExists(getenv('HOME').'/.bashrc');
    }
}
