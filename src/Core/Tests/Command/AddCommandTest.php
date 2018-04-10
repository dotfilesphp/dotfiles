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

use Dotfiles\Core\Command\AddCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Exceptions\InvalidOperationException;
use Dotfiles\Core\Tests\CommandTestCase;
use Dotfiles\Core\Tests\CommandTester;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * Class AddCommandTest.
 *
 * @covers \Dotfiles\Core\Command\AddCommand
 */
class AddCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $config;
    /**
     * @var MockObject
     */
    private $logger;

    /**
     * @var string
     */
    private $repoDir;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->repoDir = sys_get_temp_dir().'/dotfiles/tests/add-command';
    }

    public function testAddDir(): void
    {
        $command = $this->getAddCommand();
        $app = $this->getApplication();
        $app->add($command);

        $cmd = $app->find('add');
        $tester = new CommandTester($cmd);

        // test with recursive option
        $tester->execute(array(
            'path' => '.ssh',
            '-r' => true,
        ));
        $output = $tester->getDisplay(true);

        $this->assertContains('ssh', $output);
        $this->assertFileExists($this->repoDir.'/src/default/home/ssh/id_rsa');
        $this->assertFileExists($this->repoDir.'/src/default/home/ssh/id_rsa.pub');

        // test with recursive option
        $tester->execute(array(
            'path' => '.ssh',
            '-r' => true,
            '-m' => 'athena',
        ));
        $this->assertFileExists($this->repoDir.'/src/athena/home/ssh/id_rsa');
        $this->assertFileExists($this->repoDir.'/src/athena/home/ssh/id_rsa.pub');

        // test without recursive option
        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessageRegExp('/without recursive/is');
        $tester->execute(array(
            'path' => '.ssh',
        ));
        $output = $tester->getDisplay(true);
        $this->assertContains('without recursive', $output);
    }

    public function testAddFile(): void
    {
        $command = $this->getAddCommand();
        $app = $this->getApplication();
        $app->add($command);

        $cmd = $app->find('add');
        $tester = new CommandTester($cmd);
        $tester->execute(array(
            'path' => '.bashrc',
        ));
        $output = $tester->getDisplay(true);

        $this->assertContains('bashrc', $output);
        $this->assertFileExists($this->repoDir.'/src/default/home/bashrc');

        $tester->execute(array(
            'path' => '.bashrc',
            '-m' => 'zeus',
        ));
        $this->assertFileExists($this->repoDir.'/src/zeus/home/bashrc');
    }

    public function testAddNonExistingPath(): void
    {
        $command = $this->getAddCommand();
        $app = $this->getApplication();
        $app->add($command);

        $this->expectException(InvalidOperationException::class);
        $this->expectExceptionMessageRegExp('/not exists/is');

        $cmd = $app->find('add');
        $tester = new CommandTester($cmd);
        $tester->execute(array(
            'path' => 'foo',
        ));
    }

    private function getAddCommand()
    {
        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap(array(
                array('dotfiles.repo_dir', $this->repoDir),
                array('dotfiles.home_dir', __DIR__.'/fixtures/home'),
            ))
        ;

        return new AddCommand(null, $this->config, $this->logger);
    }
}
