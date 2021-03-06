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
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\CommandTestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RestoreCommandTest.
 *
 * @covers \Dotfiles\Core\Command\RestoreCommand
 * @covers \Dotfiles\Core\Processor\Template
 * @covers \Dotfiles\Core\Processor\Hooks
 * @covers \Dotfiles\Core\Processor\Patcher
 */
class RestoreCommandTest extends CommandTestCase
{
    /**
     * @return array
     */
    public function getTestExecute()
    {
        return array(
            array('.ssh/id_rsa'),
            array('.ssh/id_rsa.pub'),
            array('.bashrc'),
            array('.dotfiles'),
            array('.no-dot-prefix', true),
        );
    }

    /**
     * @param string $file
     * @dataProvider getTestExecute
     */
    public function testExecute(string $file, $assertNot = false): void
    {
        static $hasExecuted = false;
        if (!$hasExecuted) {
            $tester = $this->getTester('restore');
            $tester->execute(array(), array('verbosity' => OutputInterface::VERBOSITY_DEBUG));
            $hasExecuted = true;
        }

        $homeDir = $this->getContainer()->get('dotfiles.config')->get('dotfiles.home_dir');
        if (!$assertNot) {
            $this->assertFileExists($homeDir.DIRECTORY_SEPARATOR.$file);
        } else {
            $this->assertFileNotExists($homeDir.DIRECTORY_SEPARATOR.$file);
        }
    }

    protected function configureCommand(): void
    {
        /* @var Config $config */
        $config = $this->getContainer()->get(Config::class);

        $config->set('dotfiles.backup_dir', __DIR__.'/fixtures/backup');
        $this->command = $this->getContainer()->get(RestoreCommand::class);
    }
}
