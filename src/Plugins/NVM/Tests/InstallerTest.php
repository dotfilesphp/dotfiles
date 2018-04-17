<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM\Tests;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\NVM\Installer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Process;

/**
 * Class InstallerTest.
 *
 * @covers \Dotfiles\Plugins\NVM\Installer
 */
class InstallerTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $dispatcher;

    /**
     * @var MockObject
     */
    private $downloader;

    /**
     * @var MockObject
     */
    private $process;

    /**
     * @var MockObject
     */
    private $runner;

    public function setUp()
    {
        $this->process = $this->createMock(Process::class);
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->downloader = $this->createMock(Downloader::class);
        $this->runner = $this->createMock(ProcessRunner::class);
    }

    public function testGetBashPatch()
    {
        $installDir = $this->getParameters()->get('nvm.install_dir');
        $this->assertContains($installDir, $this->getInstallerObject()->getBashPatch());
    }

    public function testInstallSuccessfully()
    {
        // tests download installer script
        $contents = file_get_contents(__DIR__.'/fixtures/git-ls-remote.txt');
        $target = $this->getParameters()->get('nvm.temp_dir').'/versions.txt';
        $callback = function () use ($contents,$target) {
            file_put_contents($target, $contents, LOCK_EX);
        };
        $this->runner->expects($this->at(0))
            ->method('run')
            ->willReturnCallback($callback);

        $this->downloader->expects($this->at(0))
            ->method('run')
            ->with('https://raw.githubusercontent.com/creationix/nvm/v0.33.9/install.sh')
            ->will($this->returnCallback(function ($url, $target) {
                Toolkit::ensureFileDir($target);
                touch($target);
            }))
        ;

        // test execute script
        $this->runner->expects($this->at(1))
            ->method('run')
            ->with(
                $this->stringContains('bash ./var/temp/nvm/installer.sh')
            )
            ->willReturn($this->process)
        ;

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Constant::EVENT_PATCH)
        ;

        $installer = $this->getInstallerObject();
        $installer->install();
    }

    public function testWhenDownloadInstallScriptFailed()
    {
        $contents = file_get_contents(__DIR__.'/fixtures/git-ls-remote.txt');
        $target = $this->getParameters()->get('nvm.temp_dir').'/versions.txt';
        $callback = function () use ($contents,$target) {
            file_put_contents($target, $contents, LOCK_EX);
        };
        $this->runner->expects($this->any())
            ->method('run')
            ->willReturnCallback($callback);

        $this->downloader->expects($this->once())
            ->method('run')
            ->willThrowException(new \RuntimeException('some exception messages'))
        ;

        $installer = $this->getInstallerObject();
        $installer->install();
    }

    private function getInstallerObject()
    {
        $parameters = $this->getService('dotfiles.parameters');

        return new Installer($parameters, $this->dispatcher, $this->downloader, $this->runner, $this->getService('dotfiles.output'));
    }
}
