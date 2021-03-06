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
use Dotfiles\Core\Processor\Patcher;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Tests\Helper\LoggerOutputTrait;
use PHPUnit\Framework\MockObject\MockObject;

class PatcherTest extends BaseTestCase
{
    use LoggerOutputTrait;

    /**
     * @var MockObject
     */
    private $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->setUpLogger();
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
        $patch = $this->getPatcherObject();
        $patch->run();
        $output = $this->getDisplay();

        $this->assertContains('applying patch', $output);
        $homeDir = $this->getConfig()->get('dotfiles.home_dir');

        $this->assertFileExists($file = $homeDir.'/.bashrc');
        $contents = file_get_contents($file);
        $this->assertContains('#patch defaults', $contents);
        $this->assertContains('#patch machine', $contents);
    }

    private function getPatcherObject()
    {
        $this->getConfig()->set('dotfiles.backup_dir', __DIR__.'/fixtures/backup');

        return new Patcher($this->getConfig(), $this->logger, $this->dispatcher);
    }
}
