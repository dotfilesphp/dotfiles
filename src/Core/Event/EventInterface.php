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

use Dotfiles\Core\Config\Config;
use League\Event\EventInterface as BaseEventInterface;

interface EventInterface extends BaseEventInterface
{
    /**
     * @return Config
     */
    public function getConfig();

    /**
     * @param Config $config
     */
    public function setConfig(Config $config);
}
