<?php

namespace Dotfiles\Plugins\Bash\Listeners;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Dotfiles\Core\Event\InstallEvent;
use Dotfiles\Core\Util\Filesystem;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Core\Config\Config;

class InstallListener implements EventSubscriberInterface
{
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher, Config $config)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallEvent::NAME => 'onInstallEvent'
        ];
    }

    public function onInstallEvent(InstallEvent $event)
    {
        $reloadEvent = new ReloadBashConfigEvent();
        $this->dispatcher->dispatch(ReloadBashConfigEvent::NAME,$reloadEvent);
        $this->generateDotfilesConfig($reloadEvent->getBashConfig());
        $this->patchHomeConfig();
    }

    private function generateDotfilesConfig($bashConfig)
    {
        $installDir = $this->config->get('dotfiles.install_dir');

        $uname = php_uname();
        if(false!==strpos('darwin',$uname)){
            $fileName = 'bash_profile';
        }else{
            $fileName = 'bashrc';
        }

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

    private function patchHomeConfig()
    {
        $installDir = $this->config->get('dotfiles.install_dir');
        $fs = new Filesystem();
        $contents = <<<EOC
source "\$HOME/${installDir}/bashrc"
EOC;

        $fs->patch(getenv('HOME').DIRECTORY_SEPARATOR.'/.bashrc',$contents);
    }
}

