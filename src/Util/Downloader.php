<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toni\Dotfiles\Util;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Downloader
{
    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @var string
     */
    private $url;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $targetFile;

    public function __construct(OutputInterface $output,$url,$targetFile)
    {
          $this->output = $output;
          $this->url    = $url;
          $this->targetFile = $targetFile;

          if(!is_dir($dir = dirname($targetFile))){
              mkdir($dir,0755,true);
          }

          $this->createProgressBar();
    }

    public function run()
    {
        $this->createProgressBar();
        $url = $this->url;
        $targetFile = $this->targetFile;
        $this->output->writeln(sprintf('Downloading <info>%s</info> to <info>%s</info>',$url,$targetFile));
        $context = stream_context_create([], ['notification' => [$this, 'handleNotification']]);
        $this->contents = @file_get_contents($url, false, $context);
        $this->output->writeln('Download <comment>finished</comment>');
    }

    public function handleNotification($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax)
    {
        if (STREAM_NOTIFY_REDIRECTED === $notificationCode) {
            $this->createProgressBar();
            return;
        }
        if (STREAM_NOTIFY_FILE_SIZE_IS === $notificationCode) {
            $this->progressBar->start($bytesMax);
        }
        if (STREAM_NOTIFY_PROGRESS === $notificationCode) {
            $this->progressBar->setProgress($bytesTransferred);
        }
        if (STREAM_NOTIFY_COMPLETED === $notificationCode) {
            $this->progressBar->finish($bytesTransferred);
        }
    }

    private function createProgressBar()
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat("Progress: <comment>%percent:3s%%</comment> <info>%estimated:-6s%</info>");
    }
}
