<?php

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\Emitter;
use Dotfiles\Core\Util\LoggerInterface;

use PHPUnit\Framework\TestCase;

class EmitterTest extends TestCase
{
    public function testFactory()
    {
        $this->assertInstanceOf(Emitter::class,Emitter::factory());
    }

    public function testDispatch()
    {
        $hasCalled = false;
        $emitter = Emitter::factory();
        $emitter->addListener('event.test',function() use(&$hasCalled){
            $hasCalled = true;
        });
        $emitter->emit('event.test');
        $this->assertTrue($hasCalled);
    }


    public function testLogger()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $emitter = new Emitter();
        $emitter->setLogger($logger);
        $this->assertEquals($logger,$emitter->getLogger());
    }
}
