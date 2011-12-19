<?php
/**
 * File atmContextTemplateAbstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmContextTemplateAbstract.php
 *
 */
/**
 * A template class that retrieves datas replacements from
 * a container and a method passed to constructor.
 * Uses regexp with 2 named masks
 *
 * @category Abstracts
 * @package  Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmContextTemplateAbstract
 *
 */
abstract class atmContextTemplateAbstract
{
    protected $getter;
    protected $container;
    protected $globalPattern;
    protected $strictPattern;
    /**
     * Constructor
     *
     * @param object   $container the object from wich we get datas
     * @param function $getter    the method to call to retrieve datas
     */
    public function __construct($container, $getter='about')
    {
        $this->container =& $container;
        $this->getter    =& $getter;
    }
    /**
     * retrieve datas values depending on context properties defined in description
     *
     * @param mixed $datas the datas to normalize
     *
     * @return mixed normalized datas
     */
    public function apply($datas)
    {
        $type = ($datas instanceof ArrayAccess) ? 'Array' : ucfirst(gettype($datas));
        $method= 'apply'.$type;
        if ($datas !== $this->container && method_exists($this, $method)) {
            return $this->$method($datas);
        }
        return $datas;
    }
    /**
     * retrieve datas values for each element of the array 
     * 
     * @param array $datas array to normalize
     * 
     * @return array 
     */
    protected function applyArray($datas)
    {
        foreach ($datas as $key=>$value) {
            $datas[$key] = $this->apply($value);
        }
        return $datas;

    }
    /**
     * retrieve datas values for each element of the object
     *
     * @param object $datas the object on wich apply template
     *
     * @return object
     */
    protected function applyObject($datas)
    {
        $datas = clone $datas;
        foreach ($datas as $property=>$value) {
            $datas->$property = $this->apply($value);
        }
        return $datas;

    }

    /**
     * retrieve datas values if datas is a string
     *
     * @param mixed $datas the datas to normalize
     *
     * @return mixed normalized datas
     */
    protected function applyString($datas)
    {
        if (preg_match($this->strictPattern, $datas, $match)) {
            return $this->callGetter(
                $match['datas'],
                $this->markOff($match['datas'])
            );
        } else {
            return preg_replace_callback(
                $this->globalPattern, array($this, 'replace'), $datas
            );
        }
    }
    /**
     * Return the replacement of the given matches
     *
     * @param array $matches the result of the pattern matches
     *
     * @return string the replacement dpending on context description
     */
    protected function replace($matches)
    {
        return $this->callGetter(
            $matches['datas'], 
            $this->markOff($matches['datas'])
        );
    }
    /**
     * call the getter function with searched value and default value
     *
     * @param string $datas   the searched value
     * @param mixed  $default the default value if a property not exists
     *
     * @return mixed result of a function call
     */
    public function callGetter($datas, $default='')
    {
        $datas = call_user_func(
            array($this->container, $this->getter), $datas, $default
        );
        return $this->sanitize($datas);
    }
    /**
     * protect the string with delimiters
     *  
     * @param string $string the string to delimit
     * 
     * @return string
     */
    abstract public function markOff($string);
    /**
     * Sanitize datas after getting them from container
     * 
     * @param mixed $datas set of datas to clean
     * 
     * @example remove delimiters
     * @return mixed cleaned datas
     */
    abstract public function sanitize($datas);
}


