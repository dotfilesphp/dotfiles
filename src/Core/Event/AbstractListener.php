<?php

namespace Dotfiles\Core\Event;

abstract class AbstractListener implements ListenerInterface
{
    public function isListener($listener)
    {
        return $listener === $this;
    }
}
