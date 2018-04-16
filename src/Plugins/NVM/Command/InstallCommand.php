<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM\Command;

use Dotfiles\Core\Command\Command;
use Dotfiles\Plugins\NVM\Installer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * @var Installer
     */
    private $installer;

    public function __construct(?string $name = null, Installer $installer)
    {
        parent::__construct($name);

        $this->installer = $installer;
    }

    protected function configure()
    {
        $this
            ->setName('nvm:install')
            ->setDescription('Install Node Version Manager')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->installer->install();
    }
}
