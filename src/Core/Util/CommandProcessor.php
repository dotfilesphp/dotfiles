<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Util;


use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class CommandProcessor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @param string|array   $commandline The command line to run
     * @param string|null    $cwd         The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env         The environment variables or null to use the same environment as the current PHP process
     * @param mixed|null     $input       The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     *
     * @throws RuntimeException When proc_open is not installed
     *
     * @return Process
     */
    public function create($commandline, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        $process = new Process($commandline,$cwd,$env,$input,$timeout);
        $this->debug($commandline);
        return $process;
    }

    private function debug($message, $context = array())
    {
        $message = '<comment>[command]</comment> '.$message;
        $this->logger->debug($message,$context);
    }
}
