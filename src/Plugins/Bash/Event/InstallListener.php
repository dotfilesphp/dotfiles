<?php

namespace Dotfiles\Plugins\Bash\Event;

use Dotfiles\Core\Event\AbstractListener;
use Dotfiles\Core\Util\Filesystem;
use League\Event\EventInterface;

class InstallListener extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        $reloadEvent = new ReloadBashConfigEvent();
        $event->getEmitter()->emit($reloadEvent);
        $this->generateDotfilesConfig($event,$reloadEvent->getBashConfig());
        $this->patchHomeConfig($event);
    }

    private function generateDotfilesConfig(EventInterface $event,$bashConfig)
    {
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

        file_put_contents($installDir.DIRECTORY_SEPARATOR.$fileName,$contents, LOCK_EX);

    }

    private function patchHomeConfig(EventInterface $event)
    {
        $installDir = $event->getConfig()->get('dotfiles.install_dir');
        $fs = new Filesystem();
        $contents = <<<EOC
source "\$HOME/${installDir}/bashrc"
EOC;

        $fs->patch(getenv('HOME').DIRECTORY_SEPARATOR.'/.bashrc',$contents);
    }
}

