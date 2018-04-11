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

namespace Dotfiles\Core\Tests\Util;

use Dotfiles\Core\Util\Toolkit;
use PHPUnit\Framework\TestCase;

class ToolkitTest extends TestCase
{
    public function testGetCachePathPrefix(): void
    {
        global $argv;
        $expected = sys_get_temp_dir().'/dotfiles/var/cache/'.crc32($argv[0]);
        $this->assertEquals($expected, Toolkit::getCachePathPrefix());
    }
}
