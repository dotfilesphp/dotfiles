<?php

namespace Dotfiles\Core\Util;

use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;

class Filesystem extends BaseFileSystem
{
    public function patch($file,$patch)
    {
        $prefix = "### > dotfiles-patch ###";
        $suffix = "### < dotfiles-patch ###";
        $patch = "\n${prefix}\n${patch}\n${suffix}\n";
        $regex = '/'.$prefix.'.*'.$suffix.'/is';

        $contents = file_get_contents($file);
        if(preg_match($regex,$contents,$matches)){
            $contents = str_replace("\n".$matches[0],$patch,$contents);
            $this->dumpFile($file,$contents);
        }else{
            $this->appendToFile($file,$patch);
        }
    }
}
