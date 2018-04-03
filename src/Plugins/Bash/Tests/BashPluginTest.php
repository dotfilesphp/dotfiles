<?php

namespace Dotfiles\Plugins\Bash\Tests;

use Dotfiles\Core\Config\Config;
use Dotfiles\Plugins\Bash\Config\Definition;
use Dotfiles\Plugins\Bash\BashPlugin;
use PHPUnit\Framework\TestCase;

class BashPluginTest extends TestCase
{
    public function testSetupConfiguration()
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('addDefinition')
            ->with(new Definition())
        ;
        $plugin = new BashPlugin();
        $plugin->setupConfiguration($config);
    }
}
