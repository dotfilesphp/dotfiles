<?php

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Core\Config\Config as Builder;

use Dotfiles\Plugins\PHPBrew\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testProcess()
    {
        $cwd = getcwd();
        chdir(__DIR__.'/Fixtures');
        $config = new Builder();
        $config->addConfigDefinition(new Config());
        $config->loadConfiguration();
        $processed = $config->get();
        chdir($cwd);

        $this->assertTrue($processed['phpbrew']['set_prompt']);
        $this->assertArrayHasKey('machines',$processed['phpbrew']);
    }

}
