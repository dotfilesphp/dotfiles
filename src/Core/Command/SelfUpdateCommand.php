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

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Exceptions\InstallFailedException;
use Dotfiles\Core\Util\Downloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SelfUpdateCommand extends Command implements CommandInterface
{
    public const BASE_URL = 'https://raw.githubusercontent.com/dotfilesphp/dotfiles/phar';

    /**
     * @var string
     */
    private $branchAlias;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $date;

    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var string
     */
    private $pharFile;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionFile;

    public function __construct(
        ?string $name = null,
        Downloader $downloader,
        Config $config
    ) {
        parent::__construct($name);

        $this->config = $config;
        $this->downloader = $downloader;
        $this->tempDir = $config->get('dotfiles.temp_dir');
        $this->cacheDir = $config->get('dotfiles.cache_dir');
    }

    protected function configure(): void
    {
        $this
            ->setName('self-update')
            ->setAliases(array('selfupdate'))
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws InstallFailedException when version file is invalid
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $config = $this->config;
        $tempDir = $config->get('dotfiles.temp_dir');

        $output->writeln('Start checking new version');
        $url = static::BASE_URL.'/dotfiles.phar.json';
        $versionFile = $tempDir.'/update/dotfiles.phar.json';
        $downloader = $this->downloader;
        $downloader->run($url, $versionFile);
        $contents = file_get_contents($versionFile);
        if ('' === trim($contents)) {
            throw new InstallFailedException('Can not parse dotfiles.phar.json file');
        }
        $json = json_decode($contents, true);

        $this->versionFile = $versionFile;
        $this->version = $json['version'];
        $this->branchAlias = $json['branch'];
        $this->date = $json['date'];

        if (Application::VERSION !== $this->version) {
            $output->writeln("Begin update into <comment>{$this->version}</comment>");
            $this->doUpdate($output);
            $this->getApplication()->get('clear-cache')->run($input, $output);
        } else {
            $output->writeln('You already have latest <comment>dotfiles</comment> version');
        }
    }

    private function doUpdate(OutputInterface $output): void
    {
        $fs = new Filesystem();
        $tempDir = $this->tempDir.'/update/'.$this->version;
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
        // we can't coverage or test phar environment
        //@codeCoverageIgnoreStart
        $current = \Phar::running(false);
        $output->writeln($current);
        if (is_file($current)) {
            $override = array('override' => true);
            $backup = $cacheDir.'/dotfiles_old.phar';
            $fs->copy($current, $backup, $override);
            $fs->copy($this->pharFile, $current, $override);
            $output->writeln('Your <comment>dotfiles.phar</comment> is updated.');
        }
        //@codeCoverageIgnoreEnd
    }
}
