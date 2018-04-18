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
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Util\Toolkit;

/**
 * Class FilesystemTest.
 *
 * @covers \Dotfiles\Core\Util\Filesystem
 */
class FilesystemTest extends BaseTestCase
{
    public function testPatch(): void
    {
        $fs = new Filesystem();
        $current = <<<'EOC'

export FOO="BAR"

EOC;
        $target = '/tmp/foo/bar/somefile';
        Toolkit::ensureFileDir($target);
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

    public function testPatchOnNonExistingFile()
    {
        $patch = <<<'EOC'

export HELLO="WORLD"

EOC;
        $fs = new Filesystem();
        $file = '/tmp/dotfiles/tests/somefile';
        if (is_dir($dir = dirname($file))) {
            $fs->removeDir($dir);
        }
        $fs->patch($file, $patch);
        $contents = file_get_contents($file);
        $this->assertContains('> dotfiles-patch', $contents);
        $this->assertContains('export HELLO="WORLD"', $contents);
        $this->assertContains('< dotfiles-patch', $contents);
    }

    public function testRemoveDir()
    {
        $file = '/tmp/foo/bar/hello/world.txt';
        Toolkit::ensureFileDir($file);
        touch($file);

        $output = $this->getService('dotfiles.output');
        $callback = function ($pathName) use ($output) {
            $output->writeln('- '.$pathName);
        };
        $fs = new Filesystem();
        $fs->removeDir('/tmp/foo', $callback);
        $display = $this->getDisplay();

        $this->assertContains('/tmp/foo/bar/hello/world.txt', $display);
        $this->assertContains('/tmp/foo/bar/hello', $display);
        $this->assertContains('/tmp/foo/bar', $display);
        $this->assertContains('/tmp/foo', $display);
        $this->assertDirectoryNotExists('/tmp/foo');
    }
}
