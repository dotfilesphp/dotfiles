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

namespace Dotfiles\Plugins\PHPBrew;

use Dotfiles\Core\Constant;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Event\PatchEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Parameters
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Parameters $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Constant::EVENT_PRE_PATCH => 'onPrePatchEvent',
        );
    }

    public function onPrePatchEvent(PatchEvent $event): void
    {
        $config = $this->config;
        if ($config->get('phpbrew.set_prompt')) {
            $event->addPatch('.bashrc', 'export PHPBREW_SET_PROMPT=1');
            $this->logger->debug('+phpbrew configured <comment>PHPBREW_USE_PROMPT</comment>');
        }
        if ($config->get('phpbrew.rc_enable')) {
            $event->addPatch('.bashrc', 'export PHPBREW_RC_ENABLE=1');
            $this->logger->debug('+phpbrew configured <comment>PHPBREW_RC_ENABLE</comment>');
        }

        $phpBrewScript = $config->get('dotfiles.home_dir').'/.phpbrew/bashrc';
        $event->addPatch('.bashrc', 'source "'.$phpBrewScript.'"');
    }
}
