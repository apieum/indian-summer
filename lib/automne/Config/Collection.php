<?php
/**
 * File Collection.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection.php
 *
 */
require_once 'Abstract'.DIRECTORY_SEPARATOR.'SeekableCollection.php';
/**
 * This collection stores objects herited from type 'ATM_Config_Abstract'
 * and provides search on objects classes and names (return of getName).
 *
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection
 *
 */
class ATM_Config_Collection extends ATM_Config_SeekableCollection_Abstract
{
    public static $objectsType = 'ATM_Config_Abstract';
    protected $classes    = array();
    protected $names      = array();
    protected $properties = array('objIds', 'content', 'names', 'classes');
    /**
     * Construct a new Collection binded to $parent if set
     * fill the object with values of the given argument if not empty
     *
     * @param array  $array  optional values to set
     * @param object $parent a parent object to modify when these values changes
     */
    public function __construct ($array=array(), $parent=null)
    {
        $this->bind(&$parent);
        foreach ( (array) $array as $value) {
            $this->append($value);
        }
    }
    /**
     * Set content value at offset $offset
     *
     * @param integer $offset the indice to set
     * @param mixed   &$value the value of content at this offset
     *
     * @return mixed $value
     */
    protected function classesSetter($offset, &$value)
    {
        $this->classes[$offset] = get_class($value);
        return $value;
    }
    /**
     * Set content value at offset $offset
     *
     * @param integer $offset the indice to set
     * @param mixed   &$value the value of content at this offset
     *
     * @return mixed $value
     */
    protected function namesSetter($offset, &$value)
    {
        $this->names[$offset] = &$value->getName();
        return $value;
    }

    /**
     * Return a new collection object binded to this,
     * which contains only objects of class $class
     *
     * @param string $class a class name
     *
     * @return object a collection with objects of class $class
     */
    public function getCollectionOfClass($class)
    {
        return $this->searchAndGet($class, 'classes');
    }
    /**
     * Return a new collection object binded to this,
     * which contains only objects with name $name
     *
     * @param string $name an object name
     *
     * @return object a collection fill with objects named $name
     */
    public function getCollectionOfName($name)
    {
        return $this->searchAndGet($name, 'names');
    }
}
