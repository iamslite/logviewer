<?php
/**
 * @file
 * Model class for a Magento Report
 */

namespace AppBundle\Model;

class PhpLog extends DatedLogfile
{
    public function __construct($filename = null, $basePath = null)
    {
        $basePath = empty($basePath) ? '/var/log' : $basePath;
        parent::__construct($filename, $basePath);
    }

    protected function splitContents($contents)
    {
        # Date marker: [10-Mar-2016 17:56:38 UTC]
        $split_contents = preg_split('#^(\[[-[:alnum:]]+ [:0-9]+ [[:alnum:]]+\])#m', $contents, -1, PREG_SPLIT_DELIM_CAPTURE);

        $log = array();

        // The first entry will be empty.
        reset($split_contents);
        $entry = '';

        while (($line = next($split_contents)) !== false)
        {
            $next_line = next($split_contents);

            if (empty($entry)) {
                $entry = $line . "\n" . $next_line;
            }
            else {
                // $line should be another date
                // Indented line
                if (substr($next_line, 0, 6) == ' PHP  ') {
                    $entry .= $next_line;
                }
                else if (substr($next_line, 0, 17) == ' PHP Stack trace:') {
                    $entry .= $next_line;
                }
                else {
                    $log[] = $entry;
                    $entry = $line . "\n" . $next_line;
                }
            }
        }

        return $log;
    }
}