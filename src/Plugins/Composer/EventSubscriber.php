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

namespace Dotfiles\Plugins\Composer;

use Dotfiles\Core\Constant;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Installer
     */
    private $installer;

    public function __construct(
        Installer $installer
    ) {
        $this->installer = $installer;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_INSTALL => 'onInstallEvent',
        );
    }

    public function onInstallEvent(): void
    {
        $this->installer->run();
    }
}
