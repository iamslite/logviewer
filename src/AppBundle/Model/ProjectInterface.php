<?php
/**
 * @file
 * The interface for the project model.
 */

namespace AppBundle\Model;

interface ProjectInterface {
    public function getCurrentEnv();

    public function getCurrentHost();

    public function getProjectRoot();

    public function getValue($name);

    public static canConfigure($project_root);
}
