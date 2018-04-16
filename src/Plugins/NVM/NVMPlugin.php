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

namespace Dotfiles\Plugins\NVM;

use Dotfiles\Core\Plugin;

/**
 * NVM Plugin.
 */
class NVMPlugin extends Plugin
{
    protected function configure($configs)
    {
        $configs['nvm.temp_dir'] = '%dotfiles.temp_dir%/nvm';

        return $configs;
    }
}
