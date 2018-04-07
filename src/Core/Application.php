<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    const RELEASE_DATE = '@release_date@';

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        parent::__construct('dotfiles', static::VERSION);
        $this->getDefinition()->addOption(
            new InputOption('dry-run','-d',InputOption::VALUE_NONE,'Run command in test mode')
        );
    }

    public function getLongVersion()
    {
        return implode(' ',[
            static::VERSION,
            static::BRANCH_ALIAS_VERSION,
            static::RELEASE_DATE
        ]);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @param Container $container
     * @return Application
     */
    public function setContainer(Container $container): Application
    {
        $this->container = $container;
        return $this;
    }
}
