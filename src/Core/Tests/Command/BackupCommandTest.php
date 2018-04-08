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
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Tests\CommandTester;
use Dotfiles\Core\Util\Toolkit;
use Psr\Log\LoggerInterface;

class BackupCommandTest extends CommandTestCase
{
    private $dirs = array();

    public function getObjectToTest($config = array())
    {
        $defConfig = array(
            'base_dir' => __DIR__.'/fixtures/default',
            'backup_dir' => sys_get_temp_dir().'/dotfiles/backup',
            'home_dir' => __DIR__.'/fixtures/home',
            'machine_name' => 'zeus',
        );

        $this->dirs = $dirs = array_merge($defConfig, $config);

        $config = $this->createMock(Config::class);
        $logger = $this->createMock(LoggerInterface::class);

        $config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.base_dir', $dirs['base_dir']),
                array('dotfiles.backup_dir', $dirs['backup_dir']),
                array('dotfiles.home_dir', $dirs['home_dir']),
                array('dotfiles.machine_name', $dirs['machine_name']),
            ))
        ;

        return new BackupCommand($config, $logger);
    }

    public function testExecute(): void
    {
        $command = $this->getObjectToTest();
        $app = $this->getApplication();
        $app->add($command);

        $backupDir = $this->dirs['backup_dir'];
        Toolkit::ensureDir($backupDir);
        touch($backupDir.'/manifest.php');
        $tester = new CommandTester($command);
        $tester->execute(array());

        $output = $tester->getDisplay(true);
        $this->assertContains('Backup files already exists', $output);
        unlink($backupDir.'/manifest.php');
        $tester->execute(array());
        $output = $tester->getDisplay(true);
        $this->assertNotContains('Backup file already exists', $output);
        $this->assertFileExists($backupDir.'/.bashrc');
        $this->assertFileExists($backupDir.'/.zeus');
        $this->assertFileExists($backupDir.'/manifest.php');

        $manifest = include $backupDir.'/manifest.php';
        $this->assertArrayHasKey('.bashrc', $manifest);
    }
}
