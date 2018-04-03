<?php

namespace Dotfiles\Core\Util;

use Symfony\Component\Filesystem\Filesystem as BaseFileSystem;

class Filesystem extends BaseFileSystem
{
    public function patch($file,$patch)
    {
        if(!is_dir($dir=dirname($file))){
            mkdir($dir,0755,true);
        }
        if(!is_file($file)){
            touch($file);
        }
        $prefix = "### > dotfiles-patch ###";
        $suffix = "### < dotfiles-patch ###";
        $patch = "${prefix}\n${patch}\n${suffix}";
        $regex = '/'.$prefix.'.*'.$suffix.'/is';

        $contents = file_get_contents($file);
        if(preg_match($regex,$contents,$matches)){
            $contents = str_replace($matches[0],$patch,$contents);
            $this->dumpFile($file,$contents);
        }else{
            $this->appendToFile($file,$patch);
        }
    }
}
