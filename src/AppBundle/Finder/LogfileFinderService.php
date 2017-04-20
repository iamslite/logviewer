<?php
/**
 * @file
 * Class for finding Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;

class LogfileFinderService
{
    /**
     * @var Array finder types
     */
    protected $types;

    /**
     * @var the Service Container
     */
    protected $container;

    public function __construct(array $types, $container)
    {
        $this->container = $container;
        $this->types = $types;
    }

    public function find($type = null, $mask = '*', $recursive = true)
    {
        if (!empty($type)) {
            $types = array($type => $this->getType($type));
        }

        if (empty($types)) {
            $types = $this->getTypes();
        }

        $logs = array();

        foreach ($types as $typeName => $thisType) {
            if (!empty($thisType)) {
                $logs[$typeName] = $thisType->find($mask, $recursive);
            }
            else {
                $logs[$typeName] = array();
            }
        }

        // Flatten the array if there was a single type & there was data
        if (!empty($type)) {
            if (!empty($logs[$type])) {
                return $logs[$type];
            }
            else {
                return array();
            }
        }
        // else
        
        return $logs;
    }

    public function getTypes() {
        $types = array();

        foreach ($this->types as $type) {
            $theType = $this->container->get($type);
            $types[$theType->getName()] = $theType;
        }

        return $types;
    }

    public function getType($type) {
        $types = $this->getTypes();

        if (!empty($types[$type])) {
            return $types[$type];
        }
        // else
        return false;
    }

}