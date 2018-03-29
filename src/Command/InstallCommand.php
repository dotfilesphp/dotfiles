<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toni\Dotfiles\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class InstallCommand extends Command
{
    public function configure()
    {
        $this->setName('install');
        $this->loadDotEnv();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(getenv('HOME'));

        $output->writeln('<comment>Begin install dotfiles</comment>');

    }

    private function loadDotEnv()
    {
        $dotenv = new Dotenv();

        $files = array(realpath(__DIR__.'/../../.env.dist'));

        if(is_file($file = realpath(__DIR__.'/../../.env'))){
            $files[] = $file;
        }
        if(is_file($file = realpath(getenv('HOME').'.dotfiles.rc'))){
            $files[] = $file;
        }
        foreach($files as $file){
            $dotenv->load($file);
        }
    }

    private function doInstallPhpBrew(OutputInterface $output)
    {

    }
}