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
 * Class ToolkitTest.
 *
 * @covers \Dotfiles\Core\Util\Toolkit
 */
class ToolkitTest extends BaseTestCase
{
    public function getTestRelativePath()
    {
        $this->boot();
        $homeDir = getenv('DOTFILES_HOME_DIR');
        $backupDir = getenv('DOTFILES_BACKUP_DIR');

        return array(
            array('.bashrc', $homeDir.'/.bashrc'),
            array('backup/src/defaults/home/bashrc', $backupDir.'/src/defaults/home/bashrc'),
            array('/tmp/foo/bar', '/tmp/foo/bar'),
        );
    }

    public function testEnsureDirectory()
    {
        if (is_dir($dir = '/tmp/dotfiles/tests/foo')) {
            $fs = new Filesystem();
            $fs->removeDir($dir);
        }
        Toolkit::ensureFileDir($dir.'/bar.txt');
        $this->assertDirectoryExists($dir);
    }

    public function testEnsureDotPath()
    {
        $this->assertEquals('.bashrc', Toolkit::ensureDotPath('bashrc'));
        $this->assertEquals('.config/some/path', Toolkit::ensureDotPath('config/some/path'));
    }

    public function testFlattenArray()
    {
        $data = array(
            'foo' => array(
                'bar' => array(
                    'hello' => 'world',
                ),
            ),
        );
        $noPrefix = $data;
        Toolkit::flattenArray($noPrefix);
        $this->assertArrayHasKey('foo.bar.hello', $noPrefix);

        $withPrefix = $data;
        Toolkit::flattenArray($withPrefix, 'test');
        $this->assertArrayHasKey('test.foo.bar.hello', $withPrefix);
    }

    /**
     * @dataProvider getTestRelativePath
     */
    public function testGetRelativePath($expected, $path)
    {
        $this->assertEquals($expected, Toolkit::getRelativePath($path));
    }
}
