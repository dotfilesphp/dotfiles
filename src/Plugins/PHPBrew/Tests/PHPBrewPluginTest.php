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

use Dotfiles\Plugins\PHPBrew\PHPBrewPlugin;
use PHPUnit\Framework\TestCase;

class PHPBrewPluginTest extends TestCase
{
    public function testGetName(): void
    {
        $phpbrew = new PHPBrewPlugin();
        $this->assertEquals('phpbrew', $phpbrew->getName());
    }
}
