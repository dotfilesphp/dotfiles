<?php

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Core\Config\Config;

use Dotfiles\Plugins\PHPBrew\ConfigDefinition;
use PHPUnit\Framework\TestCase;

class ConfigDefinitionTest extends TestCase
{
    public function testProcess()
    {
        $config = new Config();
        $config->addDefinition(new ConfigDefinition());
        $config->addConfigDir(__DIR__.'/fixtures');
        $config->loadConfiguration();
        $processed = $config->get();

        $this->assertTrue($processed['phpbrew']['set_prompt']);
        $this->assertArrayHasKey('machines',$processed['phpbrew']);
    }

}
