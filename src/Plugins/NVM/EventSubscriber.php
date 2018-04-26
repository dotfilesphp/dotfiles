<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM;

use Dotfiles\Core\Constant;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Event\PatchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Installer
     */
    private $installer;

    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(Parameters $parameters, Installer $installer)
    {
        $this->installer = $installer;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            Constant::EVENT_PRE_PATCH => 'onPrePatch',
        );
    }

    public function onPrePatch(PatchEvent $patchEvent)
    {
        $parameters = $this->parameters;

        if (is_dir($parameters->get('nvm.install_dir'))) {
            $patch = $this->installer->getBashPatch();
            $patchEvent->addPatch('.bashrc', $patch);
        }
    }
}
