<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toni\Dotfiles\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Toni\Dotfiles\Command\CommandInterface;
use Toni\Dotfiles\Util\Config;

class BashCommand extends Command implements CommandInterface
{
    public function configure()
    {
        $this->setName('install:bash');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $targetFile = getenv('TARGET_DIR').DIRECTORY_SEPARATOR.'bashrc';
        $force = $input->getOption('force');
        if(is_file($targetFile) && !$force){
            $output->writeln("Bash dotfiles already configured in: <comment>${targetFile}</comment>");
        }
        $this->doInstallBashIt($input,$output);
        $this->doGenerateBashConfig($output,$targetFile);
    }

    public function parseEnv()
    {
        $vars = getenv('SYMFONY_DOTENV_VARS');
        $vars = explode(',',$vars);
        $keys = ['BASH','GIT'];
        $env = [];
        foreach($vars as $name){
            $exp = explode('_',$name);
            $key = $exp[0];
            $arrKey = implode('_',$exp);
            if(in_array($key,$keys)){
                array_shift($exp);
                $arrKey = implode('_',$exp);
            }else{
                $key = 'GLOBAL';
            }
            $env[$key][$arrKey] = getenv($name);
        }
        return $env;
    }

    public function getDotfilesConfiguration()
    {
        return [
            'BASH' => [
                'THEME_SHOW_SUDO' => true,
                'THEME_SHOW_SCM' => true,
                'THEME_SHOW_CLOCK' => false,
                'THEME_SHOW_EXITCODE' => false,
                'THEME_SHOW_RUBY' => false,
                'BASH_IT_THEME' => 'atomic',
            ]
        ];
    }

    private function doGenerateBashConfig(OutputInterface $output,$targetFile)
    {
        $targetDir = getenv('TARGET_DIR');
        $env = $this->parseEnv();
        $config = $env['BASH'];
        $exports = ['### START_DOTFILES ###'];
        $exports[] = "unset MAILCHECK";
        $exports[] = "export PATH=\"${targetDir}/bin:\$PATH\"";
        $exports[] = "export BASH_IT=\"${targetDir}/vendor/bash-it\"";
        $escape = ['BASH_IT_INSTALL'];
        foreach($config as $name=>$value){
            if(in_array($name,$escape)){
                continue;
            }
            if(is_bool($value)){
                $value = (string)$value;
            }
            if(is_string($value)){
              $value = '"'.$value.'"';
            }
            $exports[] = 'export '.$name.'='.$value;
        }
        $exports[] = 'source "$BASH_IT"/bash_it.sh';
        $exports[] = "### END_DOTFILES ###\n";
        $contents = implode(PHP_EOL,$exports);
        file_put_contents($targetFile, $contents,LOCK_EX);
        $output->writeln("Bash dotfiles configured in: <comment>${targetFile}</comment>");

        // patching .bashrc
        $bashrcFile = getenv('HOME').DIRECTORY_SEPARATOR.'/.bashrc';
        $bashrc = file_get_contents($bashrcFile);
    }

    private function doInstallBashIt(InputInterface $input, OutputInterface $output)
    {
        $config = Config::create();
        $src = realpath(__DIR__.'/../../vendor/bash-it/bash-it');
        $target = $config->get('dotfiles','install_dir').'/vendor/bash-it';
        $output->writeln("Copy bash-it to: <comment>${target}</comment>");
        $options['override'] = true;
        $fs = new Filesystem();
        $fs->mirror($src, $target, null, $options);

        $info = [];
        $error = [];
        $cmd = $target.'/install.sh --silent --no-modify-config';
        $output->writeln("Installing bash-it...");
        $process = new Process($cmd);
        $process->run(function($type, $buffer) use (&$info,&$error) {
            if (Process::ERR === $type) {
                $error[] = $buffer;
            } else {
                $info[] = $buffer;
            }
        });
        if(count($error) > 0){
            $error = implode(' ',$error);
            $message = "Command <options=bold>${cmd}</> failed.\nError: ${error}\n";
            $message = $output->getFormatter()->format($message);
            $output->writeln($message);
            throw new RuntimeException("Command ${cmd} failed");
        }
    }
}
