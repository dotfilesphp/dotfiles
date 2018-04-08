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

use Dotfiles\Core\Command\BackupCommand;
use Dotfiles\Core\Command\InstallCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Tests\CommandTester;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class InstallCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $config;
    /**
     * @var MockObject
     */
    private $dispatcher;

    /**
     * @var MockObject
     */
    private $logger;

    public function getTestProcessMachine()
    {
        return array(
            array('zeus'),
            array('athena'),
        );
    }

    public function testExecute(): void
    {
        $command = $this->getClassToTest();

        $backup = $this->getBackupCommand();
        $backup->expects($this->once())
            ->method('execute')
        ;

        $app = $this->getApplication();
        $app->add($command);
        $app->add($backup);
        $command = $app->find('install');
        $tester = new CommandTester($command);
        $tester->execute(array());
        $output = $tester->getDisplay(true);

        $this->assertContains('Begin installing dotfiles', $output);
    }

    /**
     * @dataProvider getTestProcessMachine
     */
    public function testProcessMachine($machine): void
    {
        $fixturesDir = __DIR__.'/fixtures/default';
        $command = $this->getClassToTest($fixturesDir, $machine);

        $backup = $this->getBackupCommand();
        $app = $this->getApplication();
        $app->add($command);
        $app->add($backup);
        $command = $app->find('install');
        $tester = new CommandTester($command);
        $tester->execute(array());

        $output = $tester->getDisplay(true);
        $home = getenv('HOME');
        $binDir = sys_get_temp_dir().'/dotfiles/tests/bin';
        $this->assertFileExists($home.'/.config/i3/config');
        $this->assertFileExists($home.'/.'.$machine);
        $this->assertFileExists($binDir.'/default-bin');
        $this->assertFileExists($binDir."/{$machine}-bin");
        $this->assertContains('default install hook', $output);
        $this->assertContains("$machine install hook", $output);

        // testing patch
        $file = $home.'/.patch';
        $contents = file_get_contents($file);
        $this->assertContains('base contents', $contents);
        $this->assertContains('patch default', $contents);
        $this->assertContains('patch '.$machine, $contents);
    }

    /**
     * @return MockObject
     *
     * @throws \ReflectionException
     */
    private function getBackupCommand()
    {
        $backup = $this->getMockBuilder(BackupCommand::class)
            ->setMethods(array('execute', 'isEnabled', 'getName', 'getDefinition', 'getAliases'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $backup->expects($this->any())
            ->method('isEnabled')
            ->willReturn(true)
        ;
        $backup->expects($this->any())
            ->method('getName')
            ->willReturn('backup')
        ;
        $backup->expects($this->any())
            ->method('getDefinition')
            ->willReturn('Backup command')
        ;
        $backup->expects($this->any())
            ->method('getAliases')
            ->willReturn(array())
        ;

        return $backup;
    }

    private function getClassToTest($baseDir = null, $machineName = null)
    {
        $this->dispatcher = $this->createMock(Dispatcher::class);
        $this->config = $this->createMock(Config::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $binDir = sys_get_temp_dir().'/dotfiles/tests/bin';
        $vendorDir = sys_get_temp_dir().'/dotfiles/tests/vendor';
        $backupDir = sys_get_temp_dir().'/dotfiles/backup';
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.bin_dir', $binDir),
                array('dotfiles.vendor_dir', $vendorDir),
                array('dotfiles.base_dir', $baseDir),
                array('dotfiles.machine_name', $machineName),
                array('dotfiles.backup_dir', $backupDir),
                array('dotfiles.home_dir', getenv('HOME')),
            ))
        ;
        $command = new InstallCommand(
            null,
            $this->dispatcher,
            $this->config,
            $this->logger
        );

        return $command;
    }
}
