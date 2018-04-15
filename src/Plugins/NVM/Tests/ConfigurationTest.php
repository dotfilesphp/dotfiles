<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM\Tests;

use Dotfiles\Core\Tests\BaseTestCase;

/**
 * Class ConfigurationTest.
 *
 * @covers \Dotfiles\Plugins\NVM\Configuration
 */
class ConfigurationTest extends BaseTestCase
{
    public function testDefaultValues()
    {
        $all = $this->getParameters()->all();
        $this->assertArrayHasKey('nvm.install_dir', $all);
    }
}
