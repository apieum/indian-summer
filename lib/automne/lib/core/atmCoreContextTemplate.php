<?php

/**
 * File atmCoreContextTemplate.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Core
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmCoreContextTemplate.php
 *
 */
$contextTemplate= implode(
    DIRECTORY_SEPARATOR,
    array(
    __DIR__, '..', '..', 'abstracts', 'context', 'atmContextTemplateAbstract.php'
    )
);
require_once $contextTemplate;
/**
 * A template class that retrieves datas from
 * a container and a method passed to constructor.
 *
 * @category Automne
 * @package  Core
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmCoreContextTemplate
 *
 */
class atmCoreContextTemplate extends atmContextTemplateAbstract
{
    protected $globalPattern = '@(?<!\\\)\{(?<datas>([^{}]+ | (?R))*)(?<!\\\)\}@xm';
    protected $strictPattern = '@^((?<!\\\)\{)(?<datas>[^{}]+)((?<!\\\)\})$@xm';
    /**
     * protect the string with delimiters
     *  
     * @param string $string the string to delimit
     * 
     * @return string
     */
    public function markOff($string)
    {
        return '\{'.$string.'\}';
    }
    /**
     * Sanitize datas after getting them from container
     * 
     * @param mixed $datas set of datas to clean
     * 
     * @example remove delimiters
     * @return mixed cleaned datas
     */
    public function sanitize($datas)
    {
        if (is_string($datas)) {
            return str_replace(array('\{','\}'), array('{','}'), $datas);
        } else {
            return $datas;
        }
    }
}


