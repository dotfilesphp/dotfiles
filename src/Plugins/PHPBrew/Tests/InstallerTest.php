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

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\PHPBrew\Installer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallerTest.
 *
 * @covers \Dotfiles\Plugins\PHPBrew\Installer
 */
class InstallerTest extends BaseTestCase
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
     * @var MockObject
     */
    private $logger;

    /**
     * @var MockObject
     */
    private $output;

    private $tempDir;

    public function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->downloader = $this->createMock(Downloader::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->tempDir = sys_get_temp_dir().'/dotfiles';
        static::cleanupTempDir();
    }

    public function testRun(): void
    {
        $installer = $this->getSUT();
        $tempDir = $this->tempDir;
        $this->downloader->expects($this->once())
            ->method('run')
            ->with(Installer::DOWNLOAD_URL, $tempDir.'/temp/phpbrew')
            ->will($this->returnCallback(function () use ($tempDir): void {
                touch($tempDir.'/temp/phpbrew');
            }))
        ;
        $installer->run();
    }

    public function testRunOnAlreadyInstalled(): void
    {
        Toolkit::ensureFileDir($file = $this->tempDir.'/bin/phpbrew');
        touch($file);
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('already installed'))
        ;
        $installer = $this->getSUT();
        $installer->run();
    }

    public function testRunWhenFileDownloaded(): void
    {
        Toolkit::ensureFileDir($file = $this->tempDir.'/temp/phpbrew');
        touch($file);
        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('file already downloaded'))
        ;
        $installer = $this->getSUT();
        $installer->run();
    }

    /**
     * @param array $config
     *
     * @return Installer
     *
     * @throws \ReflectionException
     */
    private function getSUT(
        $config = array()
    ) {
        $tempDir = $this->tempDir;
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.dry_run', false),
                array('dotfiles.install_dir', $tempDir.'/install'),
                array('dotfiles.temp_dir', $tempDir.'/temp'),
                array('dotfiles.bin_dir', $tempDir.'/bin'),
            ))
        ;

        return new Installer(
            $this->config,
            $this->downloader,
            $this->logger,
            $this->output
        );
    }
}
