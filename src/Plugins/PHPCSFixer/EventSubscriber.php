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

namespace Dotfiles\Plugins\PHPCSFixer;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public const URL = 'http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(Config $config, Downloader $downloader, OutputInterface $output, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->downloader = $downloader;
        $this->output = $output;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'dotfiles.install' => 'onInstallEvent',
        );
    }

    public function onInstallEvent(PatchEvent $event): void
    {
        $config = $this->config;
        $downloader = $this->downloader;
        $dryRun = $config->get('dotfiles.dry_run');
        $tempDir = $config->get('dotfiles.temp_dir');
        $targetFile = $tempDir.'/phpcs/php-cs-fixer.phar';
        $installDir = $config->get('dotfiles.bin_dir');
        $installFile = $installDir.DIRECTORY_SEPARATOR.$config->get('phpcs.file_name');

        if (is_file($installFile)) {
            $this->output->writeln('PHP-CS-Fixer already installed, skipping');

            return;
        }

        if (!is_file($targetFile)) {
            $downloader->run(static::URL, $targetFile, $dryRun);
        }

        if (is_file($targetFile)) {
            $fs = new Filesystem();
            $fs->copy($targetFile, $installFile);
            $fs->chmod($installFile, 0755);
            $this->output->writeln('PHP-CS-Fixer installed to: <comment>'.$installFile.'</comment>');
        }
    }
}
