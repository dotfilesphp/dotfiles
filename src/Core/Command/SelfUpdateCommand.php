<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Util\Downloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SelfUpdateCommand extends Command implements CommandInterface
{
    const BASE_URL = 'https://raw.githubusercontent.com/kilip/dotfiles/phar';

    private $version;

    private $branchAlias;

    private $date;

    private $versionFile;

    private $pharFile;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $cacheDir;

    public function configure()
    {
        $this->setName('self-update');
    }

    /**
     * @param string $tempDir
     *
     * @return SelfUpdateCommand
     */
    public function setTempDir(string $tempDir): self
    {
        $this->tempDir = $tempDir;

        return $this;
    }

    /**
     * @param string $cacheDir
     *
     * @return SelfUpdateCommand
     */
    public function setCacheDir(string $cacheDir): self
    {
        $this->cacheDir = $cacheDir;

        return $this;
    }

    /**
     * @param Downloader $downloader
     */
    public function setDownloader(Downloader $downloader): void
    {
        $this->downloader = $downloader;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start checking new version');
        $url = static::BASE_URL.'/dotfiles.phar.json';
        $versionFile = $this->tempDir.'/update/dotfiles.phar.json';
        $downloader = $this->downloader;
        $downloader->run($url, $versionFile);
        $contents = file_get_contents($versionFile);
        if ('' === trim($contents)) {
            throw new \Exception('Can not parse dotfiles.phar.json file');
        }
        $json = json_decode($contents, true);
        $this->versionFile = $versionFile;
        $this->version = $json['version'];
        $this->branchAlias = $json['branch'];
        $this->date = $json['date'];

        if (Application::VERSION !== $this->version) {
            $output->writeln("Begin update into <comment>{$this->version}</comment>");
            $this->doUpdate($output);
        } else {
            $output->writeln('You already have latest <comment>dotfiles</comment> version');
        }
    }

    private function doUpdate(OutputInterface $output)
    {
        $fs = new Filesystem();
        $tempDir = $this->tempDir.'/update/'.DIRECTORY_SEPARATOR.$this->version;
        $fs->copy($this->versionFile, $tempDir.DIRECTORY_SEPARATOR.'VERSION');

        $targetFile = $tempDir.DIRECTORY_SEPARATOR.'dotfiles.phar';
        if (!is_file($targetFile)) {
            $url = static::BASE_URL.'/dotfiles.phar';
            $downloader = $this->downloader;
            $downloader->getProgressBar()->setFormat('Download <comment>dotfiles.phar</comment>: <comment>%percent:3s%%</comment> <info>%estimated:-6s%</info>');
            $downloader->run($url, $targetFile);
        }

        $this->pharFile = $targetFile;
        $cacheDir = $this->cacheDir;
        // copy current phar into new dir
        $current = \Phar::running(false);
        $output->writeln($current);
        if (is_file($current)) {
            $override = array('override' => true);
            $backup = $cacheDir.'/dotfiles_old.phar';
            $fs->copy($current, $backup, $override);
            $fs->copy($this->pharFile, $current, $override);
            $output->writeln('Your <comment>dotfiles.phar</comment> is updated.');
        }
    }
}
