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

namespace Dotfiles\Core;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    public const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    public const RELEASE_DATE = '@release_date@';
    public const VERSION = '@package_version@';

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        parent::__construct('dotfiles', static::VERSION);
        $this->getDefinition()->addOption(
            new InputOption('dry-run', '-d', InputOption::VALUE_NONE, 'Only show which files would have been modified')
        );
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getLongVersion()
    {
        return implode(' ', array(
            static::VERSION,
            static::BRANCH_ALIAS_VERSION,
            static::RELEASE_DATE,
        ));
    }

    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $dryRun = $input->hasParameterOption(array('--dry-run'), true);
        $this->container->get('dotfiles.config')->set('dotfiles.dry_run', $dryRun);

        return parent::run($input, $output);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return Application
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
