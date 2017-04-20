<?php
/**
 * @file
 * Class for finding Magento Report files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;

class MagentoReportFinder extends LogfileFinder
{
    public function __construct($mask = '*', $class = '\\AppBundle\\Model\\MagnentoReport', $basePaths = array(), \AppBundle\Model\Project $project)
    {
        if (empty($basePaths)) {
            $basePaths = array('${PROJECTROOT}/http/var/report');
        }

        parent::__construct($mask, $basePaths, $class, $project);
    }

    public function getName()
    {
        return 'Magento Report';
    }
}