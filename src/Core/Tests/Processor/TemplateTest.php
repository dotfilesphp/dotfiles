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
use Dotfiles\Core\Util\Toolkit;
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

    /**
     * @var string
     */
    private $tempDir;

    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->tempDir = sys_get_temp_dir().'/dotfiles/tests/restore';
        $this->setUpLogger();
    }

    public function getTestExecuteSuccessfully()
    {
        return array(
            array('.ssh/id_rsa', true),
            array('.ssh/id_rsa.pub', true),
            array('.bashrc', true),
            array('.dotfiles', true),
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

        if ($ensureFile) {
            $this->assertFileExists($this->tempDir.'/home/'.$file);
        }
    }

    /**
     * @return Template
     */
    private function getTemplateObject()
    {
        $tempDir = $this->tempDir;
        Toolkit::ensureDir($tempDir);

        $config = $this->getParameters();
        $config->set('dotfiles.home_dir', $tempDir.'/home');
        $config->set('dotfiles.backup_dir', __DIR__.'/fixtures/backup');

        return new Template($config, $this->dispatcher, $this->logger);
    }
}
