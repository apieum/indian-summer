<?php
/**
 * File ATM_Autoload_Container.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
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
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container
 *
 */
class ATM_Autoload_Container extends ATM_Autoload_Container_Abstract
{
    protected $rulePrefix   = 'ATM_Autoload_';
    protected $ruleSuffix   = '_Rule';
    protected $ruleFileExt  = 'php';
    protected $context = 'ATM_Context';
    /**
     * Constructor 
     * 
     * @param object $context if set use this context in conatiner
     */
    public function __construct($context=null) 
    {
        if (!is_object($context)) {
            $context = $this->context;
            $context = new $context('Container', 'Autoload'); 
        }
        $this->setContext(&$context);
    }
    /**
     * Default function called to find the file of a class containing a rule
     * By default we return a file in the same directory
     * 
     * @param string $ruleName the name of the rule we want to load
     * 
     * @return string a file to include
     */
    public function getRuleFile($ruleName)
    {
        return $this->context->proceed('include rule file', array($ruleName));
    }
    /**
     * Call the behaviour 'get rule class' to return a rule class
     * 
     * @param string $ruleName the rule name
     * 
     * @return string a class name
     */
    public function getRuleClass($ruleName)
    {
        return $this->context->proceed('get rule class', array($ruleName));
    }
    /**
     * Default function called to find the file of a class containing a rule
     * By default we return a file in the same directory
     * 
     * @param string $ruleName the name of the rule we want to load
     * 
     * @return string a file to include
     */
    public function getDefaultRuleFile($ruleName)
    {
        $default = realpath(__DIR__.'/Rule');
        $path = $this->context->about('rules path', $default);
        return $path.DIRECTORY_SEPARATOR.ucfirst($ruleName).".".$this->ruleFileExt;
    }
    /**
     * Default function called to convert a rule name into a class name
     * By default we prefixes the rule name with classPrefix property
     * 
     * @param string $ruleName the rule name
     * 
     * @example with default prefix and rule 'ordered' returns 'ATM_Autoload_Ordered'
     * @return string a class name
     */
    public function getDefaultRuleClass($ruleName)
    {
        return $this->rulePrefix.ucfirst($ruleName).$this->ruleSuffix;
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
     * Set the string prepended to rule name used to make the default class name
     * by default : 'ATM_Autoload'
     * 
     * @param string $prefix a string to prepend to a rule name
     * 
     * @return object $this for chaining
     */
    public function setRuleClassPrefix($prefix)
    {
        $this->rulePrefix = (string) $prefix;
        return $this;
    }
    /**
     * Set the string prepended to rule name used to make the default class name
     * by default : 'ATM_Autoload'
     * 
     * @param string $suffix a string to append to a rule name
     * 
     * @return object $this for chaining
     */
    public function setRuleClassSuffix($suffix)
    {
        $this->ruleSuffix = (string) $suffix;
        return $this;
    }
    /**
     * Set the context of this object
     * 
     * @param atmCoreContext $context a context
     * 
     * @return $this
     */
    public function setContext($context)
    {
        if ($context !== $this->context) {
            $this->context =& $context;
            $this->initBehaviours();
        }
        return $this;
    }
    /**
     * Return the current context
     * 
     * @return array the current context
     */
    public function &context()
    {
        return $this->context;
    }
    /**
     * Set default behaviours
     *  
     * @return $this;
     */
    public function initBehaviours()
    {
        if ($this->context->hasBehaviour('include rule file') == false) {
            $this->context->addBehaviour(
                'include rule file',
                array(&$this, 'getDefaultRuleFile')
            );
        }
        if ($this->context->hasBehaviour('get rule class') == false) {
            $this->context->addBehaviour(
                'get rule class',
                array(&$this, 'getDefaultRuleClass')
            );
        }
        return $this;
    }
}
