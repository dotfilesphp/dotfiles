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
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Config
     */
    private $config;

    private $installDir;

    public function __construct(Dispatcher $dispatcher, Config $config)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
    }

    /**
     * @param mixed $installDir
     */
    public function setInstallDir($installDir): void
    {
        $this->installDir = $installDir;
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
        $installDir = $this->installDir;

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
        $installDir = $this->installDir;
        $fs = new Filesystem();
        $contents = <<<EOC
source "${installDir}/bashrc"
EOC;

        $fs->patch(getenv('HOME').DIRECTORY_SEPARATOR.'/.bashrc',$contents);
    }
}

