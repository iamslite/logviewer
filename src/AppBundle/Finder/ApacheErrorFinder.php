<?php
/**
 * @file
 * Class for finding Apache Error Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

use AppBundle\Model;

class ApacheErrorFinder extends ApacheAccessFinder
{
    public function __construct($mask = '*', $class = '\\AppBundle\\Model\\ApacheErrorLog', $basePaths = array(), \AppBundle\Model\Project $project)
    {
        parent::__construct($mask, $class, $basePaths, $project);

        $this->type = 'Error';
    }

    public function getName()
    {
        return 'Error';
    }
}