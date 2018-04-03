<?php

namespace Dotfiles\Core\Event;

class InstallEvent extends AbstractEvent
{
    const EVENT_NAME = 'dotfiles.install';

    public function getName()
    {
        return static::EVENT_NAME;
    }
}
