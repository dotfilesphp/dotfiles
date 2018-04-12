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

namespace Dotfiles\Core;

class Constant
{
    public const EVENT_PRE_INSTALL  = 'dotfiles.pre_install';
    public const EVENT_INSTALL      = 'dotfiles.install';
    public const EVENT_POST_INSTALL = 'dotfiles.post_install';

    // patch events
    public const EVENT_PRE_PATCH    = 'dotfiles.pre_patch';
    public const EVENT_PATCH        = 'dotfiles.patch';
    public const EVENT_POST_PATCH   = 'dotfiles.post_patch';

    // restore events
    public const EVENT_PRE_RESTORE  = 'dotfiles.pre_restore';
    public const EVENT_RESTORE      = 'dotfiles.restore';
    public const EVENT_POST_RESTORE = 'dotfiles.post_restore';
}
