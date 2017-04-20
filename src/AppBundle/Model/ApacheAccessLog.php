<?php
/**
 * @file
 * Model class for an Apache Access Log file
 */

namespace AppBundle\Model;

class ApacheAccessLog extends Logfile
{
    public function __construct($filename = null, $basePath = null)
    {
        # This *really* needs to be configurable!
        $basePath = empty($basePath) ? '/var/log/apache2' : $basePath;
        parent::__construct($filename, $basePath);
    }
}