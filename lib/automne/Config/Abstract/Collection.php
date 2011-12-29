<?php
/**
 * File Collection.php
 *
 * PHP version 5.2
 *
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection_Abstract.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Glue.php';
/**
 * A collection is a binded list of ordened objects with the same herited type.
 * "Binded" because modifications of the list are notified to observers
 * "Ordened" because indices are numerics and if index is not set value is appended.
 * "Same type" because only objects that implements self::$objectsType are sets
 * (override method 'allowed' to change behaviour)
 * It can stores objects properties by adding their names in property 'properties' 
 * and by implementing a setter compounds with property name followed by 'Setter'.
 * Each object is stored in 'content' with an unique identifier set in 'objIds' 
 * used to find the same object in observers.   
 * 
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection_Abstract
 *
 */
abstract class ATM_Config_Collection_Abstract 
    extends ATM_Config_Glue_Abstract implements ArrayAccess
{
    /**
     * a class or an interface that objects must implements
     * Must be set in childs 
     * 
     * @var string
     */
    public static $objectsType;
    /**
     * Informations about stored objects. Order matter
     * Minimal is objIds (unique id used internal) and content (the object itself)
     *  
     * @var unknown_type
     */
    protected $properties = array('objIds', 'content');
    protected $content    = array();
    protected $objIds     = array();
    protected $InvArgMsg  ="Value must be an instance of '%s' : '%s' given.";

    /**
     * set whether the class or the name at offset $datas[0] with value $datas[1]
     *  
     * @param string  $property the name of the property
     * @param integer $key      the key in $this->properties
     * @param array   &$datas   0=> offset, 1=> value
     * 
     * @return integer the key
     */
    protected function setProperty($property, $key, &$datas)
    {
        $method = $property.'Setter';
        $this->$method($datas[0], &$datas[1]);
        return $key;
    }
    /**
     * Set content value at offset $offset
     * 
     * @param integer $offset the indice to set
     * @param mixed   &$value the value of content at this offset
     * 
     * @return mixed $value
     */
    protected function contentSetter($offset, &$value)
    {
        if (!isset($this->content[$offset])) {
            $offset = count($this->content);
        }
        $this->content[$offset] =& $value;
        return $value;
    }
    /**
     * Set object identifier at offset $offset
     * notify 'searchAndReplace' if id already set
     * 
     * @param integer $offset the indice to set
     * @param mixed   &$value the value of content at this offset
     * 
     * @return mixed $value
     */
    protected function objIdsSetter($offset, &$value)
    {
        if (isset($this->objIds[$offset])) {
            $objectId = $this->objIds[$offset];
            $this->notify('searchAndReplace', $objectId, &$value, 'objIds');
        } else {
            $this->objIds[$offset]=uniqid($offset);
        }
        return $value;
    }
    /**
     * Unset $offsets off the property $property
     *  
     * @param string  $property the name of the property
     * @param integer $key      the key in $this->properties
     * @param array   $offset   the offset to unset
     * 
     * @return null
     */
    protected function spliceOneProperty($property, $key, $offset)
    {
        array_splice($this->$property, $offset, 1);
        return $key;
    }
    /**
     * test if an offset is set
     * 
     * @param integer $offset the position of an object
     * 
     * @see ArrayAccess::offsetExists()
     * @return bool whether the offset is set
     */
    public function offsetExists($offset)
    {
        return isset($this->content[$offset]);
    }
    /**
     * return the object at offset $offset
     * 
     * @param integer $offset the index of an object
     * 
     * @see ArrayAccess::offsetGet()
     * @return object of type self::$objectsType 
     */
    public function offsetGet($offset)
    {
        return $this->content[$offset];
    }
    /**
     * if value is an object of type defined in self::$objectType
     * -> set the offset if exists otherwise append it
     * else
     * -> throw an exception
     * 
     * @param integer $offset the offset to set
     * @param object  $value  an object of type defined by self::$objectType
     * 
     * @see ArrayAccess::offsetExists()
     * @throws InvalidArgumentException
     * @return null
     */
    public function offsetSet($offset, $value)
    {
        if ($this->allowed($value) == false) {
            $msg=sprintf($this->InvArgMsg, static::$objectsType, get_class($value));
            throw new InvalidArgumentException($msg);
        }
        $this->walkOn('properties', 'setProperty', array($offset, &$value));
        
    }
    /**
     * Return whether the value is authorized in this collection 
     * 
     * @param object $value a value to test
     * 
     * @return bool
     */
    public function allowed($value)
    {
        return ($value instanceof static::$objectsType);
    }
    /**
     * unset an offset and notify 'searchAndUnset'
     * 
     * @param int $offset the name of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetExists()
     * @return bool wether the offset exists
     */
    public function offsetUnset($offset)
    {
        if (isset($this->content[$offset])) {
            $this->notify('searchAndUnset', $offset, 'objIds');
            $this->walkOn('properties', 'spliceOneProperty', $offset);
        }
    }
    /**
     * append value to collection and bubble it to observers if $bubble is true
     * and observers implements method 'appendWithId'
     * 
     * @param object $value  object of type self::$objectsType
     * @param bool   $bubble if true value is appended to observers 
     * 
     * @return object this for chaining
     */
    public function append($value, $bubble=false)
    {
        $offset = count($this->content);
        $this->offsetSet($offset, &$value);
        if ($bubble === true) {
            $this->notify('appendWithId', &$value, $this->objIds[$offset]);
        }
        return $this;
    }
    /**
     * append value to collection with a defined objectId, always notify it.
     * (called by append on observers when bubbling)
     * 
     * @param object $value    object of type self::$objectsType
     * @param string $objectId an object identifier
     * 
     * @return object this for chaining
     */
    protected function appendWithId($value, $objectId)
    {
        $offset = count($this->content);
        $this->offsetSet($offset, &$value);
        $this->objIds[$offset]=$objectId;
        $this->notify('appendWithId', &$value, $objectId);
        return $this;
    }
}
