<?php
/**
 * File Section.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Section.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Directive.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'Collection.php';
 /**
 * A section is a container wich can store ATM_Config_Abstract objects :
 * - comments
 * - directives
 * - sections
 * - ... 
 *  
 * @category Automne
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Section
 *
 */
class ATM_Config_Section 
    extends ATM_Config_Directive
    implements ArrayAccess, IteratorAggregate
{
    /**
     * Constructor 
     * 
     * @param string $name    the name of the section
     * @param mixed  $value   the value
     * @param string $comment an optionnal comment
     */
    public function __construct($name=null, $value=array(), $comment=null)
    {
        $value = new ATM_Config_Collection($value, &$this);
        parent::__construct($name, $value, $comment);
    }
    /**
     * Implement of IteratorAggregate -> return an iterator
     * 
     * @return Iterator an iterator
     */
    public function getIterator()
    {
        $this->content->iterateOn('content');
        return $this->content;
    }
    /**
     * test if an offset is set
     * 
     * @param string $offset the name of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetExists()
     * @return bool wether the offset exists
     */
    public function offsetExists($offset)
    {
        return $this->content->searchOffsets($offset, 'names')!=array();
    }
    /**
     * return the offsets with name $offset
     * 
     * @param string $offset the name of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetGet()
     * @return array objects of this with name $offset 
     */
    public function offsetGet($offset)
    {
        return $this->content->searchAndGet($offset, 'names');
    }
    /**
     * test if an offset is set
     * 
     * @param string $offset the name of an ATM_Config_Abstract object
     * @param mixed  $value  the value of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetExists()
     * @return bool wether the offset exists
     */
    public function offsetSet($offset, $value)
    {
        $value =&$this->validate($offset, &$value);
        $this->content->append(&$value);
    }
    /**
     * Validate the name and the value.
     * if value is :
     * - a string -> become a directive
     * - an array or ArrayObject -> become a section
     * - a child of config objects -> stay as is 
     * Then set the name to value
     *  
     * @param string $name  the name of the config object
     * @param mixed  $value the value to objectise
     * 
     * @return object of class ATM_Config_Abstract
     */
    public function validate($name, $value)
    {
        if ($value instanceof ATM_Config_Abstract) {
            $value->setName($name);
            return $value;
        }
        if ($value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if (is_array($value)) {
            return new ATM_Config_Section($name, $value);
        }
        return new ATM_Config_Directive($name, $value);
    }
    /**
     * test if an offset is set
     * 
     * @param string $offset the name of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetExists()
     * @return bool wether the offset exists
     */
    public function offsetUnset($offset)
    {
        $this->content->searchAndUnset($offset, 'names');
    }
}