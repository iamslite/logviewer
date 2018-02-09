<?php
/**
 * @file
 * Project definitions in SiteConfig format.
 */

namespace AppBundle\Model;

use Symfony\Component\Yaml\Yaml;

class ProjectSiteconfig extends Project
{
    /**
     * @var string[]
     *  - Array of config file names.
     */
    protected $config_files;

    /**
     * @var array
     *  - The parsed configuration data.
     */
    protected $data;

    public function __construct($project_root = null)
    {
        parent::__construct($project_root);

        $this->config_files = array(
            $this->getSystemConfigDir().'systemconfig.yml',
            $this->getSystemConfigDir().'systemconfig.local.yml',
            
            $this->getProjectRoot().'.siteconfig.yml',
        );

        $this->data = array();

        $this->loadData();
    }

    protected function getSystemConfigDir() {
        return '/opt/pr_scripts/etc/';
    }

    public function loadData()
    {
        $data = array();
        
        foreach ($this->config_files as $config_file) {
            $config_data = Yaml::parse(file_get_contents($config_file));

            if (!empty($config_data)) {
                $this->merge_arrays($data, $config_data);
            }
        }
    }

    public function __get($name)
    {
        // currentHost
        // apptype
        // domainname

        $fragments = explode('.', $name);

        return $this->extract_data_value($keys, $this->data);
    }

    protected function extract_data_value($keys, $data) {
        $next_keys = $keys + array();
        $this_key = array_shift($next_keys);

        if (empty($data)) {
            return;
        }
        
        if (isset($data[$this_key])) {
            if (empty($next_keys)) {
                if (is_array($data[$this_key])) {
                    $value = $data[$this_key] + array();

                    array_walk_recursive($value, function(&$val, $key) {
                        $val = $this->getFinalValue($val);
                    });
                }
                else {
                    $value = $this->getFinalValue($data[$this_key]);
                }
                
                return $value;
            }
            else {
                $value = $this->extract_data_value($next_keys, $data[$this_key]);

                if ($value === NULL && isset($data['import'])) {
                    $imports = is_array($data['import']) ? $data['import'] : array($data['import']);
                        
                    foreach ($imports as $import) {
                        $import = $this->getFinalValue($import);
                            
                        $value = $this->__get($import);

                        if (!empty($value)) {
                            break;
                        }
                    }
                }

                return $value;
            }
        }
        // else
        return;
    }

    protected function getFinalValue($value) {
        $matches = array();
        
        while (preg_match('/\{\{(.*)\}\}/', $value, $matches)) {
            $placeholder = $matches[0];
            $token = $matches[1];
            $replacement = '';

            if (substr($token, 0, 4) == 'ENV:') {
                $token = substr($token, 4);

                $replacement = getenv($token);
                if (empty($replacement) && isset($_SERVER[$token])) {
                    $replacement = $_SERVER[$token];
                }
            }
            else {
                $replacement = $this->__get($token);
            }

            $value = str_replace($placeholder, $replacement);
        }

        return $value;
    }
            
    public function getCurrentHost()
    {
        return $_SERVER['URL'];
    }

    public function getApptype()
    {
        
    }

    public function getDomainname()
    {
    }

    /**
     * Merge the contents of two arrays in a defined way.
     * 
     * Where a key contains a keyed array then the subkeys are merged. If
     * the subkeys are not arrays then the source replaces the target value.
     * If the subkeys are numerically indexed arrays then the source replaces
     * the target value. If the subkey is string indexed then they are merged
     * recursively.
     */ 
    protected function merge_arrays(&$target, $source)
    {
        if (is_array($source)) {
            # Source keys are numeric if the first key is numeric
            reset($source);
            $first_key = key($source);
            
            $is_numeric = is_numeric($first_key) && intval($first_key) == $first_key;

            if ($is_numeric) {
                $target = $source;
            }
            else {
                foreach ($source as $key => $value) {
                    if (!isset($target[$key])) {
                        $target[$key] = $value;
                    }
                    elseif (is_array($value)) {
                        merge_arrays($targets[$key], $value);
                    }
                    else {
                        $target[$key] = $value;
                    }
                }
            }
        }
        else {
            $target = $source;
        }
    }

    public function canConfigure($projectRoot) {
        $config_path = $projectRoot . '/.siteconfig.yml';

        if (file_exists($config_path)) {
           return true;
        }
        // else
        return false;
    }
}
