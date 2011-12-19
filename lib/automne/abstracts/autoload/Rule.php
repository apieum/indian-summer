<?php
/**
 * File atmAutoloadRuleAbstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload/Rules
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleAbstract.php
 *
 */


/**
 * Rule class with cache
 * helps to include files required by entities: (abstract) classes, interfaces...
 * They are set by a container which register their method 'load' in autoloader.
 * A rule can :
 * - filter an entity by this name, 
 * - reply if an entity is known 
 * - return where is the file to load
 * - finally include the needed file. 
 * Optionnaly it can return the type of an entity : library, view, stage... 
 * The autoload process start by asking if the entity is known, 
 * then where the entity file resides and then includes the file. 
 * A rule depends on parameters and context.
 * This rule has 3 parameters :
 * <ul>
 * <li>base directory : a root path from where the search is done</li>
 * <li>filter : a string that help to retrieve informations from the entity name</li>
 * <li>default type : the default generic type of entities, 
 * if it can't be resolved by the whoIs method.
 * </li>
 * </ul>
 * As paths and managed entities can depend of a context, 
 * this rule caches filtered entities names and paths with the container context.
 * There is 2 caches managed by parameters object.
 * 
 * Override methods filter, initParams, whereIs and WhoIs to set a rule.
 * 
 * @category Abstracts
 * @package  Autoload/Rules
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleAbstract
 *
 */
abstract class atmAutoloadRuleAbstract
{
    const USE_CACHE = true;
    protected $context;
    protected $params;
    
    /**
     * Constructor
     * 
     * @param object       $context a context
     * @param array|object $params  parameters as array or atmAutoloadRuleParams
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
    abstract public static function initParams($context, $params);
    /**
     * Returns parameters of this object
     * 
     * @return object sanitized parameters
     */
    public function &getParams()
    {
        return $this->params;
    }
    /**
     * Return arguments glued with directory separator
     * 
     * @return string
     */
    public static function implodePath()
    {
        $args = func_get_args();
        return implode(DIRECTORY_SEPARATOR, $args);
    }
    /**
     * Test whether this object can load the right file from cache
     * 
     * @param string $entity the entity name
     * 
     * @return bool whether this object know the entity
     */
    public function cacheKnow($entity)
    {
        if ($this->params->getCache($entity)!==false) {
            return true;
        }
        $filterCache = $this->params->getFilterCache($entity, false);
        if ($filterCache !== false) {
            return $filterCache !== array();
        } else {
            return $this->cacheFilter($entity) !== array();
        }
    }
    /**
     * filter an entity name and put it to cache
     * 
     * @param string $entity the entity name
     * 
     * @return array the result of filter
     */
    public function cacheFilter($entity)
    {
        $result = $this->filter($entity);
        $this->params->setFilterCache($entity, $result);
        return $result;
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
     * Test whether this object can load the right file 
     * 
     * @param string $entity the entity name
     * 
     * @return bool whether this object know the entity
     */
    public function know($entity)
    {
        return $this->filter($entity) != array();
    }
    /**
     * filter an entity name
     * 
     * @param string $entity entity name
     * 
     * @return array result of filter, if empty entity is not known
     * 
     */
    abstract public function filter($entity);
    /**
     * return the file name that contains the entity
     * 
     * @param string $entity an entity name (entity name, interface name...)
     * 
     * @return bool always false as this rule return only cached path by default
     */
    abstract public function whereIs($entity);
    /**
     * return the type of an entity (always default for this rule)
     * 
     * @param string $entity an entity name (entity name, interface name...)
     * 
     * @return string always the default type for this rule
     */
    abstract public function whoIs($entity);
    /**
     * This is the autoload default action, override to change
     * 
     * @param string $entity the name of the entity to load
     * 
     * @return bool whether the file can be included 
     */
    public function load($entity) 
    {
        if (self::USE_CACHE && $this->cacheKnow($entity)) {
            $where = $this->cacheWhereIs($entity);
            return (bool) @include_once $where;
        } elseif ($this->know($entity)) {
            $where = $this->whereIs($entity);
            return (bool) @include_once $where;
        }
        return false;
    }
}