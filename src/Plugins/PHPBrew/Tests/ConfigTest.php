<?php

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Plugins\PHPBrew\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

class ConfigTest extends TestCase
{
    public function testProcess()
    {
        $contents = <<<EOC
phpbrew:
    set_prompt: true
    rc_enable: true
    machines:
        athena:
            set_prompt: false
            rc_enable: true
        zeus:
            set_prompt: true
            rc_enable: true
EOC;
        $parsed = Yaml::parse($contents);
        $processor = new Processor();
        $config = new Config();

        $processed = $processor->processConfiguration($config,$parsed);
        $this->assertTrue($processed['set_prompt']);
        $this->assertArrayHasKey('machines',$processed);
    }

}
