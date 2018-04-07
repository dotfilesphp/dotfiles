<?php

namespace Dotfiles\Plugins\Bash\Tests\Event;

use Psr\Log\LoggerInterface;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Core\Event\Dispatcher;
use DOtfiles\Core\Config\Config;

use PHPUnit\Framework\TestCase;

class ReloadBashConfigEventTest extends TestCase
{
    public function testAddHeaderConfig()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $event = new ReloadBashConfigEvent($logger);
		$event->setLogger($logger);
        $event->addHeaderConfig(['foo']);
        $event->addHeaderConfig('bar');
        $event->addFooterConfig(['hello']);
        $event->addFooterConfig('world');
        $output = $event->getBashConfig();
        $this->assertContains('foo',$output);
        $this->assertContains('bar',$output);
    }

    public function testDispatch()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with("Added bash config",['contents' => 'dispatched'])
        ;
        $event = new ReloadBashConfigEvent($logger);
        $event->setLogger($logger);
        $dispatcher = new Dispatcher();
        $dispatcher->addListener(ReloadBashConfigEvent::NAME,function($event){
            $event->addHeaderConfig('dispatched');
        });
        $dispatcher->dispatch(ReloadBashConfigEvent::NAME,$event);

        $this->assertContains('dispatched',$event->getBashConfig());
    }
}
