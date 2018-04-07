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
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Tests\CommandTester;
use Dotfiles\Core\Event\Dispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class InstallCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $dispatcher;

    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $logger;

    private function getClassToTest($baseDir = null, $machineName = null)
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->config = $this->createMock(Config::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $binDir = sys_get_temp_dir().'/dotfiles/tests/bin';
        $vendorDir = sys_get_temp_dir().'/dotfiles/tests/vendor';
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['dotfiles.bin_dir',$binDir],
                ['dotfiles.vendor_dir',$vendorDir],
                ['dotfiles.base_dir',$baseDir],
                ['dotfiles.machine_name',$machineName]
            ])
        ;
        $command = new InstallCommand(
            null,
            $this->dispatcher,
            $this->config,
            $this->logger
        );

        return $command;
    }

    public function testExecute()
    {
        $command = $this->getClassToTest();

        $app = $this->getApplication();
        $app->add($command);
        $command = $app->find('install');
        $tester = new CommandTester($command);
        $tester->execute([]);
        $output = $tester->getDisplay(true);

        $this->assertContains('Begin installing dotfiles',$output);
    }

    public function testProcessMachine()
    {
        $fixturesDir = __DIR__.'/fixtures/default';
        $command = $this->getClassToTest($fixturesDir,'zeus');

        $app = $this->getApplication();
        $app->add($command);
        $command = $app->find('install');
        $tester = new CommandTester($command);
        $tester->execute([
        ]);

        $output = $tester->getDisplay(true);
        $home = getenv('HOME');
        $this->assertFileExists($home.'/.config/i3/config');
        $this->assertFileExists($home.'/.zeus');
    }
}
