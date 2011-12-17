<?php
/**
 * File atmAutoloadContainer.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainer.php
 *
 */

require_once 
    implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'core', 'atmCoreContext.php'));

/**
 * Rule container for autoloading
 * Extends SplObjectStorage
 * This container load rule files, create and store rule objects 
 * then it add them to the SPL autoloader.
 * 
 * More details on what it do :
 * <ul>
 * <li>resolving the name of a rule, and including the right file</li>
 * <li>construct and store the rule object, then register it on autoloader</li>
 * <li>give an acccess to rules parameters</li>
 * <li>create a context if not exists because context may influence autoload</li> 
 * <li>add context behaviours if not exists to resolve rules classes and files</li>
 * </ul>
 * 
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainer
 *
 */
class atmAutoloadContainer
{
    protected $rules        = array();
    protected $rulePrefix   = 'atmAutoload';
    protected $ruleFileExt  = 'php';
    protected $context;
    /**
     * Constructor
     * set defaults methods for rule class and rule file resolution
     * 
     * @param object $context the context object
     */
    public function __construct($context=null)
    {
        if (!is_object($context)) {
            $context = new atmCoreContext('autoload', 'container');
        }
        $this->setContext(&$context);
    }
	/**
	 * OffsetSet Alias
	 * 
	 * @param object $rule   The object to add.
	 * @param array  $params The data to associate with the object.
	 *
	 * @link http://www.php.net/manual/en/splobjectstorage.attach.php
	 * @return object $this for chaining 
	 */
    public function attach($rule, $params=array())
    {
        $paramsId = strval($params);
        $class = get_class($rule); 
        $this->rules[$class][$paramsId]=&$rule;
        spl_autoload_register(array(&$this->rules[$class][$paramsId], 'load'));
        return $this;
    }
	/**
	 * OffsetUnset Alias
	 * 
	 * @param object $rule the object to remove.
	 * 
	 * @link http://www.php.net/manual/en/splobjectstorage.detach.php
	 * @return object $this for chaining 
	 */
    public function detach($rule)
    {
        $paramsId = strval($rule->getParams());
        $class    = get_class($rule);
        return $this->unregister($class, $paramsId);
    }
    /**
     * Unregister a rule from spl autoload and unset it from storage
     * 
     * @param string $class    a class name
     * @param string $paramsId a params identifiant
     * 
     * @return object $this for chaining
     */
    protected function unregister($class, $paramsId)
    {
        if (isset($this->rules[$class][$paramsId])) {
            spl_autoload_unregister(array($this->rules[$class][$paramsId], 'load'));
            unset($this->rules[$class][$paramsId]);
        }
        return $this;
    }
    /**
     * Add a rule
     * A rule is set by :
     * <ul>
     * <li>a type that permit to find rule class name</li>
     * <li>parameters to contruct the rule object</li>
     * </ul>  
     * 
     * @param string $type   the type of rule to find wich class a rule use
     * @param array  $params parameters used to create the observer object
     * 
     * @return object $this for chaining
     */
    public function addRule($type, $params=array())
    {
        $class = $this->context->proceed('get rule class from type', array($type));
        $this->context->proceed('include rule file from class', array($class));
        $rule = new $class($this->context, $params);
        $this->attach(&$rule, $rule->getParams());
        return $this;
    }
    /**
     * Delete a rule
     * Find the observer attached to this and detach it, 
     * for a given type of rule and parameters used to set it.
     * 
     * @param string $type   the type of rule
     * @param array  $params params used to create the corresponding rule observer
     * 
     * @return object $this for chaining
     */
    public function delRule($type, $params=array())
    {
        $class = $this->context->proceed('get rule class from type', array($type));
        if (class_exists($class)) {
            $paramsId = strval($class::initParams($this->context, $params));
            $this->unregister($class, $paramsId);
        }
        return $this;
    }
    /**
     * return a rule object from it type and its initials parameters
     * 
     * @param string $type   the type of the rule
     * @param array  $params parameters used to add the rule in the container
     * 
     * @return object the rule attached to the container
     */
    public function &getRule($type, $params=array())
    {
        $class = $this->context->proceed('get rule class from type', array($type));
        $this->context->proceed('include rule file from class', array($class));
        $paramsId = strval($class::initParams($this->context, $params));
        if (!isset($this->rules[$class][$paramsId])) {
            $this->addRule($type, $params);
        }
        return $this->rules[$class][$paramsId];
    }
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
        return (bool) @include_once $path.$class.".".$this->ruleFileExt;
    }
    /**
     * Default function called to convert a rule type into a class name
     * By default we prefixes the Type with classPrefix property
     * 
     * @param string $type the type of rule
     * 
     * @example with default prefix and rule 'ordered' returns 'atmAutoloadOrdered'
     * @return string a class name
     */
    public function getDefaultRuleClass($type)
    {
        return $this->rulePrefix.ucfirst($type);
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
     * by default : 'atmAutoload'
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
     * Set the context of this object
     * 
     * @param atmCoreContext $context a context
     * 
     * @return $this;
     */
    public function setContext( atmCoreContext $context)
    {
        $this->context =& $context;
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
    /**
     * Return the current context
     * 
     * @return array the current context
     */
    public function &context()
    {
        return $this->context;
    }
}
