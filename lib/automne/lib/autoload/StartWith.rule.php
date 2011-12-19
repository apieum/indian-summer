<?php
/**
 * File atmAutoloadStartWith.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadStartWith.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'RuleParams.php';
$abstractRule= implode(
    DIRECTORY_SEPARATOR,
    array(__DIR__, '..', '..', 'abstracts', 'autoload', 'Rule.php')
);
require_once $abstractRule;

/**
 * Default rule class
 * helps to include files required by entities: (abstract) classes, interfaces...
 * They are set by a container which register their method 'autoload' in autoloader.
 * A rule can answer if it know an entity and where is it's file.
 * Optionnaly it can return the generic type of an entity : library, view, stage... 
 * The autoload process start by asking if the entity is known, 
 * then where the entity file reside and then include the file. 
 * A rule depends on parameters and context.
 * This rule has 3 parameters :
 * <ul>
 * <li>base directory : a root path from where the search is done</li>
 * <li>filter : a string that help to retrieve informations from the entity name</li>
 * <li>default type : the default generic type of entities. 
 * Rules might have a 'whoIs' method, which return the type of an entity, 
 * if not found by whoIs default type is returned.
 * </li>
 * </ul>
 * As paths and managed entities can depend of a context, 
 * this rule caches filtered entities names and paths with the container context.
 * There is 2 caches managed by parameters object.
 * 
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadStartWith
 *
 */
class atmAutoloadStartWith extends atmAutoloadRuleAbstract
{
    protected static $default = 'lib';
    protected static $filter  = 'atm';
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
     * Return filter for start string
     * 
     * @param string $start the beginning of entities names
     * 
     * @return string
     */
    public static function getStartFilter($start)
    {
        return "@^${start}([A-Z][a-z_]+)?(([A-Z][a-z_]+)+)?([A-Z][a-z_]+)$@";
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
        $filter = $this->getStartFilter($this->params->getFilter());
        if (preg_match($filter, $entity, $matches) == false) {
            return array();
        }
        if ($matches[1]=='') {
            // grep one word
            $return['type']=$this->params->getDefaultType();
            $return['name']=$matches[4];
            $return['pack']=strtolower($matches[4]);
        } elseif ($matches[2]=='') {
            // grep 2 words
            $type = strtolower($matches[4]).'s';
            if (is_dir(self::implodePath($this->params->getBaseDir(), $type))) {
                $return['type']=$type;
                $return['name']=$matches[1];
            } else {
                $return['type']=$this->params->getDefaultType();
                $return['name']=$matches[4];
            }
            $return['pack']=strtolower($matches[1]);
        } else {
            // grep 3 or more words
            $type = strtolower($matches[4]).'s';
            if (is_dir(self::implodePath($this->params->getBaseDir(), $type))) {
                $return['type']=$type;
                $return['name']=$matches[2];
            } else {
                $return['type']=$this->params->getDefaultType();
                $return['name']=$matches[2].$matches[4];
            }
            $return['pack']=strtolower($matches[1]);
        }
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
        $path = self::implodePath(
            $this->params->getBaseDir(),
            $this->whoIs($entity),
            $this->getPackage($entity),
            $this->getName($entity).'.php'
        );
        return (string) realpath($path);
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
     * return the package of an entity
     *  
     * @param string $entity name
     * 
     * @return string|null
     */
    public function getPackage($entity)
    {
        $fCache = $this->params->getFilterCache($entity, array('pack'=>null));
        return $fCache['pack'];
        
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