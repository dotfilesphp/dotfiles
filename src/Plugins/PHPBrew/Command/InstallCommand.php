<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Plugins\PHPBrew\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dotfiles\Core\Command\CommandInterface;

class InstallCommand extends Command implements CommandInterface
{
    public function configure()
    {
        $this->setName('phpbrew:install');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
