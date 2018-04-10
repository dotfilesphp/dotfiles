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

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\SplFileInfo;

class Toolkit
{
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
     * Ensure that directory exists.
     *
     * @param string $file
     */
    public static function ensureFileDir($file): void
    {
        static::ensureDir(dirname($file));
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
    public static function flattenArray(array &$values, array $subnode = null, $path = null): void
    {
        if (null === $subnode) {
            $subnode = &$values;
        }
        foreach ($subnode as $key => $value) {
            if (is_array($value)) {
                $nodePath = $path ? $path.'.'.$key : $key;
                static::flattenArray($values, $value, $nodePath);
                if (null === $path) {
                    unset($values[$key]);
                }
            } elseif (null !== $path) {
                $values[$path.'.'.$key] = $value;
            }
        }
    }

    public static function getBaseDir()
    {
        $baseDir = getcwd();
        if (DOTFILES_PHAR_MODE) {
            $baseDir = str_replace(__FILE__, '', \Phar::running(false));
        }

        return $baseDir;
    }

    /**
     * @param string $path
     *
     * @return SplFileInfo
     */
    public static function getFileInfo(string $path)
    {
        $homeDir = getenv('HOME');
        $repoDir = getenv('REPO_DIR');
        $cwd = getcwd();
        if (false !== strpos($path, $homeDir)) {
            $relativePath = $homeDir;
        } elseif (false !== strpos($path, $repoDir)) {
            $relativePath = $repoDir;
        } elseif (false !== strpos($path, $cwd)) {
            $relativePath = $cwd;
        } else {
            $relativePath = dirname($path);
        }

        return new SplFileInfo($path, $relativePath, $relativePath.DIRECTORY_SEPARATOR.basename($path));
    }

    public static function loadDotEnv(): void
    {
        $cwd = static::getBaseDir();
        $files = array();
        if (is_file($file = $cwd.'/.env.dist')) {
            $files[] = $file;
        }
        if (is_file($file = $cwd.'/.env')) {
            $files[] = $file;
        }
        if (is_file($file = getenv('HOME').'/.dotfiles/.env')) {
            $files[] = $file;
        }

        if (count($files) > 0) {
            $env = new Dotenv();
            call_user_func_array(array($env, 'load'), $files);
        }
    }

    public static function normalizeValue($value)
    {
        // replace environment variables
        $pattern = '/%%([A-Z]*)%%/i';
        preg_match($pattern, $value, $match);
        if (isset($match[1])) {
            $value = str_replace($match[0], getenv($match[1]), $value);
        }

        return $value;
    }

    public static function normalizeValues($values)
    {
        foreach ($values as $section => $contents) {
            foreach ($contents as $key => $value) {
                $values[$section][$key] = static::normalizeValue($value);
            }
        }

        return $values;
    }

    public static function stripPath(string $path, $additionalPath = array())
    {
        $defaults = array(
            getenv('HOME') => '',
            getcwd() => '',
        );
        $path = strtr($path, array_merge($defaults, $additionalPath));

        return $path;
    }
}
