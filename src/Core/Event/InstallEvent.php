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

class InstallEvent extends AbstractEvent
{
    const NAME = 'dotfiles.install';

    /**
     * @var array
     */
    private $patches = array();

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var bool
     */
    private $overwriteNewFiles;

    public function getName()
    {
        return static::NAME;
    }

    public function addPatch($target, $patch)
    {
        if (!isset($this->patches[$target])) {
            $this->patches[$target] = array();
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

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @param bool $dryRun
     *
     * @return InstallEvent
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOverwriteNewFiles(): bool
    {
        return $this->overwriteNewFiles;
    }

    /**
     * @param bool $overwriteNewFiles
     *
     * @return InstallEvent
     */
    public function setOverwriteNewFiles(bool $overwriteNewFiles): self
    {
        $this->overwriteNewFiles = $overwriteNewFiles;

        return $this;
    }
}
