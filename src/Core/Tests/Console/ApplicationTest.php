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

namespace Dotfiles\Core\Tests\Console;

use Dotfiles\Core\Console\Application;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\DI\Parameters;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApplicationTest.
 *
 * @covers \Dotfiles\Core\Console\Application
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

    protected function setUp(): void/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->config = $this->createMock(Parameters::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
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

    private function getSUT()
    {
        return new Application($this->config, $this->input, $this->output);
    }
}
