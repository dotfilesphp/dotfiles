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

namespace Dotfiles\Plugins\Composer\Tests;

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\Composer\Installer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class InstallerTest.
 *
 * @covers \Dotfiles\Plugins\Composer\Installer
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
    private $runner;

    private $tempDir;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config = $this->createMock(Parameters::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->downloader = $this->createMock(Downloader::class);
        $this->runner = $this->createMock(ProcessRunner::class);
        static::cleanupTempDir();
    }

    public function testRunOnInstalled(): void
    {
        $binDir = $this->getParameters()->get('dotfiles.bin_dir');
        $file = $binDir.'/composer';
        Toolkit::ensureFileDir($file);
        touch($file);
        $installer = $this->getSUT();
        $installer->run();
        $display = $this->getDisplay();

        $this->assertContains('Composer already installed', $display);
    }

    public function testRunSuccessfully(): void
    {
        $installFile = $this->getParameters()->get('dotfiles.bin_dir').'/composer';
        if (is_file($installFile)) {
            unlink($installFile);
        }
        $this->runner->expects($this->once())
            ->method('run')
            ->with($this->stringContains('composer'))
            ->will($this->returnCallback(function () use ($installFile) {
                Toolkit::ensureFileDir($installFile);
                touch($installFile);
            }))
        ;
        $installer = $this->getSUT();
        $installer->run();
    }

    public function testRunWithFailedSignature(): void
    {
        static::cleanupTempDir();
        $installer = $this->getSUT(array(
            'installer.php' => __DIR__.'/fixtures/empty.php',
        ));
        $installer->run();
        $this->assertContains('Signature Invalid', $this->getDisplay());
    }

    private function getSUT($config = array(), $useDownloader = true)
    {
        $defaults = array(
            'installer.sig' => __DIR__.'/fixtures/installer.sig',
            'installer.php' => __DIR__.'/fixtures/installer.php',
        );
        $config = array_merge($defaults, $config);

        $this->downloader->expects($this->any())
            ->method('run')
            ->will($this->returnCallback(function ($url, $target) use ($config): void {
                Toolkit::ensureFileDir($target);
                $origin = $config['installer.php'];
                if (false !== strpos($target, 'composer.sig')) {
                    $origin = $config['installer.sig'];
                }
                $fs = new Filesystem();
                $fs->copy($origin, $target);
            }))
        ;

        return new Installer(
            $this->getService('dotfiles.output'),
            $this->getService('dotfiles.logger'),
            $this->getParameters(),
            $this->downloader,
            $this->runner
        );
    }
}
