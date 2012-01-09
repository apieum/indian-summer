<?php
/**
 * File ATM_Autoload_ContainerTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container.php
 *
 */

$libDir = str_replace('tests'.DIRECTORY_SEPARATOR, '', __DIR__);
$automnePath =  array($libDir, '..', 'Autoload','Autoload.php');
require_once implode(DIRECTORY_SEPARATOR, $automnePath);
ATM_Autoload::register();

/**
 * Test class for ATM_Autoload_Container.
 * 
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_Container
 *
 */
class ATM_Autoload_ContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Autoload_Container
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new ATM_Autoload_Container();
    }

    /**
     * set behaviours to context if not set
     * 
     * @test
     * @return null
     */
    public function setBehavioursToContextIfNotSet()
    {
        $this->assertTrue(
            $this->object->context()->hasBehaviour('get rule class')
        );
        $this->assertTrue(
            $this->object->context()->hasBehaviour('include rule file')
        );
    }
    /**
     * Test if we can override defaults RuleClass and RuleFile 
     * 
     * @test
     * @return null
     */
    public function overrideRuleClassAndRuleFileFunctions()
    {
        $testFunc = create_function('$string', 'return $string;');
        $fixture = 'Container';
        $expect = $this->object->getDefaultRuleClass($fixture);
        $this->object->context()
            ->addBehaviour('get rule class from type', $testFunc);
        $this->object->context()
            ->addBehaviour('include rule file from class', $testFunc);
        $ruleClass=$this->object->context()
            ->proceed('get rule class from type', array($fixture));
        $ruleFile=$this->object->context()
            ->proceed('include rule file from class', array($fixture));
        $this->assertNotEquals($expect, $ruleClass);
        $this->assertEquals($fixture, $ruleClass);
        $this->assertEquals($fixture, $ruleFile);
    }
    /**
     * Load a rule add it tu SPL Autoload and remove it
     * 
     * @test
     * @return null
     */
    public function loadARuleAddItToSPLAutoloadAndRemoveIt()
    {
        $this->object->context()->describe('rules path', __DIR__);
        $this->object->setRuleClassPrefix('testAutoload_');
        $this->assertFalse(class_exists('testAutoload_Container_Rule'));
        $this->object->addRule('container');
        $rule   = $this->object->getRule('container');
        $expect = array($rule, 'load');
        $rule->getParams()->setDefaultType('defaultType');//
        $this->assertTrue(class_exists('testAutoload_Container_Rule'));
        $this->assertTrue(in_array($expect, spl_autoload_functions()));
        // unload
        $this->object->delRule('container');
        $this->assertFalse(in_array($expect, spl_autoload_functions()));
        
    }
    /**
     * add a rule with getter then detach it
     * 
     * @test
     * @return null
     */
    
    public function changeOptionsAndAddARuleWithGetterThenDetachIt()
    {
        // set some options
        $this->object->context()->describe('rules path', __DIR__);
        $this->object->setRuleClassPrefix('testAutoload_');
        $this->object->setRuleClassSuffix('');
        $this->object->setRuleFileExtension('.rule.php');
        // test if options correctly sets
        $this->assertAttributeEquals('testAutoload_', 'rulePrefix', $this->object);
        $this->assertAttributeEquals('', 'ruleSuffix', $this->object);
        $object= $this->object->context()->getBehaviour('get rule class');
        $this->assertAttributeEquals('testAutoload_', 'rulePrefix', $object[0]);
        $this->assertAttributeEquals('rule.php', 'ruleFileExt', $this->object);
        $this->assertAttributeEquals('rule.php', 'ruleFileExt', $object[0]);
        $type = $this->object->getDefaultRuleClass('Container');
        $this->assertEquals('testAutoload_Container', $type);
        // load
        $this->assertFalse(class_exists('testAutoload_Container'));
        $rule = $this->object->getRule('Container');
        $this->assertTrue(class_exists('testAutoload_Container'));
        $this->assertTrue(in_array(array($rule, 'load'), spl_autoload_functions()));
        // unload
        $this->object->detach($rule);
        $this->assertFalse(in_array(array($rule, 'load'), spl_autoload_functions()));
    }
}
?>
