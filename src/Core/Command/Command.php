<?php

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Command\CommandInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends BaseCommand implements CommandInterface
{
}
