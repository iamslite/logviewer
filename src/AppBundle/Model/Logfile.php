<?php
/**
 * @file
 * Model class for a generic log file
 */

namespace AppBundle\Model;

class Logfile
{
    /**
     * @var String The filename for the report.
     */
    protected $filename;

    /**
     * @var String The base path for the report/log
     */
    protected $basePath;

    /**
     * @var String the full path
     */
    protected $fullPath;

    /**
     * @var boolean|String Whether/how the file is compressed.
     */
    protected $compressed;

    /**
     * @var array The log
     */
    protected $log;

    public function __construct($filename = null, $basePath = null)
    {
        $this->filename = $filename;
        $this->setBasePath($basePath);
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        if (substr($basePath, -1) == DIRECTORY_SEPARATOR) {
            $basePath = substr($basePath, 0, -1);
        }

        $this->basePath = $basePath;

        // Invalidate the path.
        $this->getFullPath(true);

        return $this;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;

        // Invalidate the path.
        $this->getFullPath(true);

        return $this;
    }        

    public function getFullPath($reset = false)
    {
        if (!$reset && !empty($this->fullPath)) {
            //    return $this->fullPath;
        }

        $path = $this->basePath . DIRECTORY_SEPARATOR . $this->filename;

        $extensions = self::getExtensions();

        $bases = array($path);
        foreach ($extensions['base'] as $extension) {
            $bases[] = $path . '.' . $extension;
        }

        foreach ($bases as $base) {
            if (file_exists($base)) {
                $this->fullPath = $base;

                $this->compressed = false;

                return $base;
            }

            foreach ($extensions['compression'] as $extension => $type) {
                $this_path = $base . '.' . $extension;

                if (file_exists($this_path)) {
                    $this->fullPath = $this_path;

                    $this->compressed = $type;

                    return $this_path;
                }
            }
        }

        return false;
    }

    public static function getExtensions()
    {
        return array(
            'base' => array(
                'log',
            ),
            'compression' => array(
                'gz' => 'GZIP',
                'bz2' => 'BZIP2',
            )
        );
    }

    public function exists()
    {
        // If we don't have a filename then it can't exist.
        if (empty($this->filename)) {
            return false;
        }

        // Since we only have a valid path when the file exists this is a good test.
        $filename = $this->getFullPath();

        if (!empty($filename)) {
            return true;
        }
        // else
        return false;
    }

    public function isCompressed()
    {
        if (!isset($this->compressed) || $this->compressed === null) {
            $this->getFullPath();
        }

        return $this->compressed;
    }
    
    protected function loadContents()
    {
        $filename = $this->getFullPath();

        $contents = '';

        try {
            if (!empty($filename)) {
                switch ($this->compressed) {
                case 'GZIP':
                    if (function_exists('gzopen')) {
                        $handle = gzopen($filename, 'r');
                        
                        if ($handle) {
                            while (!gzeof($handle)) {
                                $contents .= gzread($handle, 131072); // 128K
                            }
                            
                            gzclose($handle);
                        }
                    }
                    else {
                        $content = 'The compressed file cannot be decompressed.';
                    }

                    break;
                    
                case 'BZIP2':
                    if (function_exists('bzopen')) {
                        $handle = bzopen($filename, 'r');
                        
                        if ($handle) {
                            while (!feof($handle)) {
                                $contents .= bzread($handle, 131072); // 128K
                            }
                            
                            bzclose($handle);
                        }
                    }
                    else {
                        $content = 'The compressed file cannot be decompressed.';
                    }
                    
                    break;
                    
                default:
                    $contents = file_get_contents($filename);
                }
            }
        }
        catch (Exception $e) {
            $contents = 'There was a problem accessing the file.';
        }

        if (!empty($contents)) {
            return $contents;
        }
        // else
        return false;
    }

    protected function splitContents($contents)
    {
        $split_contents = explode(PHP_EOL, $contents);

        return $split_contents;
    }

    protected function loadLog()
    {
        $contents = $this->loadContents();

        if ($contents === false)
        {
            return $contents;
        }

        $this->log = $this->splitContents($contents);
    }

    public function getContents($startLine = 0, $lineCount = null)
    {
        if (empty($this->log)) {
            $this->loadLog();
        }

        if (empty($startLine) && empty($lineCount)) {
            // Shortcut
            return $this->log;
        }

        $numLines = $this->getNumLines();
        
        // We use 1-indexing, so 0 and 1 mean the same ;-)
        if ($startLine > $numLines) {
            $startLine = $numLines;
        } 
        else if ($startLine == 0) {
            $startLine = 1;
        }
        
        // Ensure that an empty line count is actually a null
        if (empty($lineCount)) {
            $lineCount = null;
        }
        
        $contents = array_slice($this->log, $startLine -1, $lineCount);

        return $contents;
    }

    public function getNumLines()
    {
        if (empty($this->log)) {
            $this->loadLog();
        }

        return count($this->log);
    }
}