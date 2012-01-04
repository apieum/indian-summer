<?php
/**
 * File ATM_Autoload_RuleParams.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleParams.php
 *
 */

/**
 * Manage Rule parameters.
 * It has 3 parameters :
 * - base directory : the directory from where find files
 * - filter : a filter that match class names to test wether the rule know an entity
 * - default type : value returned when whois method can't resolve an entity type
 *  
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_RuleParams
 *
 */
class ATM_Autoload_RuleParams
{
    protected $filter;
    protected $baseDir;
    protected $default;
    /**
     * Constructor 
     * 
     * @param string $baseDir the base directory from where find classes
     * @param string $filter  the filter applied by 'know' on classes names
     * @param string $default the default type of entities
     */
    public function __construct($baseDir=null, $filter=null, $default=null)
    {
        $this
            ->setBaseDir($baseDir)
            ->setFilter($filter)
            ->setDefaultType($default);
    }
    /**
     * set baseDir, filter and default from the given argument
     * 
     * @param mixed $params ATM_Autoload_RuleParams object or array to merge
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
}