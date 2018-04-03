<?php

namespace Dotfiles\Core\Events;

abstract class AbstractListener implements ListenerInterface
{
    public function isListener($listener)
    {
        return $listener === $this;
    }
}
