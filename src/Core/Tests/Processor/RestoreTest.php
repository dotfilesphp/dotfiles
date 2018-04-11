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

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Processor\Restore;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class RestoreTest.
 *
 * @covers \Dotfiles\Core\Processor\Restore
 */
class RestoreTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $dispatcher;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var StreamOutput
     */
    private $output;

    private $stream;

    /**
     * @var string
     */
    private $tempDir;

    public function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tempDir = sys_get_temp_dir().'/dotfiles/tests/restore';
        $this->stream = fopen('php://memory', 'w');
        $this->output = new StreamOutput($this->stream);
    }

    public function getTestExecuteSuccessfully()
    {
        return array(
            array('.ssh/id_rsa', true),
            array('.ssh/id_rsa.pub', true),
            array('.bashrc', true),
            array('.dotfiles', true),
            array('.no-dot-prefix', true),
            array('+patch .bashrc'),
            array('[init] +hooks defaults/hooks/pre-restore'),
            array('[init] +hooks defaults/hooks/pre-restore.php'),
            array('[init] +hooks dotfiles/hooks/pre-restore.bash'),
            array('[init] +hooks dotfiles/hooks/pre-restore.php'),
        );
    }

    public function getTestHook()
    {
        return array(
            array('[init] -hooks not executable: defaults/hooks/pre-restore.bash'),
            array('defaults pre-restore bash hooks'),
            array('defaults pre-restore php hooks'),
            array('machine pre-restore bash hooks'),
            array('machine pre-restore php hooks'),
            array('defaults post-restore bash'),
            array('machine post-restore bash'),
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
            $restore = $this->getRestoreObject('restore');
            $restore->run();
            $hasRun = true;
            $output = $this->getDisplay();
        }
        $this->assertContains($file, $output);

        if ($ensureFile) {
            $this->assertFileExists($this->tempDir.'/home/'.$file);
        }
    }

    /**
     * @param string $expected
     * @dataProvider getTestHook
     */
    public function testHook(string $expected): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                PatchEvent::NAME,
                $this->isInstanceOf(PatchEvent::class)
            )
        ;

        $restore = $this->getRestoreObject('restore');
        $restore->run();
        $output = $this->getDisplay();

        $this->assertContains($expected, $output);
    }

    public function testPatch(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                PatchEvent::NAME,
                $this->isInstanceOf(PatchEvent::class)
            )
        ;

        $restore = $this->getRestoreObject('restore');
        $restore->run();
        $output = $this->getDisplay();

        $this->assertContains('applying patch', $output);

        $contents = file_get_contents($this->tempDir.'/home/.bashrc');
        $this->assertContains('# bashrc default files', $contents);
        $this->assertContains('#patch defaults', $contents);
        $this->assertContains('#patch machine', $contents);
    }

    private function getDisplay()
    {
        rewind($this->output->getStream());
        $display = stream_get_contents($this->output->getStream());

        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }

    /**
     * @return Restore
     */
    private function getRestoreObject()
    {
        $tempDir = $this->tempDir;
        Toolkit::ensureDir($tempDir);
        $this->config
            ->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.home_dir', $tempDir.'/home'),
                array('dotfiles.backup_dir', __DIR__.'/fixtures/backup'),
            ))
        ;

        return new Restore($this->config, $this->dispatcher, $this->output, $this->logger);
    }
}
