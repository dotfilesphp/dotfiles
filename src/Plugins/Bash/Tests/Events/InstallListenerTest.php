<?php

namespace Dotfiles\Plugins\Bash\Tests\Events;

use Dotfiles\Plugins\Bash\Events\InstallListener;
use Dotfiles\Core\Emitter;
use Dotfiles\Core\Events\EventInterface;
use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Events\ReloadBashConfigEvent;
use PHPUnit\Framework\TestCase;

class InstallListenerTest extends TestCase
{
    public function testHandleEvent()
    {
        if(!is_dir($dir='/tmp/dotfiles/test/home/.dotfiles')){
            mkdir($dir,0755,true);
        }

        $emitter = $this->createMock(Emitter::class);
        $event = $this->createMock(EventInterface::class);
        $config = $this->createMock(Config::class);
        $event->expects($this->any())
            ->method('getEmitter')
            ->willReturn($emitter)
        ;
        $event->expects($this->any())
            ->method('getConfig')
            ->willReturn($config)
        ;

        $emitter->expects($this->once())
            ->method('emit')
            ->with(new ReloadBashConfigEvent())
            ;
        $config->expects($this->any())
            ->method('get')
            ->with('dotfiles.install_dir')
            ->willReturn($dir)
        ;
        $listener = new InstallListener();
        $listener->handle($event);

        $this->assertFileExists($dir.'/bashrc');
        $this->assertFileExists(getenv('HOME').'/.bashrc');
    }
}
