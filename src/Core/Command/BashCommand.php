<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

use Dotfiles\Core\Command\CommandInterface;
use Dotfiles\Core\Util\Config;

class BashCommand extends Command implements CommandInterface
{
    private $exports = [];

    public function configure()
    {
        $this->setName('install:bash');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $config = Config::create();
        $installDir = $config->get('dotfiles','install_dir');
        $force = $input->getOption('force');
        if(
            !$force
            && is_dir($dir=$installDir.'/vendor/bash-it'))
        {
            $output->writeln("Bash-It already installed in: <comment>${dir}</comment>");
        }else{
            $this->doInstallBashIt($input,$output);
        }

        // start generate bashrc
        $targetFile = $installDir.DIRECTORY_SEPARATOR.'bashrc';
        if(is_file($targetFile) && !$force){
            $output->writeln("Bash already configured in: <comment>${targetFile}</comment>");
        }else{
            $this->doGenerateBashConfig($output,$targetFile);
        }
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

    /**
     * Generate install_dir/bashrc file
     */
    private function doGenerateBashConfig(OutputInterface $output,$targetFile)
    {
        $config = Config::create();
        $targetDir = $config['dotfiles']['install_dir'];
        $exports = $config['bash_exports'];
        $contents = [];
        if($config->get('bash-it','install')){
            $exports = array_merge_recursive($exports,$config['bash-it']);
            $contents[] = "unset MAILCHECK";
        }
        $ignores = ['install'];
        foreach($exports as $key => $value){
            if(in_array($key,$ignores)){
                continue;
            }
            if(is_bool($value)){
                $value = $value ? 'true':'false';
            }elseif(is_string($value)){
                $value = '"'.$value.'"';
            }

            $contents[] = "export ${key}=${value}";
        }
        $contents[] = "export PATH=\"${targetDir}/bin:\$PATH\"";
        $contents[] = "export BASH_IT=${targetDir}/vendor/bash-it";
        $contents[] = 'source "$BASH_IT"/bash_it.sh';

        $contents = implode(PHP_EOL, $contents);
        $contents = <<<EOC

### START_DOTFILES ###
$contents
### END_DOTFILES ###

EOC;
        file_put_contents($targetFile, $contents,LOCK_EX);

        $this->doPatchBashRCFile($output,$targetFile);
    }

    private function doPatchBashRCFile(OutputInterface $output,$targetFile)
    {
        $homeDir = getenv('HOME');
        $osType = getenv('OSTYPE');
        $patch = <<<EOC
### START_DOTFILES ###
source ${targetFile}
### END_DOTFILES ###
EOC;
        $patch = str_replace(getenv("HOME"),'"$HOME"',$patch);
        if($osType=='darwin'){
            $bashrcFile = $homeDir.DIRECTORY_SEPARATOR.'.bash_profile';
        }else{
            $bashrcFile = $homeDir.DIRECTORY_SEPARATOR.'.bashrc';
        }
        if(!is_file($bashrcFile)){
            touch($bashrcFile);
        }
        $bashrcContents = file_get_contents($bashrcFile);
        $pattern = '/###\sSTART_DOTFILES\s###(.*)###\sEND_DOTFILES\s###/is';
        if(!preg_match($pattern, $bashrcContents,$match)){
            $bashrcContents .= $patch;
        }else{
            $bashrcContents = str_replace($match[0],$patch,$bashrcContents);
        }
        if(false===strpos($bashrcContents, "\n",-1)){
            $bashrcContents .= "\n ";
        }
        file_put_contents($bashrcFile,$bashrcContents,LOCK_EX);
        $output->writeln('Bash config generated in: <comment>'.$bashrcFile.'</comment>');
    }

    private function doInstallBashIt(InputInterface $input, OutputInterface $output)
    {
        $config = Config::create();
        $src = __DIR__ . '/../../vendor/bash-it/bash-it';
        $target = $config->get('dotfiles','install_dir').'/vendor/bash-it';
        $output->writeln("Copy bash-it to: <comment>${target}</comment>");
        $options['override'] = true;
        $fs = new Filesystem();
        $fs->mirror($src, $target, null, $options);

        $info = [];
        $error = [];
        if(!is_executable($test=$target.'/install.sh')){
            chmod($test,0777);
        }
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
