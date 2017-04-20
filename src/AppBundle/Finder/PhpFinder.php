<?php
/**
 * @file
 * Class for finding PHP Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;

class PhpFinder extends LogfileFinder
{
    public function __construct($mask = '*php*', $class = '\\AppBundle\\Model\\PhpLog', $basePaths = array(), \AppBundle\Model\Project $project)
    {
        if (empty($basePaths)) {
            $basePaths = array('/var/log');
        }

        parent::__construct($mask, $basePaths, $class, $project);
    }

    public function getName()
    {
        return 'PHP';
    }
}