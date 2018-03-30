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
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Toni\Dotfiles\Command\CommandInterface;
use Toni\Dotfiles\Util\Config;
use Toni\Dotfiles\Application;

class SelfUpdateCommand extends Command implements CommandInterface
{
    public function configure()
    {
        $this->setName('self-update');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
