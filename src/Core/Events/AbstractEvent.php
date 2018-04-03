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
use Dotfiles\Core\Config\Config;

class AbstractEvent extends BaseAbstractEvent
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger():LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param Config $config
     * @return self
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
      return $this->config;
    }
}
