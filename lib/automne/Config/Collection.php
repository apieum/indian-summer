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
require_once 'Abstract'.DIRECTORY_SEPARATOR.'IterableCollection.php';
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
class ATM_Config_Collection extends ATM_Config_IterableCollection_Abstract
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
    public function __construct (array $array=array(), $parent=null)
    {
        $this->bind(&$parent);
        foreach ($array as $value) {
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
     * return the keys of the property $property where value equals $search
     * 
     * @param mixed  $search   the value to search in property
     * @param string $property the name of the property
     * 
     * @throws InvalidArgumentException
     * @return array
     */
    public function searchOffsets($search, $property='content')
    {
        if (!in_array($property, $this->properties)) {
            $msg = "This object has no searchable property with name '%s'";
            throw new InvalidArgumentException(sprintf($msg, $property));
        }
        return array_keys($this->$property, $search, true);
    }

    /**
     * Copy property' selected datas ($datas[1]) from $this to $datas[0]
     *  
     * @param string  $property the name of the property
     * @param integer $key      the key in $this->properties
     * @param array   &$datas   0=>dest. object, 1=> offsets to copy
     * 
     * @return interger the key
     */
    protected function copyProperty($property, $key, &$datas)
    {
        $datas[0]->$property = array_values(
            array_intersect_key($this->$property, $datas[1])
        );
        return $key;
    }
    /**
     * Unset $offsets off the property $property
     *  
     * @param string  $property the name of the property
     * @param integer $key      the key in $this->properties
     * @param array   $offsets  list of keys to unset
     * 
     * @return interger the key
     */
    protected function unsetProperty($property, $key, $offsets)
    {
        $this->$property = array_values(array_diff_key($this->$property, $offsets));
        return $key;
    }
    /**
     * Replaces all values where $property equals $search by $newValue
     * 
     * @param mixed  $search   the value to search in property
     * @param object $newValue the replacement value
     * @param string $property the name of the property
     * 
     * @return object this for chaining
     */
    public function searchAndReplace($search, $newValue, $property='content')
    {
        $offsets = $this->searchOffsets($search, $property);
        foreach ($offsets as $offset) {
            $this->offsetSet($offset, &$newValue);
        }
        return $this;
    }
    /**
     * return a new collection binded to this with values selected by
     * $search on property $property 
     * 
     * @param mixed  $search   the value to search in property
     * @param string $property the name of the property
     * 
     * @return object a new collection of the same class of this
     */
    public function searchAndGet($search, $property='content')
    {
        $offsets = array_flip($this->searchOffsets($search, $property));
        $collection = new static();
        $this->walkOn('properties', 'copyProperty', array(&$collection, $offsets));
        $collection->bind($this);
        return $collection;
    }
    /**
     * Unset all offsets where $property equals $value
     * 
     * @param mixed  $search   the value to search in property
     * @param string $property the name of the property
     * 
     * @return object this for chaining
     */
    public function searchAndUnset($search, $property='content')
    {
        $this->notify('searchAndUnset', $search, $property);
        $offsets = array_flip($this->searchOffsets($search, $property));
        $this->walkOn('properties', 'unsetProperty', $offsets);
        return $this;
    }

    /**
     * Return a new collection object binded to this,
     * which contains only objects of class $class
     *
     * @param string $class a class name
     *
     * @return object a collection with objects of class $class
     */
    public function filterClasses($class)
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
    public function filterNames($name)
    {
        return $this->searchAndGet($name, 'names');
    }
}
