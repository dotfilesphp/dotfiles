<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was disstributed with this source code.
 */

namespace Dotfiles\Core\Command;


use Dotfiles\Core\Config\Config;
use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ClearCacheCommand extends Command implements CommandInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?string $name = null, Config $config, LoggerInterface $logger)
    {
        parent::__construct($name);
        $this->config = $config;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('clear-cache')
            ->setAliases(['cc'])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->config;
        $cacheDir = $config->get('dotfiles.cache_dir');

        $finder = Finder::create()
            ->in($cacheDir)
            ->files()
        ;

        $fs = new Filesystem();

        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach($finder->files() as $file){
            $fs->remove($file);
            $relPath = 'var/cache/'.$file->getRelativePathname();
            $this->logger->debug("-removed <comment>$relPath</comment>");
        }
    }
}
