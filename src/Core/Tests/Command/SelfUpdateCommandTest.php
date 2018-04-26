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

use Dotfiles\Core\ApplicationFactory;
use Dotfiles\Core\Command\ClearCacheCommand;
use Dotfiles\Core\Command\SelfUpdateCommand;
use Dotfiles\Core\Exceptions\InstallFailedException;
use Dotfiles\Core\Tests\Helper\CommandTestCase;
use Dotfiles\Core\Util\Downloader;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class SelfUpdateCommandTest.
 *
 * @covers \Dotfiles\Core\Command\SelfUpdateCommand
 */
class SelfUpdateCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $downloader;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->downloader = $this->createMock(Downloader::class);
    }

    public function testExecute(): void
    {
        $tempDir = $this->getParameters()->get('dotfiles.temp_dir');
        $this->downloader->expects($this->exactly(2))
            ->method('run')
            ->will($this->returnCallback(function ($url, $target) use ($tempDir): void {
                if (false !== strpos($target, 'dotfiles.phar.json')) {
                    $target = $tempDir.'/update/dotfiles.phar.json';
                    copy(__DIR__.'/fixtures/dotfiles.phar.json', $target);
                }
                if (false !== strpos($target, 'dotfiles.phar')) {
                    $target = $tempDir.'/update/dotfiles.phar';
                    copy(__DIR__.'/fixtures/dotfiles.phar', $target);
                }
            }))
        ;

        $tester = $this->getTester('selfupdate');
        $tester->execute(array());

        $output = $tester->getDisplay(true);

        $this->assertContains('Begin update into test', $output);
    }

    public function testExecuteWhenInstalled()
    {
        $tempDir = $this->getParameters()->get('dotfiles.temp_dir');
        $this->downloader->expects($this->at(0))
            ->method('run')
            ->will($this->returnCallback(function ($url, $target) use ($tempDir): void {
                copy(__DIR__.'/fixtures/dotfiles.latest.json', $target);
            }))
        ;

        $tester = $this->getTester('selfupdate');
        $tester->execute(array());

        $output = $tester->getDisplay(true);

        $this->assertContains('You already have latest', $output);
    }

    public function testExecuteWithInvalidVersionFile()
    {
        $tempDir = $this->getParameters()->get('dotfiles.temp_dir');
        $this->downloader->expects($this->at(0))
            ->method('run')
            ->will($this->returnCallback(function ($url, $target) use ($tempDir): void {
                $target = $tempDir.'/update/dotfiles.phar.json';
                file_put_contents($target, "\n", LOCK_EX);
            }))
        ;

        $this->expectException(InstallFailedException::class);
        $tester = $this->getTester('selfupdate');
        $tester->execute(array());
    }

    protected function configureCommand(): void
    {
        $logger = $this->getContainer()->get('dotfiles.logger');
        $factory = $this->createMock(ApplicationFactory::class);
        $command = new ClearCacheCommand(null, $this->getParameters(), $logger, $factory);
        $this->getApplication()->add($command);
        $this->command = new SelfUpdateCommand(null, $this->downloader, $this->getParameters());
    }
}
