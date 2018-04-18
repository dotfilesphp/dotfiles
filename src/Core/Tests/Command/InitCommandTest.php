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
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Tests\Helper\CommandTestCase;
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
    private $parameters;

    /**
     * @var MockObject
     */
    private $process;

    /**
     * @var MockObject
     */
    private $runner;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->runner = $this->createMock(ProcessRunner::class);
        $this->process = $this->createMock(Process::class);
        $this->parameters = $this->createMock(Parameters::class);
        static::cleanupTempDir();
    }

    public function testBackupDirQuestion(): void
    {
        Toolkit::ensureDir(getenv('DOTFILES_HOME_DIR'));
        $tester = $this->getTester('init');
        $tester->setInputs(array(
            null,
            null,
            null,
            null,
            null,
        ));

        $tester->execute(array());
        $output = $tester->getDisplay(array(true));
        $this->assertContains('local backup dir', $output);
        $this->assertContains('your machine name', $output);
        $this->assertContains('installation directory', $output);

        $tester->setInputs(array(
            '/foo/bar',
            null,
            null,
            null,
            null,
        ));
        $tester->execute(array());
        $display = $tester->getDisplay();
        $this->assertContains('Can not find parent directory', $display);
    }

    public function testInitSuccessfully(): void
    {
        $backupDir = $this->getParameters()->get('dotfiles.backup_dir');
        $homeDir = $this->getParameters()->get('dotfiles.home_dir');

        $tester = $this->getTester('init');
        $tester->setInputs(array(
            $backupDir,
            'some-machine',
            null,
            null,
        ));
        $tester->execute(array('command' => 'init'));

        $this->assertFileExists($envFile = $homeDir.'/.dotfiles_profile');

        $contents = file_get_contents($envFile);
        $this->assertContains('some-machine', $contents);
        $this->assertContains($backupDir, $contents);
    }

    protected function configureCommand(): void
    {
        $this->createHomeDirMock(__DIR__.'/fixtures/home');
        $this->command = new InitCommand(null, $this->runner, $this->getParameters());
    }
}
