<?php
/**
 * File atmAutoloadDefaultRule.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadDefaultRule.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'atmAutoloadRuleParams.php';

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
 * @link     atmAutoloadDefaultRule
 *
 */
class atmAutoloadDefaultRule
{
    protected $context;
    protected $params;
    /**
     * Constructor
     * 
     * @param object $context a context
     * @param mixed  $params  parameters as array or atmAutoloadRuleParams
     */
    public function __construct($context, $params)
    {
        $this->context =& $context;
        $this->params  = $this->initParams(&$this->context, $params);
    }
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
        return new atmAutoloadRuleParams(&$context, $params);
    }
    /**
     * Initialise default parameters
     * Parameters are set in container
     * 
     * @return object sanitized parameters
     */
    public function &getParams()
    {
        return $this->params;
    }
    /**
     * Apply the filter to the entity name
     *  
     * @param string $entity the name to filter
     * 
     * @return array the elements of entity name (result of preg_match by default)
     */
    
    public function applyFilter($entity)
    {
        @preg_match($this->params->getFilter(), $entity, $return);
        return $return;
    }
    /**
     * Filter the entity name, store it to the cache and return it
     *  
     * @param string $entity the name to filter
     * 
     * @return array the elements of entity name (result of preg_match by default)
     */
    public function filter($entity)
    {
        $result=$this->applyFilter($entity);
        $this->params->setFilterCache($entity, $result);
        return $result;
    }
    /**
     * Test whether this object can load the right file 
     * 
     * @param string $entity the entity name
     * 
     * @return bool whether this object know the entity
     */
    public function know($entity)
    {
        return (
            $this->params->getCache($entity)!==false
            || (
                ($filterCache=$this->params->getFilterCache($entity, false))!==false
                && $filterCache != array()
            )
            || (
                $filterCache == false
                && $this->filter($entity) != array()
            )
        );
    }
    /**
     * Get the path from cache or create and cache it if not in cache
     *  
     * @param string $entity a entity name
     * 
     * @return string the file name where to load the entity or false if not found 
     */
    public function cacheWhereIs($entity)
    {
        $where = $this->params->getCache($entity);
        if ($where === false) {
            $where = $this->whereIs($entity);
            $this->params->setCache($entity, $where);
        }
        return $where;
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
        /// should be implemented in child
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
        /// should be implemented in child
        return $this->params->getDefaultType();
    }
    /**
     * This is the autoload default action, override to change
     * 
     * @param string $entity the name of the entity to load
     * 
     * @return bool whether the file can be included 
     */
    public function load($entity) 
    {
        if ($this->know($entity) && ($where=$this->cacheWhereIs($entity))!==false) {
            return (bool) @include_once $where;
        }
        return false;
    }
}