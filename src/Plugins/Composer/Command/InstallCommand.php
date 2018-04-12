<?php

declare(strict_types=1);

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Composer\Command;

use Dotfiles\Core\Command\Command;
use Dotfiles\Plugins\Composer\Installer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * InstallCommand constructor.
     *
     * @param null|string $name
     * @param Installer   $installer
     */
    public function __construct(?string $name = null, Installer $installer)
    {
        parent::__construct($name);
        $this->installer = $installer;
    }

    protected function configure(): void
    {
        $this
            ->setName('composer:install')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force install composer even when already installed')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->installer->run($input->getOption('force'));
    }
}
