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

namespace Dotfiles\Core\Util;

class Toolkit
{
    /**
     * Ensure that directory exists.
     *
     * @param string $dir
     */
    public static function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public static function ensureDotPath(string $relativePathName)
    {
        if (0 !== strpos($relativePathName, '.')) {
            $relativePathName = '.'.$relativePathName;
        }

        return $relativePathName;
    }

    /**
     * Ensure that directory for file exists.
     *
     * @param string $file
     */
    public static function ensureFileDir($file): void
    {
        static::ensureDir(dirname($file));
    }

    public static function flattenArray(array &$values, $prefix = null): void
    {
        if (null !== $prefix) {
            $values = array($prefix => $values);
        }
        static::doFlattenArray($values);
    }

    /**
     * @return mixed|string
     * @codeCoverageIgnore Can't tests phar mode
     */
    public static function getBaseDir()
    {
        $baseDir = getcwd();
        if (getenv('DOTFILES_PHAR_MODE')) {
            $baseDir = str_replace(__FILE__, '', \Phar::running(false));
        }

        return $baseDir;
    }

    public static function getRelativePath(string $file): string
    {
        $homeDir = getenv('DOTFILES_HOME_DIR');
        $backupDir = getenv('DOTFILES_BACKUP_DIR');

        if (false !== strpos($file, $homeDir)) {
            return str_replace($homeDir.DIRECTORY_SEPARATOR, '', $file);
        }

        if (false !== strpos($file, $backupDir)) {
            return str_replace(dirname($backupDir).'/', '', $file);
        }

        return $file;
    }

    /**
     * Flattens an nested array of translations.
     *
     * The scheme used is:
     *   'key' => array('key2' => array('key3' => 'value'))
     * Becomes:
     *   'key.key2.key3' => 'value'
     *
     * This function takes an array by reference and will modify it
     *
     * @param array  &$values The array that will be flattened
     * @param array  $subnode Current subnode being parsed, used internally for recursive calls
     * @param string $path    Current path being parsed, used internally for recursive calls
     */
    private static function doFlattenArray(array &$values, array $subnode = null, $path = null): void
    {
        if (null === $subnode) {
            $subnode = &$values;
        }
        foreach ($subnode as $key => $value) {
            if (is_array($value)) {
                $nodePath = $path ? $path.'.'.$key : $key;
                static::doFlattenArray($values, $value, $nodePath);
                if (null === $path) {
                    unset($values[$key]);
                }
            } elseif (null !== $path) {
                $values[$path.'.'.$key] = $value;
            }
        }
    }
}
