<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests\Console;

use Dotfiles\Core\Console\ConsoleLogger;
use Dotfiles\Core\Tests\Helper\BaseTestCase;

/**
 * Class ConsoleLoggerTest.
 *
 * @covers \Dotfiles\Core\Console\ConsoleLogger
 */
class ConsoleLoggerTest extends BaseTestCase
{
    public function testVerbosity()
    {
        $output = $this->getService('dotfiles.output');
        $logger = new ConsoleLogger($output);
        $logger->emergency('emergency');
        $logger->alert('alert');
        $logger->critical('critical');
        $logger->error('error');
        $logger->warning('warning');
        $logger->notice('notice');
        $logger->info('info');
        $logger->debug('debug');

        $display = $this->getDisplay();
        $this->assertContains('emergency', $display);
        $this->assertContains('notice', $display);
        $this->assertContains('info', $display);
        $this->assertNotContains('debug', $display);
    }
}
