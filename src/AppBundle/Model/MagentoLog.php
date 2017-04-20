<?php
/**
 * @file
 * Model class for a Magento Log file
 */

namespace AppBundle\Model;

class MagentoLog extends Logfile
{
    public function __construct($filename = null, $basePath = null)
    {
        $basePath = empty($basePath) ? $_SERVER['DOCUMENT_ROOT'] . '/var/log' : $basePath;
        parent::__construct($filename, $basePath);
    }
}