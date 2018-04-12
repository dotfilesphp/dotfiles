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

namespace Dotfiles\Plugins\PHPBrew\Command;

use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Processor\Patcher;
use Dotfiles\Plugins\PHPBrew\Installer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command implements CommandInterface
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var Patcher
     */
    private $patcher;

    public function __construct(?string $name = null, Installer $installer, Patcher $patcher)
    {
        parent::__construct($name);
        $this->installer = $installer;
        $this->patcher = $patcher;
    }

    public function configure(): void
    {
        $this
            ->setName('phpbrew:install')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force install phpbrew')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->installer->run($input->getOption('force'));
        $this->patcher->run();
    }
}
