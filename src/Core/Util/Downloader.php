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

namespace Dotfiles\Core\Util;

use Dotfiles\Core\Config\Config;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Downloader
{
    /**
     * @var int
     */
    private $bytesMax;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var bool
     */
    private $hasError = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(OutputInterface $output, LoggerInterface $logger, Config $config)
    {
        $this->output = $output;
        $this->logger = $logger;
        $this->progressBar = new ProgressBar($output);
        $this->config = $config;
    }

    /**
     * @return ProgressBar
     */
    public function getProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }

    public function handleError($bar, $message): void
    {
        $this->hasError = true;
        $this->output->writeln("<comment>Error:</comment>\n<info>${message}</info>\n");
    }

    public function handleNotification($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax): void
    {
        switch ($notificationCode) {
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
                // handle error here
                break;
            case STREAM_NOTIFY_REDIRECTED:
                $this->progressBar->clear();

                break;
            case STREAM_NOTIFY_FILE_SIZE_IS:
                $this->progressBar->start($bytesMax);
                $this->bytesMax = $bytesMax;

                break;
            case STREAM_NOTIFY_PROGRESS:
                $this->progressBar->setProgress($bytesTransferred);

                break;
            case STREAM_NOTIFY_COMPLETED:
                $this->progressBar->setProgress($bytesMax);
                $this->progressBar->clear();

                break;
        }
    }

    public function run($url, $targetFile): void
    {
        $dryRun = $this->config->get('dotfiles.dry_run');
        $fullName = basename($targetFile);
        $this->progressBar->setFormat("Download <comment>$fullName</comment>: <comment>%percent:3s%%</comment> <info>%estimated:-6s%</info>");

        Toolkit::ensureFileDir($targetFile);
        $this->hasError = false;
        $this->logger->debug(sprintf('Downloading <info>%s</info> to <info>%s</info>', $url, $targetFile));
        if (!$dryRun) {
            $context = stream_context_create(array(), array('notification' => array($this, 'handleNotification')));
            set_error_handler(array($this, 'handleError'));
            $this->contents = file_get_contents($url, false, $context);
            restore_error_handler();
            if ($this->hasError) {
                throw new \RuntimeException('Failed to download '.$url);
            }
            $this->output->writeln('');
            file_put_contents($targetFile, $this->contents, LOCK_EX);
        }
        $this->logger->debug('Download <comment>finished</comment>');
    }
}
