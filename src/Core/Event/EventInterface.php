<?php

namespace Dotfiles\Core\Event;

use Dotfiles\Core\Config\Config;
use League\Event\EventInterface as BaseEventInterface;

interface EventInterface extends BaseEventInterface
{
    /**
     * @param Config $config
     */
    public function setConfig(Config $config);

    /**
     * @return Config
     */
    public function getConfig();
}
