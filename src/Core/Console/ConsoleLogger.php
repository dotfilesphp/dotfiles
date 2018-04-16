<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Console;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger as BaseConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends BaseConsoleLogger
{
    public function __construct(OutputInterface $output, array $verbosityLevelMap = array(), array $formatLevelMap = array())
    {
        $verbosityLevelMap = array(
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        );
        parent::__construct($output, $verbosityLevelMap, $formatLevelMap);
    }
}
