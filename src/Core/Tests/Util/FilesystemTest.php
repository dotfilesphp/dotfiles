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

use Dotfiles\Core\Util\Filesystem;
use PHPUnit\Framework\TestCase;

class FilesystemTest extends TestCase
{
    public function testPatch(): void
    {
        $fs = new Filesystem();
        $current = <<<'EOC'

export FOO="BAR"

EOC;
        $target = getenv('HOME').'/somefile';
        file_put_contents($target, $current, LOCK_EX);
        $patch = <<<'EOC'

export HELLO="WORLD"

EOC;
        // fresh patch test
        $fs->patch($target, $patch);
        $this->assertFileEquals(__DIR__.'/fixtures/patch-fresh', $target);

        // test patch when file already patch
        $fs->patch($target, 'EXISTING');
        $this->assertFileEquals(__DIR__.'/fixtures/patch-exist', $target);
    }
}
