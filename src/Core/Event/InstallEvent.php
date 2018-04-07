<?php

namespace Dotfiles\Core\Event;

class InstallEvent extends AbstractEvent
{
    const NAME = 'dotfiles.install';

    /**
     * @var array
     */
    private $patches = [];

    public function getName()
    {
        return static::NAME;
    }

    public function addPatch($target,$patch)
    {
        if(!isset($this->patches[$target])){
            $this->patches[$target] = [];
        }
        $this->patches[$target][] = $patch;
    }

    /**
     * @return array
     */
    public function getPatches(): array
    {
        return $this->patches;
    }
}
