<?php
/**
 * File atmAutoloadRuleParams.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleParams.php
 *
 */
/**
 * Manage Rule parameters within context
 * To resolve paths to load required files, rules have parameters.
 * This class permit to manage these parameters.
 * By default a rule has 3 parameters :
 * - base directory : the directory from where find files
 * - filter : a filter that match class names to test wether the rule know an entity
 * - default type : value returned when whois method can't resolve an entity type
 * 
 * To provide a more efficient paths resolution, this class manages 2 caches :
 * - a filter cache : store filter results
 * - a paths cache : store file paths resolutions
 * 
 *   Caches depends on context subject, environment and moment 
 *   and are cleared when setting a new value to properties.
 *   
 *   Finally to retrieve a params object from initials parameters, 
 *   it returns a hash from them when it's converted to string.
 * 
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleParams
 *
 */
class atmAutoloadRuleParams
{
    protected $context;
    protected $filter;
    protected $baseDir;
    protected $default;
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
        $this->hash = md5(
            serialize(array($this->baseDir, $this->filter, $this->default))
        );
    }
    /**
     * set baseDir, filter and default from the given argument
     * 
     * @param mixed $params atmAutoloadRuleParams object or array to merge
     * 
     * @return object $this for chaining
     */
    public function merge($params)
    {
        if (is_array($params)) {
            $params     = array_merge($params, array(null, null, null));
            $baseDir    = $params[0];
            $filter     = $params[1];
            $default    = $params[2];
        } else {
            $baseDir    = $params->baseDir;
            $filter     = $params->filter;
            $default    = $params->default;
        }
        $this
            ->setBaseDir($baseDir)
            ->setFilter($filter)
            ->setDefaultType($default);
        
        return $this;
    }
    /**
     * Set the base directory from where find files to include
     * 
     * @param string $baseDir the base directory
     * 
     * @return object $this for chaining
     */
    public function setBaseDir($baseDir=null) 
    {
        if (is_null($baseDir)) {
            $baseDir = implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', '..'));
        }
        $this->baseDir = realpath($baseDir);
        $this->clearCache();
        return $this;
    }
    /**
     * return the base directory
     * 
     * @return string a path
     */
    public function getBaseDir() 
    {
        return $this->baseDir;
    }
    /**
     * the filter is generally used to analyse classes names 
     * and determine if this object can load the corresponding file 
     * 
     * @param string $filter a filter may be a regexp or a string depends on child
     * 
     * @return object $this for chaining 
     */
    public function setFilter($filter)
    {
        $this->filter=$filter;
        $this->clearCache();
        return $this;
    }
    /**
     * return the filter to apply on entities names 
     * 
     * @return string the filter 
     */
    public function getFilter()
    {
        return $this->filter;
    }
    /**
     * If this know a file to include, 
     * it may want to know wich type of object resides in the file.
     * For example, it can be a lib class, an abstract class, an interface 
     * or more fined class like a core class, a state, a stage, a view...
     * This helps to resolve in wich directory we can find the right file.
     * 
     * @param string $default the default type to return if class name can't help
     * 
     * @return object $this for chaining
     */
    public function setDefaultType($default)
    {
        $this->default=$default;
        $this->clearCache();
        return $this;
    }
    /**
     * Return the default type of an entity
     * 
     * @return string default type
     */
    public function getDefaultType()
    {
        return $this->default;
    }
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