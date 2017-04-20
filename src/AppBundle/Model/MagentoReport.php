<?php
/**
 * @file
 * Model class for a Magento Report
 */

namespace AppBundle\Model;

class MagentoReport extends Logfile
{
    public function __construct($filename = null, $basePath = null)
    {
        $basePath = empty($basePath) ? $_SERVER['DOCUMENT_ROOT'] . '/var/report' : $basePath;
        parent::__construct($filename, $basePath);
    }
}