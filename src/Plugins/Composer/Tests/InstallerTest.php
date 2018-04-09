<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\Composer\Tests;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\CommandProcessor;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\Composer\Installer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class InstallerTest
 *
 * @package Dotfiles\Plugins\Composer\Tests
 * @covers \Dotfiles\Plugins\Composer\Installer
 */
class InstallerTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $output;

    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $downloader;

    /**
     * @var MockObject
     */
    private $processor;

    private $tempDir;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->output       = $this->createMock(OutputInterface::class);
        $this->config       = $this->createMock(Config::class);
        $this->logger       = $this->createMock(LoggerInterface::class);
        $this->downloader   = $this->createMock(Downloader::class);
        $this->processor    = $this->createMock(CommandProcessor::class);
        $this->tempDir      = sys_get_temp_dir().'/dotfiles/tests/composer';
        static::cleanupTempDir();
    }

    private function getSUT($config = array(), $useDownloader = true)
    {
        $defaults = [
            'installer.sig' => __DIR__.'/fixtures/installer.sig',
            'installer.php' => __DIR__.'/fixtures/installer.php',
        ];
        $config = array_merge($defaults, $config);

        $tempDir = $this->tempDir;


        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['dotfiles.temp_dir',$tempDir.'/temp'],
                ['dotfiles.bin_dir',$tempDir.'/bin'],
                ['composer.file_name','composer.phar']
            ])
        ;

        $this->downloader->expects($this->any())
            ->method('run')
            ->will($this->returnCallback(function($url,$target) use($config){
                Toolkit::ensureFileDir($target);
                $origin = $config['installer.php'];
                if(false!==strpos($target,'composer.sig')){
                    $origin = $config['installer.sig'];
                }
                $fs = new Filesystem();
                $fs->copy($origin,$target);
            }))
        ;

        return new Installer(
            $this->output,
            $this->logger,
            $this->config,
            $this->downloader,
            $this->processor
        );
    }

    public function testRunSuccessfully()
    {
        $process = $this->createMock(Process::class);
        $this->processor->expects($this->once())
            ->method('create')
            ->with($this->stringContains('composer.phar'))
            ->willReturn($process)
        ;

        $process->expects($this->once())
            ->method('run')
            ->will($this->returnCallback(function(){
                touch($this->tempDir.'/bin/composer.phar');
                return 0;
            }))
        ;
        $installer = $this->getSUT();
        $installer->run();
    }

    public function testRunWithFailedSignature()
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Signature Invalid'))
        ;
        $installer = $this->getSUT([
            'installer.php' => __DIR__.'/fixtures/empty.php'
        ]);
        $installer->run();
    }

    public function testRunOnInstalled()
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('Composer already installed'))
        ;
        $file = $this->tempDir.'/bin/composer.phar';
        Toolkit::ensureFileDir($file);
        touch($file);
        $installer = $this->getSUT();
        $installer->run();
    }
}
