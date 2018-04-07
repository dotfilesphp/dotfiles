<?php

namespace Dotfiles\Plugins\Bash\Tests\Listeners;

use Psr\Log\LoggerInterface;
use Dotfiles\Plugins\Bash\Listeners\InstallListener;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use PHPUnit\Framework\TestCase;

class InstallListenerTest extends TestCase
{
    public function testHandleEvent()
    {
        if(!is_dir($dir='/tmp/dotfiles/test/home/.dotfiles')){
            mkdir($dir,0755,true);
        }

        $dispatcher = $this->createMock(Dispatcher::class);
        $event = $this->createMock(InstallEvent::class);
        $config = $this->createMock(Config::class);
        $event->expects($this->any())
            ->method('getConfig')
            ->willReturn($config)
        ;
        $logger = $this->createMock(LoggerInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(ReloadBashConfigEvent::NAME,new ReloadBashConfigEvent($logger))
        ;
        $listener = new InstallListener($dispatcher,$config, $logger);
        $listener->setInstallDir($dir);
        $listener->onInstallEvent($event);

        $this->assertFileExists($dir.'/bashrc');
        $this->assertFileExists(getenv('HOME').'/.bashrc');
    }
}
