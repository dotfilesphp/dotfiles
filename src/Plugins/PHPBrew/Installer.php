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

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Installer
{
    public const DOWNLOAD_URL = 'https://github.com/phpbrew/phpbrew/raw/master/phpbrew';

    /**
     * @var Parameters
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

    /**
     * Installer constructor.
     *
     * @param \Dotfiles\Core\DI\Parameters $config
     * @param Downloader                   $downloader
     * @param LoggerInterface              $logger
     * @param OutputInterface              $output
     */
    public function __construct(
        Parameters $config,
        Downloader $downloader,
        LoggerInterface $logger,
        OutputInterface $output
    ) {
        $this->config = $config;
        $this->downloader = $downloader;
        $this->logger = $logger;
        $this->output = $output;
    }

    /**
     * @param $message
     * @param array $context
     */
    public function debug($message, array $context = array()): void
    {
        $this->logger->debug('phpbrew: '.$message, $context);
    }

    /**
     * Run PHPBrew installation.
     *
     * @param bool $force
     */
    public function run(bool $force = false): void
    {
        $config = $this->config;
        $toFile = $config->get('dotfiles.temp_dir').DIRECTORY_SEPARATOR.'phpbrew';
        $installToFile = $config->get('dotfiles.bin_dir').DIRECTORY_SEPARATOR.'phpbrew';
        Toolkit::ensureFileDir($toFile);

        if (is_file($installToFile) && !$force) {
            $this->output->writeln('<comment>PHPBrew</comment> already installed, skipping');

            return;
        }

        if (!is_file($toFile)) {
            $downloader = $this->downloader;
            $downloader->run(static::DOWNLOAD_URL, $toFile);
        } else {
            $this->debug('file already downloaded, skipping');
        }

        $dryRun = $config->get('dotfiles.dry_run');
        if (!$dryRun) {
            $fs = new Filesystem();
            $fs->chmod($toFile, 0755);
            $fs->copy($toFile, $installToFile, false);
            $cmd = array(
                $installToFile,
                'init',
            );
            $process = new Process($cmd);
            $process->run(function ($type, $buffer): void {
                //@codeCoverageIgnoreStart
                if (Process::ERR === $type) {
                    $this->logger->error('phpbrew: '.$buffer);
                } else {
                    $this->debug($buffer);
                }
                //@codeCoverageIgnoreEnd
            });
        }
    }
}
