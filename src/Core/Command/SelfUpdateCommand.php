<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Util\Config;
use Dotfiles\Core\Application;
use Dotfiles\Core\Util\Downloader;

class SelfUpdateCommand extends Command implements CommandInterface
{
    const BASE_URL = 'https://raw.githubusercontent.com/kilip/dotfiles/phar';

    private $version;

    private $branchAlias;

    private $date;

    private $versionFile;

    private $pharFile;

    public function configure()
    {
        $this->setName('self-update');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::create();
        $url = static::BASE_URL.'/VERSION';
        $tempDir = $config->getTempDir('update');
        $versionFile = $tempDir.'/VERSION';
        $downloader = new Downloader($output,$url,$versionFile);
        $downloader->run();
        $contents = file_get_contents($versionFile);
        if(trim($contents)===""){
            throw new \Exception('Can not parse VERSION file');
        }
        $exp = explode(' ',$contents);
        $this->versionFile = $versionFile;
        $this->version = $exp[0];
        $this->branchAlias = $exp[1];
        $this->date = $exp[2];
        if(Application::VERSION !== $this->version){
            $this->doUpdate($input,$output);
        }else{
            $output->writeln('You already have latest <comment>dotfiles</comment> version');
        }
    }

    private function doUpdate(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $config = Config::create();
        $tempDir = $config->getTempDir('update'.DIRECTORY_SEPARATOR.$this->version);
        $fs->copy($this->versionFile,$tempDir.DIRECTORY_SEPARATOR.'VERSION');

        $targetFile = $tempDir.DIRECTORY_SEPARATOR.'dotfiles.phar';
        if(!is_file($targetFile)){
            $url = static::BASE_URL.'/dotfiles.phar';
            $downloader = new Downloader($output,$url,$targetFile);
            $downloader->run();
        }

        $this->pharFile = $targetFile;
        $cacheDir = $config->getCacheDir();
        // copy current phar into new dir
        $current = \Phar::running(false);
        $output->writeln($current);
        if(is_file($current)){
            $override = ['override' => true];
            $backup = $cacheDir.'/dotfiles_old.phar';
            $fs->copy($current,$backup,$override);
            $fs->copy($this->pharFile,$current,$override);
            $output->writeln('Your <comment>dotfiles.phar</comment> is updated.');
        }

    }
}
