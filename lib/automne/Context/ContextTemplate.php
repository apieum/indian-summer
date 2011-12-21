<?php

/**
 * File ATM_Context_Template.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Context/Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Context_Template.php
 *
 */
require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Abstract', 'Template.php'));
/**
 * A template class that retrieves datas from
 * a container and a method passed to constructor.
 *
 * @category Automne
 * @package  Context/Template
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Context_Template
 *
 */
class ATM_Context_Template extends ATM_Context_Template_Abstract
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


