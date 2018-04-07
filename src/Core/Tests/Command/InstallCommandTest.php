<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Command;


use Dotfiles\Core\Command\InstallCommand;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Tests\CommandTester;
use Dotfiles\Core\Event\Dispatcher;

class InstallCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $dispatcher = $this->createMock(Dispatcher::class);
        $command = new InstallCommand(null,$dispatcher);
        $app = $this->getApplication();
        $app->add($command);
        $command = $app->find('install');
        $tester = new CommandTester($command);
        $tester->execute([]);
        $output = $tester->getDisplay(true);

        $this->assertContains('Begin installing dotfiles',$output);
    }
}