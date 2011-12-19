<?php
/**
 * File atmAutoload.php
 *
 * PHP version 5.2
 *
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoload.php
 *
 */
require_once 'Container.php';
/**
 * This class stores the main autoload container and load the defaults rules.
 *  
 * @category Automne
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoload
 *
 */
class atmAutoload
{
    protected static $autoloader;
    /**
     * Return the autoload container object.
     * If not set, the autoload container is set.
     *  
     * @return atmAutoloadContainer the main autoload container
     */
    public static function &autoloader()
    {
        if (!isset(self::$autoloader)) {
            self::$autoloader=new atmAutoloadContainer();
        }
        return self::$autoloader;
    }
    /**
     * Register defaults rules
     * the core use 2 rules :
     * - startWith : load entities that starts with 'atm'
     * - MinusAndContext : load entities from environment
     * 
     * @return atmAutoloadContainer the main autoload container
     */
    public static function &register()
    {
        self::autoloader()
            ->addRule('startWith')
            ->addRule('MinusAndContext');
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
            ->delRule('startWith')
            ->delRule('MinusAndContext');
        unset(self::$autoloader);
    }
}
