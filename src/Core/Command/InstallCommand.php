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
use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Event\Dispatcher;

class InstallCommand extends Command implements CommandInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function setDispatcher(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function configure()
    {
        $this->setName('install');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(getenv('HOME'));

        $output->writeln('Begin installing <comment>dotfiles</comment>');
        $event = new InstallEvent();
        $this->dispatcher->dispatch($event);
    }
}
