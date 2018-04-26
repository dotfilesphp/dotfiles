<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Event;

use Symfony\Component\EventDispatcher\Event;

class RestoreEvent extends Event
{
    /**
     * @var array
     */
    private $files;

    /**
     * @var array
     */
    private $patches;

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getPatches(): array
    {
        return $this->patches;
    }

    public function setFiles(array $files)
    {
        $this->files = $files;
    }

    public function setPatches(array $patches)
    {
        $this->patches = $patches;
    }
}
