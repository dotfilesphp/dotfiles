<?php

namespace Dotfiles\Plugins\PHPBrew\Tests\Config;

use Dotfiles\Core\Config\Config;

use Dotfiles\Plugins\PHPBrew\Config\Definition;
use PHPUnit\Framework\TestCase;

class DefinitionTest extends TestCase
{
    public function testProcess()
    {
        $config = new Config();
        $config->addDefinition(new Definition());
        $config->addConfigDir(__DIR__.'/fixtures');
        $config->loadConfiguration();
        $processed = $config->get();

        $this->assertTrue($processed['phpbrew']['set_prompt']);
        $this->assertArrayHasKey('machines',$processed['phpbrew']);
    }

}
