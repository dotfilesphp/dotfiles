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

use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\PHPBrew\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * Class DefinitionTest.
 *
 * @covers \Dotfiles\Plugins\PHPBrew\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testProcess(): void
    {
        $config = new Config();
        $config->addDefinition(new Configuration());
        $config->addConfigDir(__DIR__.'/fixtures');
        $config->loadConfiguration();
        $processed = $config->getAll();

        $this->assertTrue($processed['phpbrew']['set_prompt']);
        $this->assertArrayHasKey('machines', $processed['phpbrew']);
    }
}
