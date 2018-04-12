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

namespace Dotfiles\Core\Tests\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\StreamOutput;

trait LoggerOutputTrait
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StreamOutput
     */
    private $output;

    /**
     * @var resource
     */
    private $stream;

    public function setUpLogger(): void
    {
        $this->stream = fopen('php://memory', 'w');
        $this->output = new StreamOutput($this->stream);
        $this->output->setVerbosity(StreamOutput::VERBOSITY_DEBUG);
        $this->logger = new ConsoleLogger($this->output);
    }

    private function getDisplay()
    {
        rewind($this->output->getStream());
        $display = stream_get_contents($this->output->getStream());

        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }
}
