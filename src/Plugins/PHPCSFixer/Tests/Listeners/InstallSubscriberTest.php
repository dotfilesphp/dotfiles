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

namespace Dotfiles\Plugins\PHPCSFixer\Tests\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\PHPCSFixer\Listeners\InstallSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallSubscriberTest extends BaseTestCase
{
    public function testOnInstallEvent(): void
    {
        $tempDir = sys_get_temp_dir().'/dotfiles';
        $installDir = sys_get_temp_dir().'/dotfiles/tests/install';
        $installFile = 'phpcs';

        Toolkit::ensureDir($installDir);

        $config = $this->createMock(Config::class);
        $downloader = $this->createMock(Downloader::class);
        $event = $this->createMock(InstallEvent::class);
        $logger = $this->createMock(LoggerInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.temp_dir', $tempDir),
                array('dotfiles.bin_dir', $installDir),
                array('phpcs.file_name', $installFile),
            ))
        ;
        $downloader->expects($this->once())
            ->method('run')
            ->with(InstallSubscriber::URL, $tempDir.'/phpcs/php-cs-fixer.phar', false)
        ;

        $output->expects($this->once())
            ->method('writeln')
            ->with($this->stringContains('PHP-CS-Fixer already installed'))
        ;
        $sut = new InstallSubscriber($config, $downloader, $output, $logger);
        $sut->onInstallEvent($event);

        // test with already installed
        touch($installDir.'/'.$installFile);
        $sut->onInstallEvent($event);
    }
}
