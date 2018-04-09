<?php

namespace Dotfiles\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\COnsole\Input\InputOption;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Core\Util\Filesystem;

class AddCommand extends Command implements CommandInterface
{
    protected function configure()
    {
        $this
            ->setName('add')
            ->setDescription('Add new file into dotfiles manager')
            ->addArgument('path', InputArgument::REQUIRED,'A file or directory name to add. This file must be exists in $HOME directory')
            ->addOption('machine','-m', InputOption::VALUE_OPTIONAL, 'Add this file/directory into machine registry','default')
            ->addOption('recursive','-r', InputOption::VALUE_NONE, 'Import all directory contents recursively')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $recursive  = $input->getOption('recursive');
        $machine    = $input->getOption('machine');
        $dir        = getcwd()."/templates/$machine/home";
        $sourcePath = $input->getArgument("path");
        $source     = getenv("HOME").DIRECTORY_SEPARATOR.$sourcePath;

        if(!is_file($source)){
            throw new \InvalidArgumentException("Path <comment>$sourcePath</comment> not exists");
        }
        Toolkit::ensureDir($dir);

        $fs = new Filesystem();
        $fs->copy($source, $dir."/".$sourcePath);
    }
}
