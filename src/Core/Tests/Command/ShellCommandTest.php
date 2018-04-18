<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Command;

use Dotfiles\Core\Command\ShellCommand;
use Dotfiles\Core\Console\Shell;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShellCommandTest extends TestCase
{
    public function testExecute()
    {
        $output = $this->createMock(OutputInterface::class);
        $input = $this->createMock(InputInterface::class);
        $shell = $this->createMock(Shell::class);

        $shell->expects($this->once())
            ->method('run')
        ;
        $command = new ShellCommand(null, $shell);
        $command->run($input, $output);
    }
}
