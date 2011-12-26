<?php
/**
 * File ATM_Autoload.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload/Container
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload.php
 *
 */
require_once 'Container.php';
/**
 * This class stores the main autoload container and load the defaults rules.
 *  
 * @category Automne
 * @package  Autoload/Container
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload
 *
 */
class ATM_Autoload
{
    protected static $autoloader;
    /**
     * Return the autoload container object.
     * If not set, the autoload container is set.
     * If a context is given the conatiner context is redefined.
     * 
     * @param object $context the context to set in container
     *  
     * @return ATM_Autoload_Container the main autoload container
     */
    public static function &autoloader($context=null)
    {
        if (!isset(self::$autoloader)) {
            self::$autoloader=new ATM_Autoload_Container($context);
        } elseif (!is_null($context)) {
            self::$autoloader->setContext($context);
        }
        return self::$autoloader;
    }
    /**
     * Register defaults rules
     * the core use 2 rules :
     * - startWith : load entities that starts with 'atm'
     * - MinusAndContext : load entities from environment
     * 
     * @param object $context a context to use with container, if not exists
     * 
     * @return ATM_Autoload_Container the main autoload container
     */
    public static function &register($context=null)
    {
        self::autoloader($context)
            ->addRule('startWith');
        return self::$autoloader;
    }
    /**
     * Delete all registered rules and unset the autoload container.
     * 
     * @return null
     */
    public static function unregister()
    {
        self::autoloader()
            ->delRule('startWith');
        unset(self::$autoloader);
    }
}
