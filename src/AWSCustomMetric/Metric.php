<?php

namespace AWSCustomMetric;

class Metric
{
    private $namespace;
    private $name;
    private $unit;
    private $value;

    public static $availableUnits = [
        'Seconds',
        'Microseconds',
        'Milliseconds',
        'Bytes',
        'Kilobytes',
        'Megabytes',
        'Gigabytes',
        'Terabytes',
        'Bits',
        'Kilobits',
        'Megabits',
        'Gigabits',
        'Terabits',
        'Percent',
        'Count',
        'Bytes/Second',
        'Kilobytes/Second',
        'Megabytes/Second',
        'Gigabytes/Second',
        'Terabytes/Second',
        'Bits/Second',
        'Kilobits/Second',
        'Megabits/Second',
        'Gigabits/Second',
        'Terabits/Second',
        'Count/Second',
        'None'
    ];

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     * @return bool
     */
    public function setUnit($unit)
    {
        if (in_array($unit, self::$availableUnits)) {
            $this->unit = $unit;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

}
