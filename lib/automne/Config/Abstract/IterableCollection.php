<?php
/**
 * File IterableCollection.php
 *
 * PHP version 5.2
 *
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_IterableCollection_Abstract.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Collection.php';
 /**
 * Add the capabilitie to Collections of beeing counted and iterated.
 * Iteration can be done on every property, just select it with 'iterateOn'.
 * Can return all the content of a property with getArrayCopy
 * 
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_IterableCollection_Abstract
 *
 */
abstract class ATM_Config_IterableCollection_Abstract 
    extends ATM_Config_Collection_Abstract
    implements Iterator, Countable
{
    protected $itSubject  = 'content';
    protected $currentKey = 0;
    /**
     * Set the property on wich iterate
     * 
     * @param string $property a property name
     * 
     * @return object $this for chaining
     */
    public function iterateOn($property)
    {
        if (in_array($property, $this->properties)) {
            $this->itSubject = $property;
        }
        return $this;
    }
    /**
     * Return current iterator value
     * 
     * @see Iterator::current()
     * @return mixed
     */
    public function current()
    {
        if ($this->valid()) {
            $subject = $this->itSubject;
            return $this->{$subject}[$this->currentKey];
        }
    }
    /**
     * Increment the current key
     * 
     * @see Iterator::next()
     * @return object current value
     */
    public function next()
    {
        $this->currentKey++;
        return $this->current();
    }
    /**
     * Test whether the current key exists in the current subject 
     * 
     * @see Iterator::valid()
     * @return bool
     */
    public function valid()
    {
        $subject = $this->itSubject;
        return array_key_exists($this->currentKey, $this->$subject);
    }
    /**
     * Return the current position
     * 
     * @see Iterator::key()
     * @return integer
     */
    public function key()
    {
        return $this->currentKey;
    }
    /**
     * Set the current position to 0
     * 
     * @see Iterator::rewind()
     * @return null
     */
    public function rewind()
    {
        $this->currentKey=0;
    }
    /**
     * return the number of elements in subject
     * 
     * @see Countable::count()
     * @return integer
     */
    public function count()
    {
        $subject = $this->itSubject;
        return count($this->$subject);
    }
    /**
     * Return a copy of the current iterated property 
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $subject = $this->itSubject;
        return $this->$subject;
    }
    
}
