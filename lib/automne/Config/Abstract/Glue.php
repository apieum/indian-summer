<?php

/**
 * File Glue.php
 *
 * PHP version 5.2
 *
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Glue_Abstract.php
 *
 */
 /**
 * Provide a way to bind objects with a subject/observer pattern on a single class
 * Each "Glued" object is at the same time a subject and an observer
 * so childs can communicate between us inside and relay changes to their observers
 * which are naturally of the same type and implements sames methods.
 *  
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Glue_Abstract
 *
 */
abstract class ATM_Config_Glue_Abstract
{
    protected $observers=array();
    protected $appendMethod='';
    /**
     * Set a suffix to append to methods name when update observers
     *  
     * @param string $string a string used to call methods
     * 
     * @return object this for chaining
     */
    public function appendToMethod($string)
    {
        $this->appendMethod = (string) $string;
        return $this;
    }
    /**
     * Return wheter an object can observe this.
     * It must :
     * - be different than this
     * - be an object
     * - have the method 'update'
     * 
     * @param object $observer the object to test
     * 
     * @return bool
     */
    public function canObserve($observer)
    {
        return 
            $observer !== $this
            && is_object($observer)
            && method_exists($observer, 'update');
    }
    /**
     * bind the directive to an observer and notify 'bindObjects'
     * 
     * @param object $observer an object with method update
     * 
     * @return object this for chaining
     */
    public function bind($observer)
    {
        if ($this->canObserve($observer) && !in_array($observer, $this->observers)) {
            $obsId = spl_object_hash($observer);
            $this->observers[$obsId] =& $observer;
            $this->notify('bindObjects', $this, &$observer);
        }
        return $this;
    }
    /**
     * unbind the observer and notify 'unbindObjects'
     * 
     * @param object $observer an object with method update
     * 
     * @return object this for chaining
     */
    public function unbind($observer)
    {
        if ($this->canObserve($observer) && in_array($observer, $this->observers)) {
            $obsId = spl_object_hash($observer);
            $this->notify('unbindObjects', $this, $this->observers[$obsId]);
            unset($this->observers[$obsId]);
        }
        return $this;
    }
    /**
     * Apply method $method with args on each value of property $property
     * 
     * @param string $property a property of this object that contains an array
     * @param string $method   a method to call
     * @param mixed  $args     arguments to pass to method
     * 
     * @return object $this for chaining
     */
    protected function walkOn($property, $method, $args=null)
    {
        array_walk($this->$property, array($this, $method), $args);
        return $this;
    }
    /**
     * notify an observer by calling his method update with $args 
     * 
     * @param object  &$observer an object with method 'update'
     * @param integer $key       the position of the call, appended to args
     * @param array   $args      arguments sent to update
     * 
     * @return null
     */
    protected function notifyObserver(&$observer, $key, $args=array())
    {
        $args[] = $key;
        call_user_func_array(array(&$observer, 'update'), $args);
    }
    /**
     * Send a message to all observers by calling update method
     * 
     * @return object this for chaining 
     */
    protected function notify()
    {
        $this->walkOn('observers', 'notifyObserver', func_get_args());
        return $this;
    }
    /**
     * Receive a message from a subject
     * 
     * @return object this for chaining 
     */
    protected function update() 
    {
        $args    = func_get_args();
        $message = array_shift($args);
        $method  = $message.$this->appendMethod;
        if (is_callable(array($this, $method))) {
            call_user_func_array(array($this, $method), $args);
        }
        return $this;
    }
}
