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
        file_put_contents($targetFile, $this->contents, LOCK_EX);
        $this->output->writeln("");
        $this->output->writeln('Download <comment>finished</comment>');
    }

    public function handleNotification($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax)
    {
        switch($notificationCode){
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
                // handle error here
                break;
            case STREAM_NOTIFY_REDIRECTED:
                $this->createProgressBar();
                break;
            case STREAM_NOTIFY_FILE_SIZE_IS:
                $this->progressBar->start($bytesMax);
                break;
            case STREAM_NOTIFY_PROGRESS:
                $this->progressBar->setProgress($bytesTransferred);
                break;
            case STREAM_NOTIFY_COMPLETED:
                $this->progressBar->setProgress($bytesTransferred);
                $this->progressBar->finish();
                break;
        }
    }

    private function createProgressBar()
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat("Progress: <comment>%percent:3s%%</comment> <info>%estimated:-6s%</info>");
    }
}
