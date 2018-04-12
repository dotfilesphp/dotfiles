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

namespace Dotfiles\Core\Tests\Command;

use Dotfiles\Core\Command\InitCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Util\CommandProcessor;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Process;

/**
 * Class InitCommandTest.
 *
 * @covers \Dotfiles\Core\Command\InitCommand
 */
class InitCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $process;

    /**
     * @var MockObject
     */
    private $processor;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->processor = $this->createMock(CommandProcessor::class);
        $this->process = $this->createMock(Process::class);
        $this->config = $this->createMock(Config::class);
        static::cleanupTempDir();
    }

    public function testBackupDirQuestion(): void
    {
        $tester = $this->getTester('init');
        $tester->setInputs(array(
            null,
            null,
            null,
            null,
        ));

        $tester->execute(array());
        $output = $tester->getDisplay(array(true));
        $this->assertContains('local backup dir', $output);
        $this->assertContains('your machine name', $output);
    }

    public function testInitSuccessfully(): void
    {
        $backupDir = '/tmp/dotfiles/tests/init';
        Toolkit::ensureDir(dirname($backupDir));

        $tester = $this->getTester('init');
        $tester->setInputs(array(
            $backupDir,
            'some-machine',
            null,
        ));
        $tester->execute(array('command' => 'init'));

        $this->assertDirectoryIsWritable($backupDir);
        $this->assertDirectoryExists(getenv('HOME').'/.dotfiles');
        $this->assertFileExists($envFile = getenv('HOME').'/.dotfiles/.env');

        $contents = file_get_contents($envFile);
        $this->assertContains('some-machine', $contents);
        $this->assertContains($backupDir, $contents);

        // checking backupDir
        $this->assertFileExists($backupDir.'/.gitignore');
        $this->assertFileExists($backupDir.'/config/dotfiles.yaml');
        $this->assertFileExists($backupDir.'/src/.gitkeep');
    }

    protected function configureCommand(): void
    {
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.home_dir', sys_get_temp_dir().'/dotfiles/home'),
            ))
        ;
        $this->processor->expects($this->any())
            ->method('create')
            ->willReturn($this->process)
        ;
        $this->command = new InitCommand(null, $this->processor, $this->config);
    }
}
