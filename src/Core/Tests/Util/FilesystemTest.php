<?php

namespace Dotfiles\Core\Tests\Util;

use PHPUnit\Framework\TestCase;

use Dotfiles\Core\Util\Filesystem;

class FilesystemTest extends TestCase
{
    public function testPatch()
    {
        $fs = new Filesystem();
        $deleted = <<<EOC

### > dotfiles-patch ###
export DELETED="LINES"
### < dotfiles-patch ###

EOC;
        $current = <<<EOC

export FOO="BAR"

EOC;
        $target = getenv('HOME').'/somefile';
        file_put_contents($target,$current,LOCK_EX);
        $patch = <<<EOC

export HELLO="WORLD"

EOC;
        // fresh patch test
        $fs->patch($target,$patch);
        $this->assertFileEquals(__DIR__.'/fixtures/patch-fresh',$target);

        // test patch when file already patch
        $fs->patch($target,'EXISTING');
        $this->assertFileEquals(__DIR__.'/fixtures/patch-exist',$target);
    }
}
