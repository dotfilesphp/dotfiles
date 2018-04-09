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

use Dotfiles\Core\Application;
use Dotfiles\Core\Command\ClearCacheCommand;
use Dotfiles\Core\Command\SelfUpdateCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Exceptions\InstallFailedException;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommandTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $downloader;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Application
     */
    private $application;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config = $this->createMock(Config::class);
        $this->downloader = $this->createMock(Downloader::class);
        $this->tempDir = sys_get_temp_dir().'/dotfiles';
        $this->output = $this->createMock(OutputInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->progressBar = new ProgressBar($this->output);
        $this->application = new Application($this->config, $this->input,$this->output);
        static::cleanupTempDir();
    }

    public function createFakeVersionFile($url, $target): void
    {
        if (false !== strpos($url, 'dotfiles.phar.json')) {
            $origin = __DIR__.'/fixtures/dotfiles.phar.json';
        } else {
            $origin = __DIR__.'/fixtures/dotfiles.phar';
        }
        $fs = new Filesystem();
        $fs->copy($origin, $target);

        return;
    }

    public function testExecute(): void
    {
        $tempDir = $this->tempDir;
        $versionFile = $tempDir.'/temp/update/dotfiles.phar.json';
        $pharFile = $tempDir.'/temp/update/test/dotfiles.phar';

        Toolkit::ensureFileDir($versionFile);

        $this->downloader->expects($this->exactly(2))
            ->method('run')
            ->withConsecutive(
                array(SelfUpdateCommand::BASE_URL.'/dotfiles.phar.json', $versionFile),
                array(SelfUpdateCommand::BASE_URL.'/dotfiles.phar', $pharFile)
            )
            ->will(
                $this->returnCallback(array($this, 'createFakeVersionFile'))
            )
        ;

        $cache = $this->createMock(ClearCacheCommand::class);
        $cache->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true)
        ;
        $cache->expects($this->any())->method('getName')->willReturn('clear-cache');
        $cache->expects($this->any())
            ->method('getDefinition')
            ->willReturn(new InputDefinition())
        ;
        $cache->expects($this->any())->method('getAliases')->willReturn(array());

        $cache->expects($this->once())
            ->method('run')
        ;
        $this->application->add($cache);

        $command = $this->getSUT();
        $command->run($this->input, $this->output);
    }

    public function testExecuteOnLatestVersionPhar(): void
    {
        $version = Application::VERSION;
        $versionFile = <<<EOF
{
    "version": "{$version}",
    "branch": "1.0-dev",
    "date": "2018-04-08 06:50:24",
    "sha256": "51ccbace494495e667c9b77bb628bc0ddae590f268524bf644419745e86a07aa  dotfiles.pha"
}
EOF;

        $this->downloader->expects($this->once())
            ->method('run')
            ->will(
                $this->returnCallback(
                    function ($url, $target) use ($versionFile): void {
                        file_put_contents($target, $versionFile, LOCK_EX);
                    }
                )
            )
        ;
        $this->output->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                array($this->stringContains('Start checking new version')),
                array($this->stringContains('You already have latest'))
            )
        ;
        $command = $this->getSUT();
        $command->run($this->input, $this->output);
    }

    public function testExecuteThrowsOnEmptyVersionFile(): void
    {
        $this->downloader->expects($this->once())
            ->method('run')
            ->will(
                $this->returnCallback(
                    function ($url, $target): void {
                        touch($target);
                    }
                )
            )
        ;

        $this->expectException(InstallFailedException::class);
        $this->expectExceptionMessage('Can not parse dotfiles.phar.json file');
        $command = $this->getSUT();
        $command->run($this->input, $this->output);
    }

    private function getSUT()
    {
        $tempDir = $this->tempDir;

        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.temp_dir', $tempDir.'/temp'),
                array('dotfiles.cache_dir', $tempDir.'/cache'),
                array('dotfiles.dry_run', false),
            ))
        ;
        $this->downloader->expects($this->any())
            ->method('getProgressBar')
            ->willReturn($this->progressBar)
        ;

        $command = new SelfUpdateCommand(
            null,
            $this->downloader,
            $this->config
        );

        $command->setApplication($this->application);
        return $command;
    }
}
