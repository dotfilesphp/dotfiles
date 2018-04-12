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

use Dotfiles\Core\Processor\Hooks;
use Dotfiles\Core\Processor\Patcher;
use Dotfiles\Core\Processor\Template;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreCommand extends Command
{
    /**
     * @var Hooks
     */
    private $hooks;

    /**
     * @var Patcher
     */
    private $patcher;
    /**
     * @var Template
     */
    private $template;

    public function __construct(
        ?string $name = null,
        Template $template,
        Patcher $patcher,
        Hooks $hooks
    ) {
        parent::__construct($name);
        $this->template = $template;
        $this->patcher = $patcher;
        $this->hooks = $hooks;
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
        $this->template->run();
        $this->patcher->run();
        $this->hooks->run();
    }
}
