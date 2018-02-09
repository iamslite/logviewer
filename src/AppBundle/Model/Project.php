<?php
/**
 * @file
 * A Project definition
 */

namespace AppBundle\Model;

abstract class Project implements ProjectInterface
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

    abstract protected function loadData() 
    {
    }

    public function getCurrentHost()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function getValue($name)
    {
        return $this->$name;
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

    public static function canConfigure($projectRoot) {
        return false;
    }
}