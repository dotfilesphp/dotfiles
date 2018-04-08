<?php

namespace Dotfiles\Plugins\PHPBrew\Tests;

use Dotfiles\Plugins\PHPBrew\PHPBrewPlugin;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Core\Config\Config;

use PHPUnit\Framework\TestCase;

class PHPBrewPluginTest extends TestCase
{
    public function testGetName()
    {
        $phpbrew = new PHPBrewPlugin();
        $this->assertEquals('PHPBrew',$phpbrew->getName());
    }
}
