<?php

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Plugins\PHPBrew\PHPBrewPlugin;
use Dotfiles\Core\Emitter;
use Dotfiles\Core\Events\ReloadBashConfigEvent;
use PHPUnit\Framework\TestCase;

class PHPBrewPluginTest extends TestCase
{
    public function testGetName()
    {
        $phpbrew = new PHPBrewPlugin();
        $this->assertEquals('PHPBrew',$phpbrew->getName());
    }

    public function testRegisterListeners()
    {
        $phpbrew = new PHPBrewPlugin();

        $emitter = $this->getMockBuilder(Emitter::class)
            ->setMethods(['addListener'])
            ->getMock()
        ;
        $emitter->expects($this->once())
            ->method('addListener')
            ->with(ReloadBashConfigEvent::EVENT_NAME,[$phpbrew,'handleBashConfig'])
        ;

        $phpbrew->registerListeners($emitter);
    }

    public function testHandleBashConfig()
    {
        $event = $this->getMockBuilder(ReloadBashConfigEvent::class)
            ->setMethods(['addBashConfig'])
            ->getMock()
        ;
        $event->expects($this->once())
            ->method('addBashConfig')
            ->with([
                'export PHPBREW_SET_PROMPT=1',
                'export PHPBREW_RC_ENABLE=1',
                'source $HOME/.phpbrew/bashrc'
            ])
        ;

        $phpbrew = new PHPBrewPlugin();
        $phpbrew->handleBashConfig($event);
    }
}
