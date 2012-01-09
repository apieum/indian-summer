<?php
/**
 * File ATM_Autoload_StartWith.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_StartWith.php
 *
 */

require_once __DIR__.DIRECTORY_SEPARATOR.'RuleParams.php';
require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__,'..', 'Abstract', 'RuleCache.php'));

/**
 * This rule search for entities prepended by a given string.
 * Entities names are identified by :
 * - a prefix : helps to filter entities managed by this rule ('atm' for system)
 * - a short name : the entity identity (ex. Container...) -> the file name
 * - a package : the categorie of entity
 * - optionaly a type (otherwise use default) : ex. abstracts, interfaces...  
 * 
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_StartWith
 *
 */
class ATM_Autoload_StartWith_Rule extends ATM_Autoload_RuleCache_Abstract
{
    public static $default = 'lib';
    public static $filter  = 'ATM';
    /**
     * Initialise default parameters
     * Parameters are set in container
     * 
     * @param string $baseDir the base directory from wich we search files
     * @param string $filter  the filter used by 'know'
     * @param string $default default type of entity
     * 
     * @return object sanitized parameters
     */
    public static function initParams($baseDir=null, $filter=null, $default=null)
    {
        if (is_null($baseDir)) {
            $baseDir = self::implodePath(__DIR__, '..', '..');
        }
        if (is_null($filter)) {
            $filter = self::$filter;
        }
        if (is_null($default)) {
            $default = self::$default;
        }
        return new ATM_Autoload_RuleParams($baseDir, $filter, $default);
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
        $result = explode('_', $entity);
        if (count($result) < 2 
            || array_shift($result) !== $this->params->getFilter()
        ) {
            return array();
        }
        $return['pack'] = $result[0];
        $last   = array_pop($result);
        $name   = array_pop($result);
        $subdir = implode(DIRECTORY_SEPARATOR, $result);
        $path = self::implodePath($this->params->getBaseDir(), $subdir, $last);
        if (is_dir($path)) {
            $return['name'] = $name;
            $return['type'] = $last;
        } else {
            $return['name'] = $last;
            $return['type'] = $this->params->getDefaultType();
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
            $this->getPackage($entity),
            $this->whoIs($entity),
            $this->getName($entity).'.php'
        );
        if (file_exists($path)) {
            return $path;
        } else {
            throw new LogicException(sprintf("Path '%s' not exists.", $path));
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
        $type = $this->getType($entity);
        if ($type == 'lib') {
            return '';
        } else {
            return $type;
        }
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
        $fCache = $this->getFilterCache($entity);
        return isset($fCache['pack']) ? $fCache['pack'] : null;
        
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
        $fCache = $this->getFilterCache($entity);
        return isset($fCache['name']) ? $fCache['name'] : null;
    }
    /**
     * return the type of an entity 
     * 
     * @param string $entity the entity
     * 
     * @return string|null
     */
    public function getType($entity)
    {        
        $fCache = $this->getFilterCache($entity);
        return isset($fCache['type']) ? $fCache['type'] : null;
    }
}