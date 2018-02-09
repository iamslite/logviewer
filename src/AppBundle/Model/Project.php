<?php
/**
 * @file
 * A Project definition
 */

namespace AppBundle\Model;

class Project 
{
    /**
     * @var String Location of the project.
     */
    protected $project_root;

    public function __construct($project_root = null) 
    {
        if (empty($project_root)) {
            $project_root = $_SERVER['DOCUMENT_ROOT'] . '/../';
        }

        $this->project_root = $project_root;
    }

    abstract public function loadData() 
    {
    }

    public function getCurrentHost()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function __isset($name)
    {
        $value = $this->__get($name);

        // Slightly odd way to look at things, but ....
        return ($value !== null);
    }

    public function getProjectRoot()
    {
        return $this->project_root;
    }
}