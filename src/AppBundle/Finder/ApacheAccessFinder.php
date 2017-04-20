<?php
/**
 * @file
 * Class for finding Apache Access Log files
 */

namespace AppBundle\Finder;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

use AppBundle\Model;

class ApacheAccessFinder extends LogfileFinder
{
    /**
     * @var string Is this httpd or apache2?
     */
    protected $apache_type;

    /**
     * @var array The log files associated with this project/vhost
     */
    protected $logs;

    /**
     * @var string Type of logs we are interested in.
     */
    protected $type;

    public function __construct($mask = '*', $class = '\\AppBundle\\Model\\ApacheAccessLog', $basePaths = array(), \AppBundle\Model\Project $project)
    {
        // We need access to the project early
        $this->project = $project;

        if (empty($mask)) {
            $mask = '*';
        }

        if (empty($basePaths)) {
            $basePaths = $this->getBasePaths();
        }

        parent::__construct($mask, $basePaths, $class, $project);

        // Access logs are "CustomLogs" in the config
        $this->type = 'Custom';
    }

    public function getName()
    {
        return 'Access';
    }

    protected function getBasePaths()
    {

        if (empty($this->logs)) {
            $this->getLogs();
        }

        if (!empty($this->logs)) {
            foreach ($this->logs as $type => $logs) {
                foreach ($logs as $log) {
                    $basePaths[] = dirname($log);
                }
            }
        }

        if (empty($basePaths))
        {
            $tokens = $this->getTokens();
            $basePaths = array($tokens['APACHE_LOG_DIR']);
        }

        $basePaths = array_unique($basePaths);

        return $basePaths;
    }

    protected function getLogs()
    {
        $output = array();
        try {
            exec('/usr/bin/env apachectl -V', $output);
        }
        catch (Exception $e) 
        {
            // Do nothing
        }

        $conf_dirs = false;

        foreach ($output as $line)
        {
            if (preg_match('# -D HTTPD_ROOT="([^"]+)"#', $line, $matches) == 1)
            {
                $conf_dirs = array($matches[1]);
            }
        }

        if (empty($conf_dirs)) {
            $conf_dirs = array ('/etc/httpd', '/etc/apache2');

            foreach ($conf_dirs as $index => $this_dir)
            {
                if (!is_dir($this_dir))
                {
                    unset($conf_dirs[$index]);
                }
            }
        }

        if (preg_match('#/etc/([^/]+)#', reset($conf_dirs), $matches) == 1)
        {
            $this->apache_type = $matches[1];
        }
        else {
            $this->apache_type = 'httpd';
        }

        $logs = array();

        $hostname = $this->project->getCurrentHost();

        $logre = '#\b(\S*?)Log\s+"?(\S+)#';

        foreach ($conf_dirs as $conf_dir) 
        {
            $finder = new Finder();
            $finder->ignoreUnreadableDirs()
                ->files()
                ->in($conf_dir);

            foreach ($finder as $file)
            {
                $filename = $file->getRealPath();
                if ($filename)
                {
                    $conf = file_get_contents($filename);

                    $matches = array();

                    $vhostre = '#<VirtualHost.*>(.*?Server(?:Name|Alias) [^\n]*\b' . $hostname . '\b.*?)</VirtualHost>#s';

                    $matches = array();
                    preg_match_all($vhostre, $conf, $matches, PREG_SET_ORDER);

                    if (empty($matches)) {
                        continue;
                    }

                    foreach ($matches as $match)
                    {
                        if (!isset($match[1])) {
                            continue;
                        }

                        $vhostconf = $match[1];
                        
                        $logmatches = array();
                        preg_match_all($logre, $vhostconf, $logmatches, PREG_SET_ORDER);

                        if (empty($logmatches)) {
                            continue;
                        }

                        foreach ($logmatches as $logmatch)
                        {
                            if (!empty($logmatch[1]) && !empty($logmatch[2]))
                            {
                                $logs[$logmatch[1]][] = $logmatch[2];
                            }
                        }
                    }
                }
            }
        }

        foreach ($logs as $type => &$log_entries)
        {
            foreach ($log_entries as &$entry)
            {
                $this->replaceTokens($entry);
            }

            $log_entries = array_unique($log_entries);
        }
        
        $this->logs = $logs;
    }

    protected function getTokens()
    {
        $tokens = parent::getTokens();

        if (empty($this->apache_type))
        {
            $this->getLogs();
        }

        $tokens['APACHE_LOG_DIR'] = '/var/log/' . $this->apache_type;

        return $tokens;
    }

    public function find($mask = '*', $recursive = true)
    {
        if (empty($this->logs)) {
            $this->getLogs();
        }

        $logs = array();

        $mask_re = Glob::toRegex($mask);

        foreach ($this->logs as $type => $log_entries) {
            if (isset($this->type) && $type != $this->type) {
                continue;
            }

            foreach ($log_entries as $log) {
                foreach ($this->basePaths as $basePath)
                {
                    if (substr($log, 0, strlen($basePath) + 1) == $basePath . DIRECTORY_SEPARATOR) {
                        $filename = substr($log, strlen($basePath) + 1);
                        
                        if (preg_match($mask_re, $filename) == 1) {
                            $logfile = Model\LogfileFactory::getFromFilename($this->logClass, $filename, $basePath, $mask);
                            
                            if (!empty($logfile)) {
                                $logs[] = $logfile;
                            }
                        }
                    }
                }
            }
        }

        return $logs;
    }
}