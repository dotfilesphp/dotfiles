<?php

namespace Dotfiles\Core\Event;

use League\Event\EventInterface as BaseEventInterface;
use Dotfiles\Core\Config\Config;

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
