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

use Dotfiles\Core\Config\Config;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    public const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';

    public const RELEASE_DATE = '@release_date@';

    public const VERSION = '@package_version@';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        Config $config,
        InputInterface $input,
        OutputInterface $output
    ) {
        parent::__construct('dotfiles', static::VERSION);

        $this->config = $config;
        $this->input = $input;
        $this->output = $output;

        $this->getDefinition()->addOption(
            new InputOption('dry-run', '-d', InputOption::VALUE_NONE, 'Only show which files would have been modified')
        );
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
        if (null === $input) {
            $input = $this->input;
        }
        if (null === $output) {
            $output = $this->output;
        }

        $dryRun = $input->hasParameterOption(array('--dry-run'), true);
        $this->config->set('dotfiles.dry_run', $dryRun);

        if(!getenv('DOTFILES_REPO_DIR') && ('dev' !== getenv('DOTFILES_ENV'))){
            $input = new StringInput('init');
        }
        return parent::run($input, $output);
    }
}
