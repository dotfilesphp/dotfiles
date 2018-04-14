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

namespace Dotfiles\Plugins\Bash\Tests\Event;

use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReloadBashConfigEventTest extends TestCase
{
    public function testAddHeaderConfig(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $event = new ReloadBashConfigEvent($logger);
        $event->addHeaderConfig(array('foo'));
        $event->addHeaderConfig('bar');
        $event->addFooterConfig(array('hello'));
        $event->addFooterConfig('world');
        $output = $event->getBashConfig();
        $this->assertContains('foo', $output);
        $this->assertContains('bar', $output);
    }

    public function testDispatch(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('debug')
            ->with('Added bash config', array('contents' => 'dispatched'))
        ;
        $event = new ReloadBashConfigEvent($logger);
        $dispatcher = new Dispatcher();
        $dispatcher->addListener(ReloadBashConfigEvent::NAME, function ($event): void {
            $event->addHeaderConfig('dispatched');
        });
        $dispatcher->dispatch(ReloadBashConfigEvent::NAME, $event);

        $this->assertContains('dispatched', $event->getBashConfig());
    }
}
