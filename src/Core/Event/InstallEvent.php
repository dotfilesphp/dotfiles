<?php

namespace Dotfiles\Core\Event;

class InstallEvent extends AbstractEvent
{
    const NAME = 'dotfiles.install';

    public function getName()
    {
        return static::NAME;
    }
}
