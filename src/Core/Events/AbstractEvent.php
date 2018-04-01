<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Events;

use League\Event\AbstractEvent as BaseAbstractEvent;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dotfiles\Core\Util\LoggerInterface;

class AbstractEvent extends BaseAbstractEvent
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger():?LoggerInterface
    {
        return $this->logger;
    }
}
