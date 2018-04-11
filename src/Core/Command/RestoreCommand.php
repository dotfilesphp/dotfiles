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

namespace Dotfiles\Core\Command;

use Dotfiles\Core\Processor\Restore;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends Command
{
    /**
     * @var Restore
     */
    private $restore;

    public function __construct(?string $name = null, Restore $restore)
    {
        parent::__construct($name);
        $this->restore = $restore;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('restore')
            ->setDescription('Restore dotfiles from backup')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->restore->run();
    }
}
