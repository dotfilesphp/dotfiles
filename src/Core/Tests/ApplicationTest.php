<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Tests;

use Dotfiles\Core\Application;
use PHPUnit\Framework\TestCase;

/**
 * Class ApplicationTest
 * @package Dotfiles\Core\Tests
 * @covers \Dotfiles\Core\Application
 */
class ApplicationTest extends TestCase
{
    public function testEnv()
    {
        $app = new Application();
        $this->assertEquals('@package_version@',$app->getVersion());
        $expected = implode(' ',[
            Application::VERSION,
            Application::BRANCH_ALIAS_VERSION,
            Application::RELEASE_DATE
        ]);

        $this->assertEquals($expected,$app->getLongVersion());
    }
}

