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

use Dotfiles\Core\Tests\Helper\BaseTestCase;
use Dotfiles\Core\Util\Toolkit;

/**
 * Class ToolkitTest.
 *
 * @covers \Dotfiles\Core\Util\Toolkit
 */
class ToolkitTest extends BaseTestCase
{
    public function testGetCachePathPrefix(): void
    {
        global $argv;
        $expected = getenv('DOTFILES_CACHE_DIR').DIRECTORY_SEPARATOR.crc32($argv[0]);
        $this->assertEquals($expected, Toolkit::getCachePathPrefix());
    }
}
