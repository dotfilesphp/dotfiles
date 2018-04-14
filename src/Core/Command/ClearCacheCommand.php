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

namespace Dotfiles\Core\Command;

use Dotfiles\Core\ApplicationFactory;
use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ClearCacheCommand.
 */
class ClearCacheCommand extends Command implements CommandInterface
{
    /**
     * @var ApplicationFactory
     */
    private $factory;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Parameters
     */
    private $parameters;

    public function __construct(
        ?string $name = null,
        Parameters $parameters,
        LoggerInterface $logger,
        ApplicationFactory $factory
    ) {
        parent::__construct($name);
        $this->parameters = $parameters;
        $this->logger = $logger;
        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this
            ->setName('clear-cache')
            ->setAliases(array('cc', 'cache-clear'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $config = $this->parameters;
        $cacheDir = $config->get('dotfiles.cache_dir');

        if (!is_dir($cacheDir)) {
            return;
        }

        $output->writeln("Cleaning cache in <comment>$cacheDir</comment>");
        $logger = $this->logger;
        $fs = new Filesystem();
        $fs->removeDir($cacheDir, function ($directory) use ($logger): void {
            $message = "-removed <comment>$directory</comment>";
            $this->logger->debug($message);
        });
        $this->factory->boot();
    }
}
