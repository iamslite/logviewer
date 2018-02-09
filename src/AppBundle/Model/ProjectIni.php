<?php
/**
 * @file
 * A Project definition based on an INI file
 */

namespace AppBundle\Model;

class ProjectIni extends Project
{
    /**
     * @var String Location of the project.
     */
    protected $project_root;

    /**
     * @var String Location of the INI file, relative to the project root
     */
    protected $ini_path;

    /**
     * @var Array Parsed data
     */
    protected $data;

    public function __construct($project_root = null, $ini_path = null) 
    {
        parent::__construct($project_root);

        if (empty($ini_path)) {
            $ini_path = 'docker/config.ini';
        }

        $this->ini_path = $ini_path;

        $this->loadData();
    }

    public function loadData() 
    {
        $ini_file = $this->project_root . DIRECTORY_SEPARATOR . $this->ini_path;

        $this->data = parse_ini_file($ini_file, TRUE);
    }

    public function getProperty($section, $key)
    {
        if (empty($this->data)) {
            $this->loadData();
        }

        if ($section == '.')
        {
            if (isset($this->data[$key])) {
                return $this->data[$key];
            }
            else {
                return null;
            }
        }
        else
        {
            if (isset($this->data[$section][$key])) {
                return $this->data[$section][$key];
            }
            else {
                return null;
            }
        }
    }

    public function getRecursiveProperty($section, $key)
    {
        $value = $this->getProperty($section, $key);

        if ($value === null) {
            $parent = $this->getProperty($section, 'parent');

            if ($parent !== null) {
                $value = $this->getRecursiveProperty($parent, $key);
            }
        }

        return $value;
    }

    public function getRecursivePropertyFallback($section, array $keys)
    {
        foreach ($keys as $thisKey)
        {
            $value = $this->getRecursivePropert($section, $thisKey);

            // If we found a useful value, return it.
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    public function findBestSection(array $sections)
    {
        if (empty($this->data)) {
            $this->loadData();
        }
        
        foreach ($sections as $thisSection)
        {
            if (!empty($this->data[$thisSection]))
            {
                return $thisSection;
            }
        }

        return null;
    }
        
    public function findBestEnvironment()
    {
        $sections = array(
            'dev',
            'integration',
            'staging',
            'preproduction',
            'live',
            '.',
        );

        $env = $this->getCurrentEnv();

        $key = array_search($env, $sections);
        if ($key === false)
        {
            array_unshift($sections, $key);
        }
        else if ($key > 0)
        {
            array_splice($sections, 0, $key -1);
        }

        $bestSection = $this->findBestSection($sections);

        return $bestSection;
    }

    public function getCurrentEnv()
    {
        if (isset($_ENV['ENV'])) {
            $env = $_ENV['ENV'];
        }
        else
        {
            $env = 'live';
        }

        return $env;
    }

    public function getCurrentHost()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function __get($name)
    {
        $value = $this->getProperty('.', $name);

        if ($value === null)
        {
            $env = $this->findBestEnvironment();

            $value = $this->getRecursiveProperty($env, $name);
        }

        return $value;
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