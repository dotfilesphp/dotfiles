<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Tests\Command;

use Dotfiles\Core\Command\ClearCacheCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommandTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $logger;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->config = $this->createMock(Config::class);
        static::cleanupTempDir();
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $tempDir = sys_get_temp_dir().'/cache';
        $cacheFile = $tempDir.'/some-file.php';
        Toolkit::ensureFileDir($cacheFile);
        touch($cacheFile);

        $this->config->expects($this->once())
            ->method('get')
            ->with('dotfiles.cache_dir')
            ->willReturn($tempDir)
        ;

        $this->logger->expects($this->once())
            ->method('debug')
            ->with($this->stringContains('some-file.php'))
        ;


        $command = new ClearCacheCommand(null,$this->config,$this->logger);
        $command->run($input, $output);
        $this->assertFileNotExists($cacheFile);
    }
}
