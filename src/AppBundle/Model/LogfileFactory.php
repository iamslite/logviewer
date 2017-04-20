<?php
/**
 * @file
 * Factory class for obtaining Log files
 */

namespace AppBundle\Model;

use Symfony\Component\Finder\Glob;

class LogfileFactory 
{
    public static function getFromFilename($class, $path, $basepath = null, $mask = '*')
    {
        $object = new $class(null, $basepath);

        $basepath = $object->getBasePath();

        if (strpos($path, $basepath) === 0) {
            // Strip the directory separator as well.
            $path = substr($path, strlen($basepath) + 1);
        }

        $extensions = $class::getExtensions();

        $suffixes = array();

        $extensions['base'][] = '';

        foreach ($extensions['base'] as $extension) {
            $suffix = (!empty($extension) ? '.' . $extension : '');

            $suffixes[] = $suffix;

            foreach ($extensions['compression'] as $compression => $type) {
                $suffixes[] = $suffix . '.' . $compression;
            }
        }

        // Longest first.
        arsort($suffixes);

        $mask_re = Glob::toRegex($mask);

        foreach ($suffixes as $suffix) {
            $suffix_length = strlen($suffix);
            $test_suffix = ($suffix_length > 0) ? substr($path, 0 - $suffix_length) : '';

            if ($test_suffix == $suffix) {
                // Now check the remainder of the filename matches the regexp
                $test_path = substr($path, 0, strlen($path) - $suffix_length);

                if (preg_match($mask_re, $test_path) == 1) {
                    $path = $test_path;

                    $object->setFilename($path);

                    return $object;
                }
            }
        }

        return false;
    }
}