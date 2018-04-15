<?php

namespace Dotfiles\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Console\Shell;

/**
 * ShellCommand
 *
 * @author Anthonius Munthi me@itstoni.com
 */
class ShellCommand extends Command
{
    protected function configure() {
        $this
            ->setName('shell')
            ->setDescription('Execute dotfiles shell')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $shell = new Shell($this->getApplication());
        $shell->run();
    }
}
