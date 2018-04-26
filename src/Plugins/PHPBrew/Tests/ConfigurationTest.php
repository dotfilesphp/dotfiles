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

namespace Dotfiles\Plugins\PHPBrew\Tests;

/**
 * Class ConfigurationTest.
 *
 * @covers \Dotfiles\Plugins\PHPBrew\Configuration
 */
class ConfigurationTest extends \Dotfiles\Core\Tests\Helper\BaseTestCase
{
    public function testProcess(): void
    {
        $parameters = $this->getParameters();

        $this->assertTrue($parameters->get('phpbrew.set_prompt'));
        $this->assertTrue($parameters->get('phpbrew.rc_enable'));
    }
}
