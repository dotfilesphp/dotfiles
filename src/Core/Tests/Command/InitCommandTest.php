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
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Util\CommandProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Process;

class InitCommandTest extends CommandTestCase
{
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
        static::cleanupTempDir();
    }

    public function testInitSuccessfully(): void
    {
        $repoDir = '/tmp/dotfiles/tests/init';
        $this->configureInitCommand();

        $tester = $this->getTester('init');
        $tester->setInputs(array(
            $repoDir,
            'some-machine',
            null,
            null,
        ));
        $tester->execute(array('command' => 'init'));
        $output = $tester->getDisplay(true);

        $this->assertContains(getenv('HOME'), $output);
        $this->assertDirectoryIsWritable($repoDir);
        $this->assertDirectoryExists(getenv('HOME').'/.dotfiles');
        $this->assertFileExists($envFile = getenv('HOME').'/.dotfiles/.env');

        $contents = file_get_contents($envFile);
        $this->assertContains('some-machine', $contents);
        $this->assertContains($repoDir, $contents);

        // checking repodir
        $this->assertFileExists($repoDir.'/.gitignore');
        $this->assertFileExists($repoDir.'/config/dotfiles.yaml');
        $this->assertFileExists($repoDir.'/src/.gitkeep');
    }

    public function testRepoDirQuestion(): void
    {
        $repoDir = sys_get_temp_dir().'/dotfiles';
        $this->configureInitCommand();
        $tester = $this->getTester('init');
        $tester->setInputs(array(
            null,
            '/foo/bar/hello',
            $repoDir,
            null,
            null,
            null,
        ));

        $tester->execute(array());
        $output = $tester->getDisplay(array(true));
        $this->assertContains('have to define', $output);
        $this->assertContains('/foo/bar', $output);
        $this->assertContains('home directory', $output);
    }

    private function configureInitCommand(): void
    {
        $this->processor->expects($this->any())
            ->method('create')
            ->willReturn($this->process)
        ;
        $this->command = new InitCommand(null, $this->processor);
    }
}
