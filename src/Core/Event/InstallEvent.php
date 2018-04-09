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

namespace Dotfiles\Core\Event;

class InstallEvent extends AbstractEvent
{
    public const NAME = 'dotfiles.install';

    /**
     * @var array
     */
    private $patches = array();

    public function addPatch($target, $patch): void
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
}
