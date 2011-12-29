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
class ATM_Config_Section extends ATM_Config_Directive
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
        parent::__construct(
            $name, 
            new ATM_Config_Collection(array(), $this),
            $comment
        );
        array_walk(array_flip($value), array($this, 'offsetSet'));
        
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
        return $this->content->filterNames($offset);
    }
    /**
     * Append value to content depending on type:
     * - string -> become a directive
     * - array  -> become a section
     * - config object -> stay as is 
     * 
     * @param string $offset the name of an ATM_Config_Abstract object
     * @param mixed  $value  the value of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetSet()
     * @return object the created object
     */
    public function offsetSet($offset, $value)
    {
        if ($this->content->allowed($value)) {
            return $this->addConfigObject($offset, &$value);
        }
        if (is_array($value)) {
            return $this->addSection($offset, &$value);
        }
        return $this->addDirective($offset, &$value);
    }
    /**
     * Unset all objects with name $offset
     * 
     * @param string $offset the name of a ATM_Config_Abstract object
     * 
     * @see ArrayAccess::offsetUnset()
     * @return null
     */
    public function offsetUnset($offset)
    {
        $this->content->searchAndUnset($offset, 'names');
    }
    /**
     * Create and Append a Directive with name $name and value $value
     * 
     * @param string   $name  the name of directive
     * @param scalable $value a value
     * 
     * @return object a directive
     */
    public function addDirective($name, $value)
    {
        $directive = new ATM_Config_Directive($name, $value);
        $this->content->append(&$directive);
        return $directive;
    }
    /**
     * Return the directive with name $name at position $position in result
     * 
     * @param string  $name     the name of directive
     * @param integer $position the position in the list of directive with name $name
     * 
     * @return object a directive
     */
    public function getDirective($name, $position=0)
    {
        $return = $this[$name]->filterClasses('ATM_Config_Directive');
        return $return[$position];
    }
    /**
     * Return a collection of directives with name $name   
     * 
     * @param string $name the name of directive
     * 
     * @return object a directive or a collection if $position === false
     */
    public function getDirectives($name)
    {
        return $this[$name]->filterClasses('ATM_Config_Directive');
    }
    /**
     * Create and Append a Section with name $name and value $value
     * 
     * @param string $name  the $name of section
     * @param array  $value a value
     * 
     * @return object a section
     */
    public function addSection($name, array $value)
    {
        $section = new ATM_Config_Section($name, $value);
        $this->content->append(&$section);
        return $section;
    }
    /**
     * Return a section with name $name at position $position in result
     * if $position === false return the merge of found sections   
     * 
     * @param string  $name     the name of section
     * @param integer $position the position in the list of section with name $name
     * 
     * @return object the selected section or a new one if $position is false
     */
    public function getSection($name, $position=0)
    {
        $result = $this[$name]->filterClasses('ATM_Config_Section');
        return $result[$position]; 
    }
    /**
     * Return a section merged from every section with name $name 
     * 
     * @param string $name the name of section
     * 
     * @return object a section with content merged from result
     */
    public function getSections($name)
    {
        $result = $this[$name]->filterClasses('ATM_Config_Section');
        $return = new ATM_Config_Section();
        $return->content = new ATM_Config_Collection(array(), $this);
        foreach ($result as $section) {
            $return->content->merge($section->content);
        }
        return $return;
    }
    /**
     * Create and Append a Comment with name $name and value $value
     * 
     * @param string $comment the content of comment
     * 
     * @return object a directive
     */
    public function addComment($comment)
    {
        $comment = new ATM_Config_Comment($comment);
        $this->content->append(&$comment);
        return $comment;
    }
    /**
     * Return the comment at position $position in the list of comments
     * 
     * @param integer $position the position in the list of comments
     * 
     * @return object a comment
     */
    public function getCommentAt($position=0)
    {
        $return = $this->content->filterClasses('ATM_Config_Comment');
        return $return[$position]; 
    }
    /**
     * Append an existing Config Object with the name $name 
     * 
     * @param string $name         the $name to set in object
     * @param object $configObject the object to append
     * 
     * @return object the config object
     */
    public function addConfigObject($name, ATM_Config_Abstract $configObject)
    {
        $configObject->setName($name);
        $this->content->append(&$configObject);
        return $configObject;
    }
}