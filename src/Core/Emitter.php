<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core;

use League\Event\Emitter as BaseEmitter;
use Dotfiles\Core\Util\LoggerInterface;
use Dotfiles\Core\Util\Logger;
use Dotfiles\Core\Events\AbstractEvent;

class Emitter extends BaseEmitter
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *  Get emitter instance
     *
     *  @return self
     */
    public static function factory()
    {
        static $instance;
        if(!is_object($instance)){
            $instance = new self();
        }
        return $instance;
    }

    /**
     * @return self
     */
    public function setLogger(LoggerInterface $logger):self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger():LoggerInterface
    {
        if(!$this->logger instanceof LoggerInterface){
            $this->logger = new Logger();
        }
        return $this->logger;
    }

    /**
     * @var AbstractEvent $event
     */
    public function emit($event)
    {
        if($event instanceof AbstractEvent){
            $event->setLogger($this->logger);
        }
        return parent::emit($event);
    }

}
