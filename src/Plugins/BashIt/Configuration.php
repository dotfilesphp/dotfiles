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

namespace Dotfiles\Plugins\BashIt;

use Dotfiles\Core\Config\DefinitionInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements DefinitionInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('bash_it');
        $root
            ->children()
                ->scalarNode('git_hosting')
                    ->info('Your place for hosting Git repos. Use this for private repos.')
                    ->defaultValue('git@git.domain.com')
                ->end()
                ->scalarNode('theme_name')
                    ->defaultValue('atomic')
                ->end()
                ->scalarNode('irc_client')
                    ->defaultValue('irssi')
                    ->info('Change this to your console based IRC client of choice.')
                ->end()
                ->scalarNode('todo')
                    ->defaultValue('t')
                    ->info('Set this to the command you use for todo.txt-cli')
                ->end()
                ->booleanNode('scm_check')
                    ->defaultTrue()
                    ->info('Set this to false to turn off version control status checking within the prompt for all themes')
                ->end()
                ->booleanNode('check_mail')
                    ->defaultFalse()
                    ->info('check mail when opening terminal.')
                ->end()
                ->scalarNode('short_hostname')
                    ->defaultNull()
                    ->info('Set Xterm/screen/Tmux title with only a short hostname.')
                ->end()
                ->scalarNode('short_user')
                    ->defaultNull()
                    ->info('Set Xterm/screen/Tmux title with only a short username.')
                ->end()
                ->scalarNode('short_term_line')
                    ->defaultFalse()
                    ->info('Set Xterm/screen/Tmux title with shortened command and directory.')
                ->end()
                ->booleanNode('automatic_reload')
                    ->info('Make Bash-it reload itself automatically after enabling or disabling aliases, plugins, and completions.')
                    ->defaultTrue()
                ->end()
                ->scalarNode('vcprompt_executable')
                    ->info('Set vcprompt executable path for scm advance info in prompt (demula theme)')
                    ->defaultNull()
                ->end()
                ->arrayNode('theme')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('show_clock_char')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('clock_char')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('clock_char_color')
                            ->defaultValue('$normal')
                        ->end()
                        ->booleanNode('show_clock')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('clock_color')
                            ->defaultValue('$normal')
                        ->end()
                        ->scalarNode('clock_format')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
