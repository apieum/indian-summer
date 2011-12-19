<?php
/**
 * File atmAutoloadContainerTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainer.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'Container.php';

/**
 * Test class for atmAutoloadContainer.
 * 
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadContainer
 *
 */
class atmAutoloadContainerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmAutoloadContainer
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
        $this->object = new atmAutoloadContainer();
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
            $this->object->context()->hasBehaviour('get rule class from type')
        );
        $this->assertTrue(
            $this->object->context()->hasBehaviour('include rule file from class')
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
        $this->object->setRuleClassPrefix('testAutoload');
        $this->assertFalse(class_exists('testAutoloadContainerRule'));
        $this->object->addRule('containerRule');
        $rule   = $this->object->getRule('containerRule');
        $expect = array($rule, 'load');
        $rule->getParams()->setDefaultType('defaultType');//
        $this->assertTrue(class_exists('testAutoloadContainerRule'));
        $this->assertTrue(in_array($expect, spl_autoload_functions()));
        // unload
        $this->object->delRule('containerRule');
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
        $this->object->setRuleClassPrefix('testAutoload');
        $this->object->setRuleFileExtension('.php');
        // test if options correctly sets
        $this->assertAttributeEquals('testAutoload', 'rulePrefix', $this->object);
        $object= $this->object->context()->getBehaviour('get rule class from type');
        $this->assertAttributeEquals('testAutoload', 'rulePrefix', $object[0]);
        $this->assertAttributeEquals('php', 'ruleFileExt', $this->object);
        $this->assertAttributeEquals('php', 'ruleFileExt', $object[0]);
        $type = $this->object->getDefaultRuleClass('Container');
        $this->assertEquals('testAutoloadContainer', $type);
        // load
        $this->assertFalse(class_exists('testAutoloadContainer'));
        $rule = $this->object->getRule('Container');
        $this->assertTrue(class_exists('testAutoloadContainer'));
        $this->assertTrue(in_array(array($rule, 'load'), spl_autoload_functions()));
        // unload
        $this->object->detach($rule);
        $this->assertFalse(in_array(array($rule, 'load'), spl_autoload_functions()));
    }
}
?>
