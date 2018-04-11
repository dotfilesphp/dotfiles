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

namespace Dotfiles\Core\Tests\Command;

use Dotfiles\Core\Command\RestoreCommand;
use Dotfiles\Core\Processor\Restore;
use Dotfiles\Core\Tests\CommandTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RestoreCommandTest.
 *
 * @covers \Dotfiles\Core\Command\RestoreCommand
 */
class RestoreCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $restore;

    public function testExecute(): void
    {
        $this->restore = $this->createMock(Restore::class);
        $this->restore->expects($this->once())
            ->method('run')
        ;

        $tester = $this->getTester('restore');
        $tester->execute(array());
    }

    protected function configureCommand(): void
    {
        $this->command = new RestoreCommand(null, $this->restore);
    }
}
