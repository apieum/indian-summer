<?php
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'RuleParams.php';
require_once $baseDir.implode(
    DIRECTORY_SEPARATOR, 
    array('lib', 'automne', 'abstracts', 'autoload', 'Rule.php')
);

class testAutoloadContainerRule extends atmAutoloadRuleAbstract
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
        return new atmAutoloadRuleParams($context, $params);
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
        return false;
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
        return false;
    }
}
