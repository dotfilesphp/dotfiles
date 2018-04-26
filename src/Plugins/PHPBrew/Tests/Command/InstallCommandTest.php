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

namespace Dotfiles\Plugins\PHPBrew\Tests\Command;

use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Plugins\PHPBrew\Command\InstallCommand;
use Dotfiles\Plugins\PHPBrew\Installer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InstallCommandTest.
 *
 * @covers \Dotfiles\Plugins\PHPBrew\Command\InstallCommand
 */
class InstallCommandTest extends BaseTestCase
{
    public function testExecute(): void
    {
        $installer = $this->createMock(Installer::class);
        $dispatcher = $this->createMock(Dispatcher::class);
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->expects($this->once())
            ->method('getOption')
            ->with('force')
            ->willReturn(false)
        ;
        $installer->expects($this->once())
            ->method('run')
        ;
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Constant::EVENT_PATCH)
        ;
        $command = new InstallCommand(null, $installer, $dispatcher);
        $command->execute($input, $output);
    }
}
