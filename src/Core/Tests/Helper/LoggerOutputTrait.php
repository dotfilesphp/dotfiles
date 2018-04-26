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

/**
 * Trait LoggerOutputTrait.
 *
 * @deprecated use built in display in BaseTestCase
 */
trait LoggerOutputTrait
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StreamOutput
     */
    private $loggerOutput;

    /**
     * @var resource
     */
    private $loggerStream;

    public function getLoggerOutput()
    {
        return $this->loggerOutput;
    }

    public function setUpLogger(): void
    {
        $this->loggerStream = fopen('php://memory', 'w');
        $this->loggerOutput = new StreamOutput($this->loggerStream);
        $this->loggerOutput->setVerbosity(StreamOutput::VERBOSITY_DEBUG);
        $this->logger = new ConsoleLogger($this->loggerOutput);
    }

    private function getLoggerDisplay()
    {
        rewind($this->loggerOutput->getStream());
        $display = stream_get_contents($this->loggerOutput->getStream());

        $display = str_replace(PHP_EOL, "\n", $display);

        return $display;
    }
}
