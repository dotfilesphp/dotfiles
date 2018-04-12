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

namespace Dotfiles\Plugins\Bash;

use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Constant;
use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Core\Event\PatchEvent;
use Dotfiles\Core\Util\Toolkit;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    private $logger;

    public function __construct(Dispatcher $dispatcher, Config $config, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_PATCH => array(
                array('onPrePatchEvent', 1000),
            ),
            Constant::EVENT_POST_RESTORE => array(
                array('onPostRestore', 1000),
            ),
        );
    }

    /**
     * @param PatchEvent $event
     */
    public function onPrePatchEvent(PatchEvent $event): void
    {
        $this->doBashPatch($event);
    }

    private function doBashPatch(PatchEvent $event): void
    {
        $currentPatches = $event->getPatches();
        $bashPatch = array();
        if (array_key_exists('.bashrc', $currentPatches)) {
            $bashPatch = $currentPatches['.bashrc'];
        }
        $reloadEvent = new ReloadBashConfigEvent($this->logger);
        $reloadEvent->addFooterConfig(implode("\n", $bashPatch));

        $this->dispatcher->dispatch(ReloadBashConfigEvent::NAME, $reloadEvent);
        $this->generateDotfilesConfig($reloadEvent->getBashConfig());

        $installDir = $this->config->get('dotfiles.install_dir');
        $target = $this->config->get('dotfiles.home_dir').'/.bashrc';

        $event->setPatch($target, array("source \"${installDir}/bashrc\""));
    }

    private function generateDotfilesConfig($bashConfig): void
    {
        $installDir = $this->config->get('dotfiles.install_dir');

        $uname = php_uname();
        if (false !== strpos('darwin', $uname)) {
            $fileName = 'bash_profile';
        } else {
            $fileName = 'bashrc';
        }

        // write config into dotfiles location
        $contents = <<<EOC
# WARNING!!!
# This file is generated automatically by DOTFILES installer
# All changes in this file will be overwrite later with DOTFILES

export PATH="{$installDir}/bin:\$PATH"
$bashConfig

# END DOTFILES CONFIG

EOC;
        Toolkit::ensureFileDir($file = $installDir.DIRECTORY_SEPARATOR.$fileName);
        file_put_contents($file, $contents, LOCK_EX);
    }
}
