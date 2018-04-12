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

namespace Dotfiles\Core\Processor;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

class Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $section;

    public function __construct(
        Config $config,
        Dispatcher $dispatcher,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    public function run(): void
    {
        $machine = getenv('DOTFILES_MACHINE_NAME');
        $config = $this->config;
        $sections = array('defaults', $machine);

        foreach ($sections as $name) {
            $this->section = $name;
            $this->debug("Processing <comment>$name</comment> section.");
            $backupDir = $config->get('dotfiles.backup_dir')."/src/{$name}";
            $this->processHomeDir($backupDir.'/home');
            $this->debug('');
            $this->debug('');
        }

        $this->section = 'patch';
        $this->debug('applying patch');
    }

    private function debug($message, array $context = array()): void
    {
        $message = sprintf('[%s] %s', $this->section, $message);
        $this->logger->info($message, $context);
    }

    private function processHomeDir($homeDir): void
    {
        $targetDir = $this->config->get('dotfiles.home_dir');
        if (!is_dir($homeDir)) {
            $this->debug("Home directory <comment>$homeDir</comment> not found");

            return;
        }

        $files = Finder::create()
            ->in($homeDir)
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->files()
        ;

        $fs = new Filesystem();
        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($files as $file) {
            $target = Toolkit::ensureDotPath($file->getRelativePathname());
            $fs->copy($file->getRealPath(), $targetDir.DIRECTORY_SEPARATOR.$target);
            $this->debug('+restore: <comment>'.$target.'</comment>');
        }
    }
}
