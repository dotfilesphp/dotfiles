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

use Dotfiles\Core\Command\SelfUpdateCommand;
use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Tests\BaseTestCase;
use Dotfiles\Core\Util\Downloader;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommandTest extends BaseTestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $downloader;

    /**
     * @var string
     */
    private $tempDir;

    public function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config       = $this->createMock(Config::class);
        $this->downloader   = $this->createMock(Downloader::class);
        $this->tempDir      = sys_get_temp_dir().'/dotfiles';
    }

    public function testExecute()
    {
        $input      = $this->createMock(InputInterface::class);
        $output     = $this->createMock(OutputInterface::class);
        $tempDir    = $this->tempDir;

        $this->downloader->expects($this->once())
            ->method('run')
            ->with(SelfUpdateCommand::BASE_URL.'/dotfiles.phar.json',$tempDir.'/temp/update/dotfiles.phar.json')
            ->willReturnOnConsecutiveCalls([
                [$this->returnCallback(function() use ($tempDir){
                    touch($tempDir.'/temp/update/dotfiles.phar.json');
                })],
            ])
        ;
        $command    = $this->getSUT();
        $command->execute($input,$output);
    }

    private function getSUT()
    {
        $tempDir = $this->tempDir;

        $this->config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['dotfiles.temp_dir',$tempDir.'/temp'],
                ['dotfiles.cache_dir', $tempDir.'/cache'],
            ])
        ;
        return new SelfUpdateCommand(
            null,
            $this->downloader,
            $this->config
        );
    }
}
