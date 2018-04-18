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

namespace Dotfiles\Core\Tests\Helper;

use Dotfiles\Core\Command\Command;

abstract class CommandTestCase extends BaseTestCase
{
    protected $application;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @param string $commandName
     *
     * @return CommandTester
     */
    public function getTester(string $commandName): CommandTester
    {
        $this->configureCommand();
        $this->getApplication()->add($this->command);
        $cmd = $this->getApplication()->find($commandName);

        return new CommandTester($cmd);
    }

    abstract protected function configureCommand();
}
