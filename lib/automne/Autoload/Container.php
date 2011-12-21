<?php
/**
 * File ATM_Autoload_Container.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload/Container
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container.php
 *
 */

require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'Context', 'Context.php'));
require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Abstract', 'Container.php'));

/**
 * Rule container for automne autoloading
 * Extends ATM_Autoload_Container_Abstract
 * 
 * add rules loaders (behaviours) to abstract class 
 * 
 * @category Automne
 * @package  Autoload/Container
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container
 *
 */
class ATM_Autoload_Container extends ATM_Autoload_Container_Abstract
{
    protected $rulePrefix   = 'ATM_Autoload';
    protected $ruleFileExt  = 'rule.php';
    protected $context = 'ATM_Context';
    
    /**
     * Default function called to find the file of a class containing a rule
     * By default we return a file in the same directory
     * 
     * @param string $class the name of the class we want to load
     * 
     * @return string a file to include
     */
    public function getDefaultRuleFile($class)
    {
        $path = $this->context->about('rules path', __DIR__).DIRECTORY_SEPARATOR;
        return $path.$class.".".$this->ruleFileExt;
    }
    /**
     * Default function called to convert a rule type into a class name
     * By default we prefixes the Type with classPrefix property
     * 
     * @param string $type the type of rule
     * 
     * @example with default prefix and rule 'ordered' returns 'ATM_AutoloadOrdered'
     * @return string a class name
     */
    public function getDefaultRuleClass($type)
    {
        return $this->rulePrefix.'_'.ucfirst($type);
    }
    /**
     * Set the file extension appended to the rule class file name.
     * Dot is removed if given.
     * 
     * @param string $extension a file extension (by default php)
     * 
     * @return object $this for chaining 
     */
    public function setRuleFileExtension($extension)
    {
        if ($extension[0] == '.') {
            $extension = substr($extension, 1);
        }
        $this->ruleFileExt = $extension;
        return $this;
    }
    /**
     * Set the string prepended to rule type used to make the default class name
     * by default : 'ATM_Autoload'
     * 
     * @param string $prefix a string to prepend to a rule type
     * 
     * @return object $this for chaining
     */
    public function setRuleClassPrefix($prefix)
    {
        $this->rulePrefix = (string) $prefix;
        return $this;
    }
    /**
     * Set default behaviours
     *  
     * @return $this;
     */
    public function initBehaviours()
    {
        if ($this->context->hasBehaviour('include rule file from class') == false) {
            $this->context->addBehaviour(
                'include rule file from class',
                array(&$this, 'getDefaultRuleFile')
            );
        }
        if ($this->context->hasBehaviour('get rule class from type') == false) {
            $this->context->addBehaviour(
                'get rule class from type',
                array(&$this, 'getDefaultRuleClass')
            );
        }
        return $this;
    }
}
