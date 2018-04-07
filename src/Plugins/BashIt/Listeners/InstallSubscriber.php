<?php

namespace Dotfiles\Plugins\BashIt\Listeners;

use Dotfiles\Core\Util\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Dotfiles\Core\Event\Dispatcher;
use Dotfiles\Plugins\Bash\Event\ReloadBashConfigEvent;
use Dotfiles\Core\Config\Config;
use Symfony\Component\Finder\Finder;

class InstallSubscriber implements EventSubscriberInterface
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Config
     */
    private $config;

    private $installDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Dispatcher $dispatcher, Config $config, LoggerInterface $logger)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param mixed $installDir
     */
    public function setInstallDir($installDir): void
    {
        $this->installDir = $installDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            ReloadBashConfigEvent::NAME => 'onInstallEvent'
        ];
    }

    public function onInstallEvent(ReloadBashConfigEvent $event)
    {
        $this->logger->info('Installing <comment>Bash-IT</comment>');

        $bashItConfig = $this->renderConfig();
        $target = $this->installDir.'/bash-it.bash';
        file_put_contents($target,$bashItConfig,LOCK_EX);
        $this->logger->info("BashIt configuration written in: <comment>$target</comment>");

        $footer = <<<EOC
# Load Bash It
source "{$this->installDir}/bash-it.bash"
EOC;
        $event->addFooterConfig($footer);
        $this->copySource();
    }

    private function copySource()
    {
        $fs = new Filesystem();
        $source = __DIR__.'/../../../../vendor/bash-it/bash-it';
        $finder = Finder::create()
            ->in($source)
            ->ignoreVCS(true)
            ->exclude('test')
            ->exclude('test_lib')
        ;
        $target = $this->installDir.'/vendor/bash-it';
        $fs->mirror($source,$target,$finder,['override'=>true]);
    }

    private function renderConfig()
    {
        $config = $this->config;
        $exports = [
            'GIT_HOSTING'   => $config->get('bash_it.git_hosting'),
            'BASH_IT_THEME' => $config->get('bash_it.theme_name'),
            'IRC_CLIENT'    => $config->get('bash_it.irc_client'),
            'TODO'          => $config->get('bash_it.todo'),
            'SCM_CHECK'     => $config->get('bash_it.scm_check'),
            'BASH_IT_AUTOMATIC_RELOAD_AFTER_CONFIG_CHANGE' => $config->get('bash_it.automatic_reload'),

            // theme section
            'THEME_SHOW_CLOCK_CHAR' => $config->get('bash_it.theme.show_clock_char'),
            'THEME_CLOCK_CHAR_COLOR' => $config->get('bash_it.theme.clock_char_color'),
            'THEME_SHOW_CLOCK' => $config->get('bash_it.theme.show_clock'),
            'THEME_SHOW_CLOCK_COLOR' => $config->get('bash_it.theme.clock_color'),
            'THEME_CLOCK_FORMAT' => $config->get('bash_it.theme.clock_format'),
        ];



        if(!is_null($test = $config->get('bash_it.short_hostname'))){
            $exports['SHORT_HOSTNAME'] = $test;
        }

        if(!is_null($test = $config->get('bash_it.short_user'))){
            $exports['SHORT_USER'] = $test;
        }

        if($config->get('bash_it.short_term_line')){
            $exports['SHORT_TERM_LINE'] = true;
        }

        if(!is_null($test=$config->get('bash_it.vcprompt_executable'))){
            $exports['VCPROMPT_EXECUTABLE'] = $test;
        }

        // theme
        if(!is_null($test = $config->get('bash_it.theme.clock_char'))){
            $exports['THEME_CLOCK_CHAR'] = $test;
        }

        ksort($exports);
        // begin generate contents
        $targetDir = $this->installDir.'/vendor/bash-it';
        $contents = [
            "export BASH_IT=\"${targetDir}\""
        ];
        foreach($exports as $name=>$value){
            if(is_string($value)){
                $value = '"'.$value.'"';
            }
            if(is_bool($value)){
                $value = $value ? 'true':'false';
            }
            $contents[] = "export $name=$value";
            $this->logger->debug("+bash-config: export <comment>$name</comment> = <comment>$value</comment>");
        }
        if(!$config->get('bash_it.check_mail')){
            $contents[] = 'unset MAILCHECK';
            $this->logger->debug('+bash-config: unset <comment>MAILCHECK</comment>');
        }

        $contents[] = 'source "$BASH_IT"/bash_it.sh';
        $contents[] = "\n";
        return implode("\n",$contents);
    }
}
