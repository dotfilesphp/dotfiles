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
use Symfony\Component\Process\Process;

/**
 * ProcessRunner help to create Process so it can be easily
 * mock later in testing environment.
 * This class also have a predefined DebugFormatterHelper.
 */
class ProcessRunner
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * ProcessRunner constructor.
     *
     * @param LoggerInterface $logger
     * @param OutputInterface $output
     */
    public function __construct(
        LoggerInterface $logger,
        OutputInterface $output
    ) {
        $this->logger = $logger;
        $this->output = $output;
    }

    /**
     * Creates process and run with predefined DebugFormatterHelper.
     *
     * @param string         $commandline
     * @param callable|null  $callback
     * @param string|null    $cwd
     * @param array|null     $env
     * @param null           $input
     * @param int|float|null $timeout     The timeout in seconds or null to disable
     *
     * @see     Process
     *
     * @return Process
     */
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
        $output->writeln($helper->start(
            spl_object_hash($process),
            'Executing: '.$commandline,
            'STARTED'
        ));
        $process->run(function ($type, $buffer) use ($helper,$output,$process,$callback) {
            if (is_callable($callback)) {
                call_user_func($callback, $type, $buffer);
            }
            $contents = $helper->progress(
                spl_object_hash($process),
                $buffer,
                Process::ERR === $type
            );
            $output->write($contents);
        });

        return $process;
    }
}
