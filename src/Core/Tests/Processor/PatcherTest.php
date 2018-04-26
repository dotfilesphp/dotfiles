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

namespace Dotfiles\Core\Tests\Processor;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Processor\Patcher;
use Dotfiles\Core\Tests\Helper\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PatcherTest.
 *
 * @covers \Dotfiles\Core\Processor\Patcher
 */
class PatcherTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
    }

    public function testPatch(): void
    {
        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                array(Constant::EVENT_PRE_PATCH, $this->isInstanceOf(PatchEvent::class)),
                array(Constant::EVENT_POST_PATCH, $this->isInstanceOf(PatchEvent::class))
            )
        ;
        $homeDir = $this->getParameters()->get('dotfiles.home_dir');
        $file = $homeDir.'/.bashrc';
        touch($file);
        $event = new RestoreEvent();
        $patch = $this->getPatcherObject();
        $patch->onPreRestore($event);
        $patch->onPostRestore();
        $output = $this->getDisplay();

        $this->assertContains('applying patch', $output);

        $this->assertFileExists($file);
        $contents = file_get_contents($file);
        $this->assertContains('#patch defaults', $contents);
        $this->assertContains('#patch machine', $contents);
    }

    private function getPatcherObject()
    {
        $this->createBackupDirMock(__DIR__.'/fixtures/backup');
        $logger = $this->getService('dotfiles.logger');

        return new Patcher($this->getParameters(), $logger, $this->dispatcher);
    }
}
