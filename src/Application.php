<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toni\Dotfiles;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;

use Toni\Dotfiles\Command\InstallCommand;
use Toni\Dotfiles\Command\CommandInterface;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';
    const BRANCH_ALIAS_VERSION = '@package_branch_alias_version@';
    const RELEASE_DATE = '@release_date@';

    public function __construct()
    {
        parent::__construct('dotfiles', static::VERSION);
        $this->buildCommands();
    }

    public function buildCommands()
    {
        $commands = array();
        $files = Finder::create()
          ->in(__DIR__.'/Command')
          ->name('*Command.php')
          ->files()
        ;

        foreach($files as $file){
            $class = 'Toni\\Dotfiles\\Command\\'.str_replace('.php','',$file->getFileName());
            if(class_exists($class)){
              $r = new \ReflectionClass($class);
              if($r->implementsInterface(CommandInterface::class)){
                  $command = new $class();
                  $commands[] = $command;
              }
            }
        }
        $this->addCommands($commands);
        $this->getDefinition()->addOption(
          new InputOption('force', '-f', InputOption::VALUE_NONE, 'Force command to be executed')
        );
        $this->getDefinition()->addOption(
          new InputOption('reload', '-r', InputOption::VALUE_NONE, 'Only reload configuration')
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
}
