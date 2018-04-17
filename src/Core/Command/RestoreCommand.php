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

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends Command
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(
        ?string $name = null,
        Dispatcher $dispatcher
    ) {
        parent::__construct($name);
        $this->dispatcher = $dispatcher;
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
        $dispatcher = $this->dispatcher;
        $dispatcher->dispatch(Constant::EVENT_PRE_RESTORE);
        $dispatcher->dispatch(Constant::EVENT_RESTORE);
        $dispatcher->dispatch(Constant::EVENT_POST_RESTORE);
    }
}
