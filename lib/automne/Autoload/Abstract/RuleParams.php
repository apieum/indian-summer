<?php
/**
 * File ATM_Autoload_RuleParams_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload/Rules/Params
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleParams_Abstract.php
 *
 */
/**
 * Manage Rule parameters within context
 * Parameters stores datas associated with a rule.
 * This class provides 2 caches for tests if entities are loadable and paths results.
 * 
 *   Caches depends on context subject, environment and moment 
 *   and are cleared when setting a new value to properties.
 *   
 *   when it's converted to string it returns a hash string from initials parameters
 * 
 * @category Abstracts
 * @package  Autoload/Rules/Params
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleParams_Abstract
 *
 */
abstract class ATM_Autoload_RuleParams_Abstract
{
    protected $context;
    protected $hash;
    protected $filterCache= array();
    protected $pathsCache = array();
    /**
     * Constructor
     * 
     * @param object $context a context object
     * @param mixed  $params  parameters of the rule
     */
    public function __construct($context, $params=array())
    {
        $this->context=& $context;
        $this->merge($params);
        $this->hash = md5(serialize($params));
    }
    /**
     * merge parameters with the given argument
     * 
     * @param mixed $params object or array to merge
     * 
     * @return object $this for chaining
     */
    abstract public function merge($params);
    
    /**
     * return a hash of the object at this creation 
     *
     * @return string an hash as identifier
     */
    public function __toString()
    {
        return $this->hash;
    }
    /**
     * This add a filter result to cache or determine it if not given
     * 
     * @param string $entity       a entity name to filter
     * @param array  $filterResult the result of a filter
     * 
     * @return object $this for chaining
     */
    public function setFilterCache($entity, $filterResult)
    {
        $contextId= $this->getCacheId();
        $this->filterCache[$contextId][$entity] = $filterResult;
        return $this;
    }
    /**
     * Create if not exists and return the filter result stored in cache 
     * 
     * @param string $entity  the entity name we want to get from filter cache
     * @param array  $default default value to return if not set
     * 
     * @return array contextual entity name filter result
     */
    public function getFilterCache($entity, $default=array())
    {
        $contextId= $this->getCacheId();
        if (isset($this->filterCache[$contextId][$entity])) {
            return $this->filterCache[$contextId][$entity];
        }
        return $default;
    }
    /**
     * Return wether a filter is cached
     *  
     * @param string $entity the name of an entity
     * 
     * @return bool
     */
    public function hasFilterCache($entity)
    {
        $contextId= $this->getCacheId();
        return isset($this->filterCache[$contextId][$entity]);
    }
    /**
     * Set a path in the cache associated to a entity name and a context
     * 
     * @param string $entity a entity name
     * @param string $path   a path, if false this object could not load entity
     * 
     * @return object $this for chaining
     */
    public function setCache($entity, $path)
    {
        $contextId= $this->getCacheId();
        $this->pathsCache[$contextId][$entity]=$path;
        return $this;
    }
    /**
     * return a contextual path for an entity if cached, otherwise false 
     * 
     * @param string $entity  a entity name
     * @param string $default the default paths to return if not in cache
     * 
     * @return string a path or false if not in cache
     */
    public function getCache($entity, $default=false)
    {
        $contextId= $this->getCacheId();
        if (isset($this->pathsCache[$contextId][$entity])) {
            return $this->pathsCache[$contextId][$entity];
        }
        return $default;
    }
    /**
     * Delete all path and filter cache entries
     * 
     * @return object $this for chaining
     */
    public function clearCache()
    {
        $this->pathsCache  = array();
        $this->filterCache = array();
        return $this;
    }
    /**
     * Return a minimized context id to store datas in cache
     * 
     * @return string a minimized context identifier
     */
    public function getCacheId()
    {
        return md5(
            serialize(
                array(
                    $this->context->what(),
                    $this->context->where(),
                    $this->context->when()
                )
            )
        );
    }
}