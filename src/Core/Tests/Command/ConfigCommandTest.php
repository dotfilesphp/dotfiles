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

use Dotfiles\Core\Command\ConfigCommand;
use Dotfiles\Core\Tests\Helper\CommandTestCase;

/**
 * Class ConfigCommandTest.
 *
 * @covers \Dotfiles\Core\Command\ConfigCommand
 */
class ConfigCommandTest extends CommandTestCase
{
    public function testExecute()
    {
        $tester = $this->getTester('config');
        $tester->execute(array());
        $output = $tester->getDisplay();
        $this->assertContains('dotfiles.env=dev', $output);
    }

    protected function configureCommand()
    {
        $parameters = $this->getParameters();
        $this->command = new ConfigCommand(null, $parameters);
    }
}
