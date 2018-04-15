<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM\Tests;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Plugins\NVM\EventSubscriber;
use Dotfiles\Plugins\NVM\Installer;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class EventSubscriberTest.
 *
 * @covers \Dotfiles\Plugins\NVM\EventSubscriber
 */
class EventSubscriberTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $installer;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->installer = $this->createMock(Installer::class);
    }

    public function getEventSubscriberObject()
    {
        $parameters = $this->getParameters();

        return new EventSubscriber($parameters, $this->installer);
    }

    public function testOnPrePatch()
    {
        $installDir = $this->getParameters()->get('nvm.install_dir');
        if (!is_dir($installDir)) {
            mkdir($installDir, 0755, true);
        }
        $this->installer->expects($this->once())
            ->method('getBashPatch')
            ->willReturn('some-patch')
        ;
        $eventSubscriber = $this->getEventSubscriberObject();
        $patchEvent = new PatchEvent(array());

        $dispatcher = new Dispatcher();
        $dispatcher->addSubscriber($eventSubscriber);
        $dispatcher->dispatch(Constant::EVENT_PRE_PATCH, $patchEvent);
        $this->assertArrayHasKey('.bashrc', $patches = $patchEvent->getPatches());
        $this->assertEquals('some-patch', $patches['.bashrc'][0]);

        // test patch should not called when nvm not installed
        $patchEvent->setPatch('.bashrc', array());
        rmdir($installDir);
        $dispatcher->dispatch(Constant::EVENT_PRE_PATCH, $patchEvent);
        $patches = $patchEvent->getPatches();
        $this->assertEmpty($patches['.bashrc']);
    }
}
