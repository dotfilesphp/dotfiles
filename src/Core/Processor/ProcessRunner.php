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

namespace Dotfiles\Core\Processor;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class ProcessRunner
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var float
     */
    private $timeout = 60;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(
        LoggerInterface $logger,
        OutputInterface $output
    ) {
        $this->logger = $logger;
        $this->output = $output;
    }

    /**
     * @param string|array   $commandline The command line to run
     * @param string|null    $cwd         The working directory or null to use the working dir of the current PHP process
     * @param array|null     $env         The environment variables or null to use the same environment as the current PHP process
     * @param mixed|null     $input       The input as stream resource, scalar or \Traversable, or null for no input
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     *
     * @throws RuntimeException When proc_open is not installed
     * @deprecated use run method
     *
     * @return Process
     */
    public function create($commandline, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        $process = new Process($commandline, $cwd, $env, $input, $timeout);
        $this->debug($commandline);

        return $process;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }


    private function debug($message, $context = array()): void
    {
        $message = '<comment>[command]</comment> '.$message;
        $this->logger->debug($message, $context);
    }

    public function run($commandline, callable $callback = null, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        $process = new Process(
            $commandline,
            $cwd,
            $env,
            $input,
            $timeout
        );

        $helper = new DebugFormatterHelper();
        $output = $this->output;

        $process->run(function($type,$buffer) use($helper,$output,$process,$callback) {
            if(is_callable($callback)){
                call_user_func($callback,$type,$buffer);
            }
            $contents = $helper->progress(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            );
            $output->writeln($contents);
        });

        return $process;
    }
}
