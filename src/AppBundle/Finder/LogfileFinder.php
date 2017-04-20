<?php
/**
 * @file
 * Class for finding Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;

use AppBundle\Model;

abstract class LogfileFinder
{
    /**
     * @var String the master mask for this log file type
     */
    protected $mask;

    /**
     * @var Array possible base paths for this type
     */
    protected $basePaths;

    /**
     * @var String the class name for this log type
     */
    protected $logClass;

    /**
     * @var \AppBundle\Model\Project the project definition
     */
    protected $project;

    public function __construct($mask, $basePaths, $logClass, \AppBundle\Model\Project $project)
    {
        $this->mask = $mask;
        $this->logClass = $logClass;
        $this->project = $project;

        foreach ($basePaths as $index => &$basePath) 
        {
            $this->replaceTokens($basePath);

            if (!is_dir($basePath)) {
                unset($basePaths[$index]);
            }
        }

        $this->basePaths = $basePaths;
    }

    /**
     * The name by which this finder is known.
     *
     * @return string
     */
    abstract function getName();
    
    public function find($mask = '*', $recursive = true)
    {
        $logs = array();

        foreach ($this->basePaths as $basePath) {
            $finder = new Finder();
            
            if (!$recursive) {
                if (is_numeric($recursive)) {
                    $finder->depth('< ' . intval($recursive));
                }
                else {
                    $finder->depth('== 0');
                }
            }
            
            $finder->ignoreUnreadableDirs()
                ->files()
                ->name($this->mask)
                ->in($basePath);
            
            foreach ($finder as $file) {
                $logfile = Model\LogfileFactory::getFromFilename($this->logClass, $file->getPathname(), $basePath, $mask);

                if (!empty($logfile)) {
                    $logs[] = $logfile;
                }
            }
        }

        asort($logs);

        return $logs;
    }
    
    protected function replaceTokens(&$str)
    {
        $split_str = preg_split('#(\${[^}]+}|\$[A-Za-z][[:alnum:]]*)#', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        $tokens = $this->getTokens();

        foreach ($split_str as &$entry) {
            if (substr($entry, 0, 1) == '$') {
                if (substr($entry, 1, 1) == '{') {
                    $key = substr($entry, 2, -1);
                }
                else {
                    $key = substr($entry, 1);
                }

                if (!empty($tokens[$key])) {
                    $entry = $tokens[$key];
                }
            }
        }

        $str = implode($split_str);
    }

    protected function getTokens()
    {
        $tokens = array(
            'PROJECTROOT' => $this->project->getProjectRoot(),
        );

        return $tokens;
    }
}