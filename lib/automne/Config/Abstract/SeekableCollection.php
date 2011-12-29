<?php
/**
 * File SeekableCollection.php
 *
 * PHP version 5.2
 *
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_SeekableCollection_Abstract.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'IterableCollection.php';
/**
 * Provides the ability to seek a position and convenients functions to do searches
 * 
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_SeekableCollection_Abstract
 *
 */
abstract class ATM_Config_SeekableCollection_Abstract 
    extends ATM_Config_IterableCollection_Abstract implements SeekableIterator
{
    /**
     * return the value of current iterator subject at offset $position 
     * 
     * @param integer $position the position to search
     * 
     * @throws OutOfBoundsException if the position not exists
     * @return mixed value of subject at position
     */
    public function seek($position)
    {
        $subject = $this->itSubject;
        if (!isset($this->{$subject}[$position])) {
            throw new OutOfBoundsException("Undefined offset at position $position");
        }
        $this->currentKey = $position;
        return $this->{$subject}[$position];
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
}
