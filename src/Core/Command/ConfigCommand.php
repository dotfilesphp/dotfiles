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

use Dotfiles\Core\DI\Parameters;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigCommand extends Command
{
    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(?string $name = null, Parameters $parameters)
    {
        parent::__construct($name);
        $this->parameters = $parameters;
    }

    protected function configure(): void
    {
        $this
            ->setName('config')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $config = $this->parameters;

        $params = $config->all(true);

        foreach ($params as $name => $value) {
            $output->writeln(sprintf(
                '<info>%s</info>=<comment>%s</comment>',
                $name,
                $value
            ));
        }
    }
}
