<?php

namespace Dotfiles\Plugins\Bash\Config;

use Dotfiles\Core\Config\ConfigInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
class Definition implements ConfigInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root = $builder->root('bash');
        return $builder;
    }
}
