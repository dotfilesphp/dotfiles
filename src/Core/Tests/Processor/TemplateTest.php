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
use Dotfiles\Core\Processor\Template;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Tests\Helper\LoggerOutputTrait;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RestoreTest.
 *
 * @covers \Dotfiles\Core\Processor\Template
 */
class TemplateTest extends BaseTestCase
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
        static::cleanupTempDir();
    }

    public function getTestExecuteSuccessfully()
    {
        return array(
            array('.ssh/id_rsa', true),
            array('.ssh/id_rsa.pub', true),
            array('.bashrc', true),
            array('.no-dot-prefix', true),
        );
    }

    /**
     * @param string $file
     * @param string $type
     * @dataProvider getTestExecuteSuccessfully
     */
    public function testExecuteSuccessfully(string $file, $ensureFile = false): void
    {
        static $hasRun = false, $output;
        if (!$hasRun) {
            $restore = $this->getTemplateObject('restore');
            $restore->run();
            $hasRun = true;
            $output = $this->getDisplay();
        }
        $restore = $this->getTemplateObject('restore');
        $restore->run();
        $this->assertContains($file, $output);

        $tempDir = $this->getParameters()->get('dotfiles.home_dir');
        if ($ensureFile) {
            $this->assertFileExists($tempDir.DIRECTORY_SEPARATOR.$file);
        }
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

        return new Template($config, $this->dispatcher, $this->logger);
    }
}
