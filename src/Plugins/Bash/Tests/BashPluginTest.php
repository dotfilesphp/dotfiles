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

namespace Dotfiles\Plugins\Bash\Tests;

use Dotfiles\Core\ApplicationFactory;
use PHPUnit\Framework\TestCase;

class BashPluginTest extends TestCase
{
    public function testSetupConfiguration(): void
    {
        $factory = new ApplicationFactory();
        $factory->boot();
        $this->assertTrue($factory->hasPlugin('bash'));
    }
}
