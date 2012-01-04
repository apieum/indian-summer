<?php
/**
 * File ATM_Autoload_Container_Abstract.php
 *
 * PHP version 5.2
 *
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container_Abstract.php
 *
 */

/**
 * Rule container for autoloading
 * This container load rule files, create and store rule objects 
 * then it add them to the SPL autoloader.
 * 
 * More details :
 * <ul>
 * <li>it resolves the class of a rule, and include the right file</li>
 * <li>constructs the rule object, then register it on autoloader</li>
 * <li>stores rules with initials parameters and class name</li>
 * <li>provides a way to set wich rule method is called to load a class</li>
 * </ul>
 * 
 * @category Abstracts
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container_Abstract
 *
 */
abstract class ATM_Autoload_Container_Abstract
{
    protected $rules          = array();
    protected $rulesMethod    = array();
    public static $loadMethod = 'load';
    /**
     * Attach a rule object and register it to spl_autoload
     * 
     * @param object $rule   The rule object to attach.
     * @param array  $params The data to associate with the object.
     * 
     * @return object $this for chaining
     */
    public function attach($rule, $params=array())
    {
        $class    = get_class($rule);
        $ruleId   = $this->getRuleIdFromClass($class, $params);
        $method   = $this->getLoaderMethod($class, self::$loadMethod);
        $this->rules[$class][$ruleId] =& $rule;
        spl_autoload_register(array(&$this->rules[$class][$ruleId], $method));
        $this->setLoaderMethod($class, $method);
        return $this;
    }
    /**
     * Detach a rule object
     * 
     * @param object $rule   the object to remove.
     * @param array  $params datas associated to the rule at creation.
     * 
     * @return object $this for chaining 
     */
    public function detach($rule, $params=array())
    {
        $class    = get_class($rule);
        $ruleId   = $this->getRuleIdFromClass($rule, $params);
        return $this->unregister($class, $ruleId);
    }
    /**
     * Unregister a rule from spl autoload and unset it from storage
     * 
     * @param string $class  a class name
     * @param string $ruleId a params identifiant
     * 
     * @return object $this for chaining
     */
    protected function unregister($class, $ruleId)
    {
        if (isset($this->rules[$class][$ruleId])) {
            $method = $this->getLoaderMethod($class, self::$loadMethod);
            spl_autoload_unregister(array($this->rules[$class][$ruleId], $method));
            unset($this->rules[$class][$ruleId]);
        }
        return $this;
    }
    /**
     * Add a rule
     * A rule is set by :
     * <ul>
     * <li>a name that permit to find rule class name</li>
     * <li>parameters to contruct the rule object, used to find it later</li>
     * </ul>  
     * 
     * @param string $ruleName the rule name
     * @param array  $params   parameters used to create the observer object
     * 
     * @return object $this for chaining
     */
    public function addRule($ruleName, $params=array())
    {
        $class = $this->getRuleClass($ruleName);
        include_once $this->getRuleFile($ruleName);
        $class = new ReflectionClass($class);
        $rule = $class->newInstanceArgs($params);
        $this->attach(&$rule, $params);
        return $this;
    }
    /**
     * Delete a rule
     * Find the attached rule with the rule name and params used to instanciate it,
     * then detach it and unregister it from autoload.
     * 
     * @param string $ruleName the rule name
     * @param array  $params   params used to create the rule
     * 
     * @return object $this for chaining
     */
    public function delRule($ruleName, $params=array())
    {
        $class = $this->getRuleClass($ruleName);
        if (class_exists($class)) {
            $ruleId = $this->getRuleIdFromClass($class, $params);
            $this->unregister($class, $ruleId);
        }
        return $this;
    }
    /**
     * return a rule object from it's name and parameters used to create it
     * 
     * @param string $ruleName the rule name
     * @param array  $params   parameters used to add the rule in the container
     * 
     * @return object the rule attached to the container
     */
    public function &getRule($ruleName, $params=array())
    {
        $class = $this->getRuleClass($ruleName);
        include_once $this->getRuleFile($ruleName);
        $ruleId = $this->getRuleIdFromClass($class, $params);
        if (!isset($this->rules[$class][$ruleId])) {
            $this->addRule($ruleName, $params);
        }
        return $this->rules[$class][$ruleId];
    }
    /**
     * return an id for params and rule class
     * default based only on initial parameters
     * 
     * @param object $ruleClass a rule class
     * @param array  $params    parameters used to set the rule we search
     * 
     * @return string an identifiant
     */
    public function getRuleIdFromClass($ruleClass, $params=array())
    {
        return md5(serialize($params));
    }
    /**
     * Set the method to register in autoload associated to a rule name
     * 
     * @param string $ruleName the rule name
     * @param string $method   the method the autoload call to load a file
     * 
     * @return object $this for chaining
     */
    public function setRuleMethod($ruleName, $method)
    {
        return $this->setLoaderMethod($this->getRuleClass($ruleName), $method);
    }
    /**
     * Get the method that would be called by autoload for a rule name
     * 
     * @param string $ruleName the rule name
     * @param string $default  the value to return if not set
     * 
     * @return string the method associated to a rule or default
     */
    public function getRuleMethod($ruleName, $default='load')
    {
        return $this->getLoaderMethod($this->getRuleClass($ruleName), $default);
    }
    /**
     * Set the method to register in autoload associated to a rule class
     * 
     * @param string $class  the class of the rule
     * @param string $method the method the autoload call to load a file
     * 
     * @return object $this for chaining
     */
    protected function setLoaderMethod($class, $method)
    {
        $this->rulesMethod[$class] = $method;
        return $this;
    }
    /**
     * Get the method that would be called by autoload for a rule class
     * 
     * @param string $class   the class of the rule
     * @param string $default the value to return if not set
     * 
     * @return string the method associated to a rule or default
     */
    protected function getLoaderMethod($class, $default='load')
    {
        if (isset($this->rulesMethod[$class])) {
            return $this->rulesMethod[$class];
        }
        return $default; 
    }
    /**
     * Return the file to include for a rule class
     * 
     * @param string $ruleName the rule name
     * 
     * @return string a file to include
     */
    abstract public function getRuleFile($ruleName);
    /**
     * Return a rule class from a rule name
     * 
     * @param string $ruleName the rule name
     * 
     * @return string a class name
     */
    abstract public function getRuleClass($ruleName);
}
