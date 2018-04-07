<?php
/**
 * Created by PhpStorm.
 * User: toni
 * Date: 4/6/18
 * Time: 12:29 PM
 */

namespace Dotfiles\Core\Util;


class Toolkit
{
    public static function normalizeValues($values)
    {
        foreach($values as $section=> $contents){
            foreach($contents as $key=>$value){
                $values[$section][$key] = static::normalizeValue($value);
            }
        }
        return $values;
    }

    public static function normalizeValue($value)
    {
        // replace environment variables
        $pattern = '/%%([A-Z]*)%%/i';
        preg_match($pattern,$value,$match);
        if(isset($match[1])){
            $value = str_replace($match[0],getenv($match[1]),$value);
        }
        return $value;
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
     * @param array  $subnode   Current subnode being parsed, used internally for recursive calls
     * @param string $path      Current path being parsed, used internally for recursive calls
     */
    public static function flattenArray(array &$values, array $subnode = null, $path = null)
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

    public static function ensureDir(string $dir)
    {
        if(!is_dir($dir)){
            mkdir($dir,0755,true);
        }
    }

    /**
     * Ensure that directory exists
     *
     * @param string $file
     */
    public static function ensureFileDir($file)
    {
        static::ensureDir(dirname($file));
    }

    public static function getBaseDir()
    {
        $baseDir = realpath(dirname(__DIR__.'/../../../src'));
        if(false !== strpos($dir = \Phar::running(),'phar:///')){
            $baseDir = str_replace('/dotfiles.phar','',\Phar::running(false));
        }
        return $baseDir;
    }

    public static function ensureDotPath(string $relativePathName)
    {
        if(0 !== strpos($relativePathName,'.')){
            $relativePathName = '.'.$relativePathName;
        }
        return $relativePathName;
    }

    public static function stripPath(string $path)
    {
        return substr($path,strrpos($path,DIRECTORY_SEPARATOR),strlen($path));
    }
}
