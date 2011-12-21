<?php
/**
 * File testAutoloadContainerRule.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Fixtures/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     testAutoloadContainerRule.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'RuleParams.php';
require_once 
    $baseDir.$relDir.implode(DIRECTORY_SEPARATOR, array('Abstract', 'Rule.php'));
/**
 * Fixture for autoload Container
 * 
 * @category Automne
 * @package  Tests/Fixtures/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     testAutoloadContainer
 *
 */
class testAutoload_ContainerRule extends ATM_Autoload_Rule_Abstract
{
    
    /**
     * Initialise default parameters
     * Parameters are set in container
     * 
     * @param object $context the context object
     * @param array  $params  parameters of this object
     * 
     * @return object sanitized parameters
     */
    public static function initParams($context, $params)
    {
        return new ATM_Autoload_RuleParams($context, $params);
    }

    /**
     * Apply the filter to the entity name
     *  
     * @param string $entity the name to filter
     * 
     * @return array the elements of entity name (result of preg_match by default)
     */
    
    public function filter($entity)
    {
        @preg_match($this->params->getFilter(), $entity, $return);
        return $return;
    }
    /**
     * return the file name that contains the entity
     * 
     * @param string $entity an entity name (entity name, interface name...)
     * 
     * @return bool always false as this rule return only cached path by default
     */
    public function whereIs($entity)
    {
        return $entity;
    }
    /**
     * return the type of an entity (always default for this rule)
     * 
     * @param string $entity an entity name (entity name, interface name...)
     * 
     * @return string always the default type for this rule
     */
    public function whoIs($entity)
    {
        return $entity;
    }
}
