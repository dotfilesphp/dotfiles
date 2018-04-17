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

use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\PHPBrew\Installer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class InstallerTest.
 *
 * @covers \Dotfiles\Plugins\PHPBrew\Installer
 */
class InstallerTest extends \Dotfiles\Core\Tests\Helper\BaseTestCase
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
     * @var string
     */
    private $tempDir;

    public function setUp(): void
    {
        $this->downloader = $this->createMock(Downloader::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tempDir = $this->getParameters()->get('dotfiles.temp_dir');
    }

    public function testRun(): void
    {
        $installer = $this->getSUT();
        $tempDir = $this->tempDir;
        $this->downloader->expects($this->once())
            ->method('run')
            ->with(Installer::DOWNLOAD_URL, $tempDir.'/phpbrew')
            ->will($this->returnCallback(function ($url, $target): void {
                touch($target);
            }))
        ;
        $installer->run();
    }

    public function testRunOnAlreadyInstalled(): void
    {
        $installDir = $this->getParameters()->get('dotfiles.install_dir');
        Toolkit::ensureFileDir($file = $installDir.'/bin/phpbrew');
        touch($file);
        $installer = $this->getSUT();
        $installer->run();
        $this->assertContains('already installed', $this->getDisplay());
    }

    /**
     * @param array $config
     *
     * @return Installer
     *
     * @throws \ReflectionException
     */
    private function getSUT()
    {
        static::cleanupTempDir();

        return new Installer(
            $this->getParameters(),
            $this->downloader,
            $this->logger,
            $this->getService('dotfiles.output'),
            $this->getService(ProcessRunner::class)
        );
    }
}
