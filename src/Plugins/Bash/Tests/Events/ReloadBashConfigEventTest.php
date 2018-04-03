<?php

namespace Dotfiles\Core\Tests\Events;

use Dotfiles\Plugins\Bash\Events\ReloadBashConfigEvent;
use Dotfiles\Core\Emitter;
use Dotfiles\Core\Util\LoggerInterface;
use DOtfiles\Core\Config\Config;

use PHPUnit\Framework\TestCase;

class ReloadBashConfigEventTest extends TestCase
{
    public function testGetName()
    {
        $event = new ReloadBashConfigEvent();
        $this->assertEquals(ReloadBashConfigEvent::EVENT_NAME,$event->getName());
    }

    public function testAddHeaderConfig()
    {
        $event = new ReloadBashConfigEvent();
		$event->setEmitter(new Emitter);
        $event->addHeaderConfig(['foo']);
        $event->addHeaderConfig('bar');
        $event->addFooterConfig(['hello']);
        $event->addFooterConfig('world');
        $output = $event->getBashConfig();
        $this->assertContains('foo',$output);
        $this->assertContains('bar',$output);
    }

    public function testEmit()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->setMethods(['debug'])
            ->getMock()
        ;
		#$r = new \ReflectionObject($logger);
		#print_r($r->getMethods());
        $logger->expects($this->once())
            ->method('debug')
            ->with("Added bash config:\ndispatched")
        ;

        $config = $this->createMock(Config::class);
        $event = new ReloadBashConfigEvent();
        $emitter = new Emitter();
        $emitter->setLogger($logger);
        $emitter->setConfig($config);

        $emitter->addListener(ReloadBashConfigEvent::EVENT_NAME,function($event){
            $event->addHeaderConfig(['dispatched']);
        });

        $emitter->emit($event);

        $this->assertContains('dispatched',$event->getBashConfig());
    }
}
