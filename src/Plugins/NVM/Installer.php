<?php

/*
 * This file is part of the dotfiles project.
 *
 *     (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Plugins\NVM;

use Dotfiles\Core\DI\Parameters;
use Dotfiles\Core\Processor\Patcher;
use Dotfiles\Core\Processor\ProcessRunner;
use Dotfiles\Core\Util\Downloader;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Installer.
 */
class Installer
{
    /**
     * @var Downloader
     */
    private $downloader;

    /**
     * @var string
     */
    private $installScript;

    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var Patcher
     */
    private $patcher;

    /**
     * @var ProcessRunner
     */
    private $processor;

    public function __construct(
        Parameters $parameters,
        Patcher $patcher,
        Downloader $downloader,
        ProcessRunner $processor,
        OutputInterface $output
    ) {
        $this->parameters = $parameters;
        $this->patcher = $patcher;
        $this->downloader = $downloader;
        $this->processor = $processor;
        $this->output = $output;
        $this->installScript = $parameters->get('dotfiles.temp_dir').'/nvm/installer.sh';
    }

    public function getBashPatch()
    {
        $installDir = $this->parameters->get('nvm.install_dir');

        return <<<EOC
# > NVM patch
 export NVM_DIR="$installDir/.nvm"
 [ -s "\$NVM_DIR/nvm.sh" ] && \. "\$NVM_DIR/nvm.sh"
# < NVM patch
EOC;
    }

    public function install()
    {
        if ($this->downloadInstallScript()) {
            $this->doInstall();
        }
    }

    private function doInstall()
    {
        $temp = $this->parameters->get('dotfiles.temp_dir');
        $home = $temp.'/nvm';
        $env = array(
            // temporary home directory
            'HOME' => $home,

            // nvm install location
            'NVM_DIR' => $this->parameters->get('nvm.install_dir'),
        );

        $runner = $this->processor;
        $runner->run(
            'bash '.$this->installScript,
            null,//callback
            null, //cwd
            $env
        );

        // ask patcher to run, to add nvm bash config
        $this->patcher->run();
    }

    private function downloadInstallScript()
    {
        $downloader = $this->downloader;

        $url = $this->getInstallScriptUrl();

        try {
            $downloader->run($url, $this->installScript);

            return true;
        } catch (\Exception $exception) {
            $this->output->writeln($exception->getMessage());
            $this->output->writeln('<error>Aborting installation, download error');

            return false;
        }
    }

    private function getInstallScriptUrl()
    {
        $installUrl = 'https://raw.githubusercontent.com/creationix/nvm/{VERSION}/install.sh';
        $command = 'git ls-remote --tags git://github.com/creationix/nvm.git | sort -t \'/\' -k 3 -V';

        $process = $this
            ->processor
            ->run($command)
        ;
        $output = $process->getOutput();
        $pattern = '/v[0-9\.]+/im';
        preg_match_all($pattern, $output, $matches);
        $versions = $matches[0];
        $current = $versions[count($versions) - 1];
        $url = str_replace('{VERSION}', $current, $installUrl);

        return $url;
    }
}
