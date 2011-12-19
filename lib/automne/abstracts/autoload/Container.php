<?php
/**
 * File atmAutoloadContainerAbstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainerAbstract.php
 *
 */

/**
 * Rule container for autoloading
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
 * @category Abstracts
 * @package  Autoload/Container
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainerAbstract
 *
 */
abstract class atmAutoloadContainerAbstract
{
    protected $rules = array();
    protected $context;
    /**
     * Constructor
     * set context and behaviours
     * 
     * @param object $context the context object
     */
    public function __construct($context=null)
    {
        if (!is_object($context)) {
            $context = $this->context;
            $context = new $context('autoload', 'container');
        }
        $this->setContext(&$context);
    }
    /**
     * Attach a rule object
     * 
     * @param object $rule   The object to add.
     * @param array  $params The data to associate with the object.
     * 
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
     * Detach a rule object
     * 
     * @param object $rule the object to remove.
     * 
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
        $class = $this->getRuleClass($type);
        @include_once $this->getRuleFile($class);
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
        $class = $this->getRuleClass($type);
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
        $class = $this->getRuleClass($type);
        @include_once $this->getRuleFile($class);
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
    public function getRuleFile($class)
    {
        return $this->context
            ->proceed('include rule file from class', array($class));
    }
    /**
     * Call the behaviour 'get rule class from type' to return a rule class
     * 
     * @param string $type the type of rule
     * 
     * @return string a class name
     */
    public function getRuleClass($type)
    {
        return $this->context
            ->proceed('get rule class from type', array($type));
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
        $this->context =& $context;
        $this->initBehaviours();
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
     * Initialize default behaviours of this object
     * 
     * @return $this
     */
    public function initBehaviours()
    {
        // implement in child
        return $this;
    } 
}
