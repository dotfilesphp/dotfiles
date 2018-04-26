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

use Dotfiles\Core\ApplicationFactory;
use Dotfiles\Core\Command\ClearCacheCommand;
use Dotfiles\Core\Tests\Helper\CommandTestCase;
use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCacheCommandTest.
 *
 * @covers \Dotfiles\Core\Command\ClearCacheCommand
 */
class ClearCacheCommandTest extends CommandTestCase
{
    /**
     * @var MockObject
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = $this->createMock(ApplicationFactory::class);
    }

    public function testExecute(): void
    {
        Toolkit::ensureDir($this->getParameters()->get('dotfiles.cache_dir'));
        $this->factory->expects($this->once())
            ->method('boot')
        ;

        $tester = $this->getTester('clear-cache');
        $tester->execute(array(), array('verbosity' => OutputInterface::VERBOSITY_DEBUG));
        $output = $tester->getDisplay();

        $this->assertContains('Cleaning cache in ', $output);
    }

    protected function configureCommand(): void
    {
        $this->command = new ClearCacheCommand(
            null,
            $this->getParameters(),
            $this->getService('dotfiles.logger'),
            $this->factory
        );
    }
}
