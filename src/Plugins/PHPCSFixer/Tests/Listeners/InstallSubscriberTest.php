<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\PHPCSFixer\Tests\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Plugins\PHPCSFixer\Listeners\InstallSubscriber;

class InstallSubscriberTest extends BaseTestCase
{
    public function testOnInstallEvent()
    {
        $tempDir = sys_get_temp_dir().'/dotfiles';
        $config = $this->createMock(Config::class);
        $downloader = $this->createMock(Downloader::class);
        $event = $this->createMock(InstallEvent::class);
        $event->expects($this->once())
            ->method('isDryRun')
            ->willReturn(false)
        ;

        $config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['dotfiles.temp_dir',$tempDir]
            ])
        ;
        $downloader->expects($this->once())
            ->method('run')
            ->with(InstallSubscriber::URL,$tempDir.'/phpcs/php-cs-fixer.phar',false)
        ;

        $sut = new InstallSubscriber($config,$downloader);
        $sut->onInstallEvent($event);
    }
}
