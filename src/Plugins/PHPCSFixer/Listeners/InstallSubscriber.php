<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\PHPCSFixer\Listeners;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Util\Downloader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InstallSubscriber implements EventSubscriberInterface
{
    const URL = 'http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar';
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Downloader
     */
    private $downloader;

    public static function getSubscribedEvents()
    {
        return [
            InstallEvent::NAME => 'onInstallEvent'
        ];
    }

    public function __construct(Config $config, Downloader $downloader)
    {
        $this->config       = $config;
        $this->downloader   = $downloader;
    }

    public function onInstallEvent(InstallEvent $event)
    {
        $config = $this->config;
        $downloader = $this->downloader;
        $dryRun = $event->isDryRun();
        $tempDir = $config->get('dotfiles.temp_dir');
        $targetFile = $tempDir.'/phpcs/php-cs-fixer.phar';
        if(!is_file($targetFile)){
            $downloader->run(static::URL,$targetFile,$dryRun);
        }
    }
}
