<?php
/**
 * File Config.php
 *
 * PHP version 5.2
 *
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Abstract.php
 *
 */
require_once __DIR__.DIRECTORY_SEPARATOR.'Glue.php';
/**
 * Base class for config objects.
 * They all have a name and a content, that must be validated on setting
 * 
 * @category Abstract
 * @package  Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Abstract
 *
 */
abstract class ATM_Config_Abstract extends ATM_Config_Glue_Abstract
{
    protected $name;
    protected $content;
    /**
     * Set the name of this object and notify 'setNameOn' to observers 
     * 
     * @param string $name the new name
     * 
     * @return object $this for chaining
     */
    public function setName($name)
    {
        $this->name =& $this->validateName(&$name);
        $this->notify('setNameOn', $this, &$this->name);
        return $this;
    }
    /**
     * return the name of this object
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * set the value of this object and notify 'setContentOn'
     * 
     * @param mixed $content the value associated to this
     * 
     * @return object this for chaining
     */
    public function setContent($content)
    {
        $this->content =& $this->validateContent(&$content);
        $this->notify('setContentOn', $this, &$this->content);
        return $this;
    }
    /**
     * Return a content that can be set within the given argument. 
     * 
     * @param mixed &$content datas to set in this object
     * 
     * @return mixed the content
     */
    protected function &validateContent(&$content)
    {
        return $content;
    }
    /**
     * Return a name that can be set within the given argument. 
     * 
     * @param string &$name the name of this object to validate
     * 
     * @return string the name
     */
    protected function &validateName(&$name)
    {
        $name = (string) $name;
        return $name;
    }
    /**
     * return the content
     * 
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}