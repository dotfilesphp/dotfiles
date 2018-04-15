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

namespace Dotfiles\Core\Console;

use Dotfiles\Core\Command\ShellCommand;
use Dotfiles\Core\DI\Parameters;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';

    const RELEASE_DATE = '@release_date@';

    const VERSION = '@package_version@';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var bool
     */
    private $shellIsRunning = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        Parameters $parameters,
        InputInterface $input,
        OutputInterface $output
    ) {
        parent::__construct('dotfiles', static::VERSION);

        $this->parameters = $parameters;
        $this->input = $input;
        $this->output = $output;

        $this->getDefinition()->addOption(
            new InputOption('dry-run', '-d', InputOption::VALUE_NONE, 'Only show which files would have been modified')
        );
        $this->add(new ShellCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function getLongVersion()
    {
        return implode(' ', array(
            static::VERSION,
            static::BRANCH_ALIAS_VERSION,
            static::RELEASE_DATE,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        if (
            !getenv('DOTFILES_BACKUP_DIR')
            && ('dev' !== getenv('DOTFILES_ENV'))
        ) {
            return $this->find('init')->run($input, $output);
        }
        $this->setDefaultCommand('shell');

        return parent::run($input, $output);
    }
}
