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

namespace Dotfiles\Core\DI;

use Dotfiles\Core\Exceptions\InvalidArgumentException;

/**
 * Provide parameters during runtime.
 */
class Parameters implements \ArrayAccess
{
    private $configs = array();

    public function all()
    {
        return $this->configs;
    }

    public function get($name)
    {
        if (!array_key_exists($name, $this->configs)) {
            throw new InvalidArgumentException('Parameters key "'.$name.'" not exists.');
        }

        return $this->configs[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->configs[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->configs[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        $this->configs[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        unset($this->configs[$offset]);
    }

    public function set($name, $value): self
    {
        $this->configs[$name] = $value;

        return $this;
    }

    /**
     * @param array $configs
     */
    public function setConfigs(array $configs): void
    {
        $this->configs = $configs;
    }
}
