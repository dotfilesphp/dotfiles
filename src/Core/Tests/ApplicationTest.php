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

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\Application;
use Dotfiles\Core\Config\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApplicationTest.
 *
 * @covers \Dotfiles\Core\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $config;

    /**
     * @var MockObject
     */
    private $input;

    /**
     * @var MockObject
     */
    private $output;

    protected function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config     = $this->createMock(Config::class);
        $this->input      = $this->createMock(InputInterface::class);
        $this->output     = $this->createMock(OutputInterface::class);
    }

    public function testVersion(): void
    {
        $app = $this->getSUT();
        $this->assertEquals('@package_version@', $app->getVersion());
        $expected = implode(' ', array(
            Application::VERSION,
            Application::BRANCH_ALIAS_VERSION,
            Application::RELEASE_DATE,
        ));

        $this->assertEquals($expected, $app->getLongVersion());
    }

    public function testRun()
    {
        $this->config->expects($this->once())
            ->method('set')
            ->with('dotfiles.dry_run',false)
        ;
        $app = $this->getSUT();
        $app->setAutoExit(false);
        $app->run();
    }

    private function getSUT()
    {
        return new Application($this->config,$this->input,$this->output);
    }
}
