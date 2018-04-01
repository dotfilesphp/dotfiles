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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Util\Downloader;
use Dotfiles\Core\Util\Config;

class ComposerCommand extends Command implements CommandInterface
{
    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function configure()
    {
        $this->setName('install:composer');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::create();
        $installDir = $config->get('dotfiles','install_dir');
        $targetDir = $config->get('dotfiles','install_dir').'/bin';
        $force = $input->getOption('force');
        $installFileName = $targetDir.DIRECTORY_SEPARATOR.$config->get('composer','filename');
        if(is_file($installFileName) && !$force){
            $output->writeln(sprintf('Composer already installed in <comment>%s</comment>',$installFileName));
            return 0;
        }

        if(!is_dir($dir = sys_get_temp_dir().'/dotfiles')){
            mkdir($dir,0755,true);
        }

        $target = $dir.DIRECTORY_SEPARATOR.'composer.php';
        if(!is_file($target) || $force){
            try{
                $url = "https://getcomposer.org/installer";
                $downloader = new Downloader($output,$url,$target);
                $downloader->run();
            }catch(\Exception $e){
                throw $e;
            }
        }

        if(!is_dir($targetDir)){
            mkdir($targetDir,0755,true);
        }
        $cmd = [
            'php',
            $target,
            '--install-dir='.$targetDir,
            '--filename='.$config->get('composer','filename'),
            '--ansi'
        ];
        $cmd = implode(' ',$cmd);
        $output->writeln(sprintf("Executing command: <comment>%s</comment>",$cmd));
        passthru($cmd);
        $output->writeln(sprintf("Composer installed in: <comment>%s</comment>",$installFileName));
    }
}
