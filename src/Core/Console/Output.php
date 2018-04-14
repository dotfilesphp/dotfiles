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

namespace Dotfiles\Core\Console;

use Dotfiles\Core\DI\Parameters;
use Symfony\Component\Console\Output\ConsoleOutput;

class Output extends ConsoleOutput
{
    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(Parameters $parameters)
    {
        parent::__construct();
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function write($messages, $newline = false, $options = \Symfony\Component\Console\Output\Output::OUTPUT_NORMAL): void
    {
        $messages = $this->stripFileName($messages);
        parent::write($messages, $newline, $options);
    }

    /**
     * Strip long filename to be shorter.
     *
     * @param string $messages
     *
     * @return string
     */
    private function stripFileName(string $messages): string
    {
        return $messages;
    }
}
