<?php

namespace Dotfiles\Plugins\Bash\Events;

use Dotfiles\Core\Events\AbstractListener;
use League\Event\EventInterface;

class InstallListener extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        $reloadEvent = new ReloadBashConfigEvent();
        $event->getEmitter()->emit($reloadEvent);

        $bashConfig = $reloadEvent->getBashConfig();

        $prefix = "### BEGIN_DOTFILES_PATCH ###";
        $suffix = "### END_DOTFILES_PATCH ###";
        $event->getConfig()->get('dotfiles.install_dir');

        $uname = php_uname();
        if(false!==strpos('darwin',$uname)){
            $fileName = 'bash_profile';
        }else{
            $fileName = 'bashrc';
        }

        $installDir = $event->getConfig()->get('dotfiles.install_dir');
        // write config into dotfiles location
        $contents = <<<EOC
# WARNING!!!
# This file is generated automatically by DOTFILES installer
# All changes in this file will be overwrite later with DOTFILES

$bashConfig

# END DOTFILES CONFIG

EOC;

        file_put_contents($installDir.DIRECTORY_SEPARATOR.$fileName,$bashConfig, LOCK_EX);

        // write bash config patch into home location
        $contents = [
            $prefix,
            "source = \"${installDir}/${fileName}\"",
            $suffix
        ];
        $contents = implode(PHP_EOL,$contents);
        $target = getenv('HOME').DIRECTORY_SEPARATOR.'.'.$fileName;
        file_put_contents($target,$contents,LOCK_EX);
    }
}

