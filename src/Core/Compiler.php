<?php

/*
 * This file is part of the dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dotfiles\Core;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Seld\PharUtils\Timestamps;

class Compiler
{
    private $version;
    private $branchAliasVersion = '';
    private $versionDate;

    public function compile($pharFile = 'dotfiles.phar')
    {
        if(file_exists($pharFile)){
            unlink($pharFile);
        }

        $this->setupVersion();
        $this->generatePhar($pharFile);
    }

    private function setupVersion()
    {
        $process = new Process('git log --pretty="%H" -n1 HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }
        $this->version = trim($process->getOutput());

        $process = new Process('git log -n1 --pretty=%ci HEAD', __DIR__);
        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure to run compile from composer git repository clone and that git binary is available.');
        }

        $this->versionDate = new \DateTime(trim($process->getOutput()));
        $this->versionDate->setTimezone(new \DateTimeZone('UTC'));
        $process = new Process('git describe --tags --exact-match HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        } else {
            // get branch-alias defined in composer.json for dev-master (if any)
            $localConfig = __DIR__ . '/../../composer.json';
            $contents = file_get_contents($localConfig);
            $json = json_decode($contents,true);
            if (isset($json['extra']['branch-alias']['dev-master'])) {
                $this->branchAliasVersion = $json['extra']['branch-alias']['dev-master'];
            }
        }

    }

    private function generatePhar($pharFile="dotfiles.phar")
    {
        $phar = new \Phar($pharFile, 0, 'dotfiles.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $finderSort = function ($a, $b) {
            return strcmp(strtr($a->getRealPath(), '\\', '/'), strtr($b->getRealPath(), '\\', '/'));
        };

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.yaml')
            ->name('*.yml')
            ->exclude([
                'Tests'
            ])
            ->notName('Compiler.php')
            ->in(__DIR__)
            ->sort($finderSort)
        ;
        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('LICENSE')
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->in(__DIR__ . '/../../vendor/symfony')
            ->in(__DIR__ . '/../../vendor/composer')
            ->in(__DIR__ . '/../../vendor/myclabs')
            ->in(__DIR__ . '/../../vendor/psr')
            ->sort($finderSort)
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder->files()
            ->ignoreVCS(true)
            ->exclude('Tests')
            ->exclude('tests')
            ->exclude('docs')
            ->name('*.yaml')
            ->name('*.yml')
            ->name('*.php')
            ->in(__DIR__ . '/../Plugins')
            ->sort($finderSort)
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../../vendor/autoload.php'));
        $this->addDotfilesBin($phar);
        $phar->setStub($this->getStub());
        $phar->stopBuffering();

        unset($phar);

        $util = new Timestamps($pharFile);
        $util->updateTimestamps($this->versionDate);
        $util->save($pharFile, \Phar::SHA1);
    }

    private function getStub()
    {
        $stub = <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of dotfiles project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

// Avoid APC causing random fatal errors per https://github.com/composer/composer/issues/264
if (extension_loaded('apc') && ini_get('apc.enable_cli') && ini_get('apc.cache_by_default')) {
    if (version_compare(phpversion('apc'), '3.0.12', '>=')) {
        ini_set('apc.cache_by_default', 0);
    } else {
        fwrite(STDERR, 'Warning: APC <= 3.0.12 may cause fatal errors when running composer commands.'.PHP_EOL);
        fwrite(STDERR, 'Update APC, or set apc.enable_cli or apc.cache_by_default to 0 in your php.ini.'.PHP_EOL);
    }
}

Phar::mapPhar('dotfiles.phar');

EOF;

        // add warning once the phar is older than 60 days
        if (preg_match('{^[a-f0-9]+$}', $this->version)) {
            $warningTime = $this->versionDate->format('U') + 60 * 86400;
            $stub .= "define('COMPOSER_DEV_WARNING_TIME', $warningTime);\n";
        }

        return $stub . <<<'EOF'
require 'phar://dotfiles.phar/bin/dotfiles';

__HALT_COMPILER();
EOF;
    }

    private function addDotfilesBin($phar)
    {
        $content = file_get_contents(__DIR__ . '/../../bin/dotfiles');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/dotfiles', $content);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = $this->getRelativeFilePath($file);
        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".$content."\n";
        }

        if ($path === 'src/Core/Application.php') {
            $content = str_replace('@package_version@', $this->version, $content);
            $content = str_replace('@package_branch_alias_version@', $this->branchAliasVersion, $content);
            $content = str_replace('@release_date@', $this->versionDate->format('Y-m-d H:i:s'), $content);
        }
        $phar->addFromString($path, $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    /**
     * @param  \SplFileInfo $file
     * @return string
     */
    private function getRelativeFilePath($file)
    {
        $realPath = $file->getRealPath();
        $pathPrefix = dirname(dirname(__DIR__ )).DIRECTORY_SEPARATOR;
        $pos = strpos($realPath, $pathPrefix);
        $relativePath = ($pos !== false) ? substr_replace($realPath, '', $pos, strlen($pathPrefix)) : $realPath;
	    return strtr($relativePath, '\\', '/');
    }
}
