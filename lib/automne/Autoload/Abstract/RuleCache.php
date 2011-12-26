<?php
/**
 * File ATM_Autoload_RuleCache_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleCache_Abstract.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'Rule.php';

/**
 * Rule class with cache
 * helps to include files required by entities: (abstract) classes, interfaces...
 * This base rule managed 2 caches :
 * - filter results cache
 * - paths resolution cache
 * 
 * Know or whereIs first try to get paths from cache.
 * So if you set entities paths manually in cache, they will be returned.
 * 
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleCache_Abstract
 *
 */
abstract class ATM_Autoload_RuleCache_Abstract extends ATM_Autoload_Rule_Abstract
{
    protected $filterCache= array();
    protected $pathsCache = array();
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
        $cacheId = $this->getCacheId();
        $this->filterCache[$cacheId][$entity] = $filterResult;
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
        $cacheId = $this->getCacheId();
        if (isset($this->filterCache[$cacheId][$entity])) {
            return $this->filterCache[$cacheId][$entity];
        }
        return $default;
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
        $cacheId = $this->getCacheId();
        $this->pathsCache[$cacheId][$entity]=$path;
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
        $cacheId = $this->getCacheId();
        if (isset($this->pathsCache[$cacheId][$entity])) {
            return $this->pathsCache[$cacheId][$entity];
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
        return md5(serialize($this->params));
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
        if ($this->getCache($entity, false)!==false) {
            return true;
        }
        $filterCache = $this->getFilterCache($entity, false);
        if ($filterCache !== false) {
            return $filterCache !== array();
        } else {
            $filterCache = $this->filter($entity);
            $this->setFilterCache($entity, $filterCache);
            return $filterCache !== array();
        }
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
        $where = $this->getCache($entity, false);
        if ($where === false) {
            $where = $this->whereIs($entity);
            $this->setCache($entity, $where);
        }
        return $where;
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
        if ($this->cacheKnow($entity)) {
            $where = $this->cacheWhereIs($entity);
            return (bool) @include_once $where;
        }
        return false;
    }
}