<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\Bash\Event;

use Dotfiles\Core\Event\AbstractEvent;
use Psr\Log\LoggerInterface;

class ReloadBashConfigEvent extends AbstractEvent
{
    const NAME = 'bash.reload_config';

    private $header = array();

    private $footer = array();

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function addHeaderConfig($contents)
    {
        if (!is_array($contents)) {
            $contents = array($contents);
        }
        $this->header = array_merge($this->header, $contents);
        $this->logger->debug(
            'Added bash config',
            array('contents' => implode(PHP_EOL, $contents))
        );
    }

    public function addFooterConfig($contents)
    {
        if (!is_array($contents)) {
            $contents = array($contents);
        }
        $this->footer = array_merge($this->footer, $contents);
    }

    public function getBashConfig()
    {
        $config = array_merge($this->header, $this->footer);

        return implode(PHP_EOL, $config);
    }
}
