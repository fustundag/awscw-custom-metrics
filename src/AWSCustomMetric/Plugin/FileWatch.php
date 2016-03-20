<?php

namespace AWSCustomMetric\Plugin;

use AWSCustomMetric\DI;
use AWSCustomMetric\Metric;

class FileWatch extends BaseMetricPlugin implements MetricPluginInterface
{
    private $fileToWatch;
    private $checkPointFile;
    private $patterns;

    public function __construct(DI $diObj, $namespace = null, $cronExpression = '')
    {
        parent::__construct($diObj, $namespace, $cronExpression);
        $this->patterns = [];
    }

    public function getFileToWatch()
    {
        return $this->fileToWatch;
    }

    public function setFileToWatch($fileToWatch, $rotatePattern = null)
    {
        if ($rotatePattern) {
            $fileToWatch = str_replace($rotatePattern, date($rotatePattern), $fileToWatch);
        }
        $this->fileToWatch = $fileToWatch;
    }

    /**
     * @return mixed
     */
    public function getCheckPointFile()
    {
        return $this->checkPointFile;
    }

    /**
     * @param mixed $checkPointFile
     */
    public function setCheckPointFile($checkPointFile)
    {
        $this->checkPointFile = $checkPointFile;
    }

    /**
     * @return array
     */
    public function getPatterns()
    {
        return $this->patterns;
    }

    /**
     * @param string $pattern
     * @param bool $isRegExp
     */
    public function addPattern($pattern, $isRegExp = false)
    {
        $this->patterns[ $pattern ] = $isRegExp;
    }

    /**
     * @param string $pattern
     */
    public function removePattern($pattern)
    {
        unset($this->patterns[ $pattern ]);
    }

    /**
     * @param $line
     * @return int
     */
    public function checkLine($line)
    {
        $matchedCount = 0;
        foreach ($this->patterns as $pattern => $isRegExp) {
            if ($isRegExp) {
                if (preg_match($pattern, $line)) {
                    $matchedCount++;
                }
            } else {
                if (strpos($line, $pattern)!==false) {
                    $matchedCount++;
                }
            }
        }
        if ($matchedCount>0 && $this->diObj->getLogger()) {
            $this->diObj->getLogger()->error(
                'FileWatch found matched line!'
                .' LINE >>> ' . $line . ' <<<'
            );
        }
        return $matchedCount;
    }

    /**
     * @return Metric[]|null|bool
     */
    public function getMetrics()
    {
        try {
            if (is_file($this->fileToWatch)===false) {
                throw new \Exception("File to watch \"". $this->fileToWatch ."\" not found!");
            }

            clearstatcache(true);
            $checkPointFile = $this->checkPointFile?:$this->fileToWatch . '.checkpoint';
            $checkPoint     = is_file($checkPointFile)?intval(file_get_contents($checkPointFile)):0;
            $fileSize       = filesize($this->fileToWatch);
            if ($fileSize==0) {
                return [
                    $this->createNewMetric('FileWatchError', 'Count', 0)
                ];
            }
            if ($checkPoint>$fileSize) {
                $checkPoint = 0;
            }
            $fp = fopen($this->fileToWatch, 'r');
            if (!$fp) {
                throw new \Exception("File to watch \"". $this->fileToWatch ."\" could not opened for read!");
            }
            $seekStatus = fseek($fp, $checkPoint);
            if ($seekStatus==-1) {
                throw new \Exception("Seek to \"". $checkPoint ."\" failed for \"". $this->fileToWatch ."\"!");
            }

            $newCheckPoint      = $checkPoint;
            $foundPatternsCount = 0;
            while (($line = fgets($fp)) !== false) {
                if (($newCheckPoint+strlen($line))>$fileSize) {
                    break;
                }
                $foundPatternsCount += $this->checkLine($line);
                $newCheckPoint      += strlen($line);
            }
            fclose($fp);
            file_put_contents($checkPointFile, $newCheckPoint);

            return [
                $this->createNewMetric('FileWatchError', 'Count', $foundPatternsCount)
            ];
        } catch (\Exception $e) {
            if ($this->diObj->getLogger()) {
                $this->diObj->getLogger()->error('FileWatch thrown exception! ExcpMsg: ' . $e->getMessage());
            }
            return [
                $this->createNewMetric('FileWatchException', 'Count', 1)
            ];
        }
    }
}
