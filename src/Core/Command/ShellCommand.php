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

use Dotfiles\Core\Console\Shell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ShellCommand.
 *
 * @author Anthonius Munthi me@itstoni.com
 */
class ShellCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('shell')
            ->setDescription('Execute dotfiles shell')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shell = new Shell($this->getApplication());
        $shell->run();
    }
}
