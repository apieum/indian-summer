<?php
/**
 * File MinusAndContext.rule.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload/Rules
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     MinusAndContext.rule.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'RuleParams.php';
$abstractRule= implode(
    DIRECTORY_SEPARATOR,
    array(__DIR__, '..', '..', 'abstracts', 'autoload', 'Rule.php')
);
require_once $abstractRule;

/**
 * This rule loads entities from a defined type in an environnement if file exists
 * or in type directory if not. 
 * Files names are made by the entity name without the type, 
 * prepended by minus and whatever can be usefull to organise the directory.
 * (first intended for states and stages)
 * 
 * @example
 * for the class LoadConstantState :
 * if context environment is 'env' will first search in directory states/env/
 * if file not exists the search continue in directory states/
 * the file name pattern is *-CompoundNameType.php
 * so will find 10-LoadConstant.php or 10.1-LoadConstant.php... 
 * 
 * @category Automne
 * @package  Autoload/Rules
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadMinusAndContext
 *
 */
class atmAutoloadMinusAndContext extends atmAutoloadRuleAbstract
{
    public static $default = 'lib';
    public static $filter  = "@^([A-Z][a-z_]+(?>[A-Z][a-z_]+)*)([A-Z][a-z_]+)$@";
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
        $params = new atmAutoloadRuleParams(&$context, $params);
        if (is_null($params->getFilter())) {
            $params->setFilter(self::$filter);
        }
        if (is_null($params->getDefaultType())) {
            $params->setDefaultType(self::$default);
        }
        return $params;
    }
    /**
     * return the filtered entity name
     * 
     * @param string $entity entity name
     * 
     * @return array the filtered name
     */
    public function filter($entity)
    {
        $filter = $this->params->getFilter();
        if (preg_match($filter, $entity, $matches) == false) {
            return array();
        }
        return array('name'=>$matches[1], 'type'=>strtolower($matches[2]).'s');
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
        $searchEnv = self::implodePath(
            $this->params->getBaseDir(),
            $this->whoIs($entity),
            $this->context->where(),
            '*-'.$this->getName($entity).'.php'
        );
        $result=glob($searchEnv);
        if (count($result)>0) {
            return realpath($result[0]);
        } else {
            $search = self::implodePath(
                $this->params->getBaseDir(),
                $this->whoIs($entity),
                '*-'.$this->getName($entity).'.php'
            );
            $result=glob($search);
            return count($result)>0 ? realpath($result[0]) : false;
        }
    }
    /**
     * return the type of an entity
     * 
     * @param string $entity an entity name (class name, interface name...)
     * 
     * @return string|null
     */
    public function whoIs($entity)
    {
        $fCache = $this->params->getFilterCache($entity, array('type'=>null));
        return $fCache['type'];
    }
    /**
     * return the name of an entity 
     * 
     * @param string $entity the entity
     * 
     * @return string|null
     */
    public function getName($entity)
    {        
        $fCache = $this->params->getFilterCache($entity, array('name'=>null));
        return $fCache['name'];
    }
}

