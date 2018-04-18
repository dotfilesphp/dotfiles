<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Processor;

use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Tests\Helper\BaseTestCase;

/**
 * Class ProcessRunnerTest.
 *
 * @covers \Dotfiles\Core\Processor\ProcessRunner
 */
class ProcessRunnerTest extends BaseTestCase
{
    public function testRun()
    {
        $runner = $this->getRunner();
        $runner->run('php --version');

        $display = $this->getDisplay();
        $this->assertContains('STARTED  Executing: php --version', $display);

        $hasCalled = false;
        $callback = function ($type, $buffer) use (&$hasCalled) {
            $hasCalled = true;
        };
        $runner->run('php --version', $callback);
        $this->assertTrue($hasCalled);
    }

    private function getRunner()
    {
        return new ProcessRunner(
            $this->getService('dotfiles.logger'),
            $this->getService('dotfiles.output')
        );
    }
}
