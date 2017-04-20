<?php
/**
 * @file
 * Class for finding PHP Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;

class MagentoFinder extends LogfileFinder
{
    public function __construct($mask = '*', $class = '\\AppBundle\\Model\\MagnentoLog', $basePaths = array(), \AppBundle\Model\Project $project)
    {
        if (empty($basePaths)) {
            $basePaths = array('${PROJECTROOT}/http/var/log');
        }

        parent::__construct($mask, $basePaths, $class, $project);
    }

    public function getName()
    {
        return 'Magento Log';
    }
}