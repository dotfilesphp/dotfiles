<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Util;

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

    /**
     * @var int
     */
    private $bytesMax;

    private $hasError = false;

    private $contents;

    public function __construct(OutputInterface $output)
    {
          $this->output = $output;
    }

    public function run($url,$targetFile)
    {
        if(!is_dir($dir = dirname($targetFile))){
            mkdir($dir,0755,true);
        }

        $this->createProgressBar();
        $this->hasError = false;
        $this->output->writeln(sprintf('Downloading <info>%s</info> to <info>%s</info>',$url,$targetFile));
        $this->output->writeln("");
        $context = stream_context_create([], ['notification' => [$this, 'handleNotification']]);
        set_error_handler([$this,'handleError']);
        $this->contents = file_get_contents($url, false, $context);
        restore_error_handler();
        if($this->hasError){
            throw new \RuntimeException('Failed to download '.$url);
        }
        file_put_contents($targetFile, $this->contents, LOCK_EX);
        $this->output->writeln("");
        $this->output->writeln('Download <comment>finished</comment>');
    }

    public function handleError($bar,$message)
    {
        $this->hasError = true;
        $this->output->writeln("<comment>Error:</comment>\n<info>${message}</info>\n");
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
                $this->$bytesMax = $bytesMax;
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

    private function createProgressBar()
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat("Progress: <comment>%percent:3s%%</comment> <info>%estimated:-6s%</info>");
    }
}
