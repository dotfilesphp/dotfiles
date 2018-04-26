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

use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\RestoreEvent;
use Dotfiles\Core\Processor\Template;
use Dotfiles\Core\Tests\Helper\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RestoreTest.
 *
 * @covers \Dotfiles\Core\Processor\Template
 */
class TemplateTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $dispatcher;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        static::cleanupTempDir();
    }

    public function testRestore()
    {
        $event = new RestoreEvent();
        $template = $this->getTemplateObject();

        $template->onPreRestore($event);
        $template->onRestore($event);

        $files = $event->getFiles();
        $this->assertArrayHasKey('.bashrc', $files);
        $this->assertArrayHasKey('.override', $files);

        $home = $this->getParameters()->get('dotfiles.home_dir');
        $contents = file_get_contents($home.'/.override');
        $this->assertNotContains('should not displayed', $contents);
        $this->assertContains('override', $contents);
    }

    /**
     * @return Template
     */
    private function getTemplateObject()
    {
        static::cleanupTempDir();
        $this->boot();
        $config = $this->getParameters();
        $this->createBackupDirMock(__DIR__.'/fixtures/backup');

        return new Template($config, $this->dispatcher, $this->getService('dotfiles.logger'));
    }
}
