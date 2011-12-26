<?php
/**
 * File ATM_Autoload_Rule_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Rule_Abstract.php
 *
 */


/**
 * Rule class 
 * helps to include files required by entities: (abstract) classes, interfaces...
 * They are set by a container which register their method 'load' in autoloader.
 * A rule can :
 * - filter an entity by this name, 
 * - reply if an entity is known 
 * - return where is the file to load
 * - finally include the needed file. 
 * Optionnaly it can return the type of an entity : library, view, stage... 
 * The autoload process start by asking if the entity is known, 
 * then where the entity file resides and finally includes the file.
 * 
 * Override methods filter, initParams, whereIs and WhoIs to set a rule.
 * 
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Rule_Abstract
 *
 */
abstract class ATM_Autoload_Rule_Abstract
{
    protected $params;
    
    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        $args = func_get_args();
        $this->params = call_user_func_array(array($this, 'initParams'), $args);
    }
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
        if ($this->know($entity)) {
            $where = $this->whereIs($entity);
            return (bool) @include_once $where;
        }
        return false;
    }
}