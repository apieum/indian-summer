<?php
/**
 * File ATM_ContextTests.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Context.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$fname=str_replace('Test', '', basename(__FILE__));
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'Context.php';
/**
 * Test class for atmCoreContext.
 * 
 * @category AutomneTests
 * @package  Tests/Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_ContextTest
 *
 */
class ATM_ContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Context
     */
    protected $context;

    /**
     * Sets up the fixture.
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->fixSubject = "{Subject}";
        $this->fixEnv     = "{Environment}";
        $this->context = new ATM_Context($this->fixSubject, $this->fixEnv);
    }
    /**
     * a context depends on subject, environment and moment
     * 
     * @return @test
     */
    public function aContextDependsOnSubjectEnvironmentAndMoment()
    {
        $this->assertEquals($this->fixSubject, $this->context->what());
        $this->assertEquals($this->fixEnv, $this->context->where());
        $this->assertEquals(ATM_Context::DEFAULT_MOMENT, $this->context->when());
    }

    /**
     * Context subject, environment and moment can change
     * 
     * @return @test
     */
    public function contextSubjectEnvironmentAndMomentCanChange()
    {
        $this->context
            ->with('newSubject')
            ->into('newEnv')
            ->during('newMoment');
        $this->assertEquals('newSubject', $this->context->what());
        $this->assertEquals('newEnv', $this->context->where());
        $this->assertEquals('newMoment', $this->context->when());
    }
    /**
     * Each context as an identity depending on it properties
     * 
     * @return @test
     */
    public function contextIdentityDependsOnContextProperties()
    {
        $expected = new ATM_Context($this->fixSubject, $this->fixEnv);
        $this->assertEquals($expected->identify(), $this->context->identify());
    }
    /**
     * A context can be described
     * 
     * @return @test
     */
    public function canDescribeAContext()
    {
        $this->context->describe("the context exists", true);
        $this->assertTrue($this->context->about("the context exists"));
        $this->context->describe("alias", "Test Context");
        $this->assertEquals("Test Context", $this->context->about("alias"));
    }
    /**
     * When description not exists 'about' return default
     * 
     * @return @test
     */
    public function whenDescriptionNotExistsAboutReturnDefault()
    {
        
        $this->assertEquals("", $this->context->about("alias"));
        $this->assertEquals(
        	"Test Context",
            $this->context->about("alias", "Test Context")
        );
    }
    /**
     * Can delete a description by setting it to null 
     *
     * @return @test
     */
    public function canDeleteADescription()
    {
        $this->context->popOut("alias");
        $this->assertEquals("", $this->context->about("alias"));
        $this->context->describe("alias", "Test Context");
        $this->assertEquals("Test Context", $this->context->about("alias"));
        $this->context->popOut("alias");
        $this->assertEquals("", $this->context->about("alias"));
        
    }
    /**
     * 'about' is contextual
     * 
     * @return @test
     */
    public function aboutIsContextual()
    {
        $alias = "{context} Context";
        $this->context->describe("alias", $alias);
        $this->assertEquals($alias, $this->context->about('alias'));
        $this->context->describe("context", "Test");
        $expected = $this->context->normalize($alias);
        $this->assertEquals($expected, $this->context->about('alias'));
    }
    /**
     * Descriptions helps to contextualize strings
     * 
     * @return @test
     */
    public function descriptionsHelpsToContextualizeStrings()
    {
        $this->context->describe("alias", "Test Context");
        $this->context->describe("fixture", "alias is '{alias}'");
        $expected = "alias is 'Test Context'";
        $this->assertEquals($expected, $this->context->about('fixture'));
        $this->context->describe("alias", "Test String Values");
        $expected = "alias is 'Test String Values'";
        $this->assertEquals($expected, $this->context->about('fixture'));
    }
    /**
     * Descriptions helps to contextualize arrays
     * 
     * @return @test
     */
    public function descriptionsHelpsToContextualizeArrays()
    {
        $alias = "Test Context";
        $this->context->describe("alias", $alias);
        $this->context->describe(
        	"fixture", 
            array("alias is '{alias}'", "we are in {alias}")
        );
        $expected = array("alias is '$alias'" , "we are in $alias");
        $this->assertEquals($expected, $this->context->about('fixture'));
        $alias = "Test String Values";
        $this->context->describe("alias", $alias);
        $expected = array("alias is '$alias'" , "we are in $alias");
        $this->assertEquals($expected, $this->context->about('fixture'));
    }
    /**
     * Descriptions helps to contextualize objects
     * 
     * @return @test
     */
    public function descriptionsHelpsToContextualizeObjects()
    {
        $alias = "Test Context";
        $this->context->describe("alias", $alias);
        $fixture  = new stdClass();
        $fixture->aliasIs = "alias is '{alias}'";
        $fixture->weAreIn = "we are in {alias}";
        $this->context->describe('fixture', $fixture);
        $expected  = new stdClass();
        $expected->aliasIs = "alias is '$alias'";
        $expected->weAreIn = "we are in $alias";
        $this->assertEquals($expected, $this->context->about('fixture'));
        $alias = "Test String Values";
        $this->context->describe("alias", $alias);
        $expected->aliasIs = "alias is '$alias'";
        $expected->weAreIn = "we are in $alias";
        $this->assertEquals($expected, $this->context->about('fixture'));
    }
    /**
     * Normalize is recursive
     * 
     * @return @test
     */
    public function normalizeIsRecursive()
    {
        $this->context->describe("context", "Test");
        $this->context->describe("alias", "{context} Context");
        $fixture  = "alias is '{alias}'";
        $expected = "alias is 'Test Context'";
        $this->assertEquals($expected, $this->context->normalize($fixture));
    }
    /**
     * If description not exists return value as is
     * 
     * @return @test
     */
    public function ifDescriptionNotExistsReturnValueAsIs()
    {
        $this->context->describe("alias", "{context} Context");
        $fixture  = "alias is '{alias}'";
        $expected = "alias is '{context} Context'";
        $this->assertEquals($expected, $this->context->normalize($fixture));
    }
    /**
     * Subject, environment, and moment can be contextual
     * 
     * @return @test
     */
    public function subjectEnvironmentAndMomentCanBeContextual()
    {
        $this->context->describe('Subject', 'the subject');
        $this->context->describe('Environment', 'the environment');
        $this->context->during('{moment}')->describe('moment', 'while testing');
        $this->assertEquals('the subject',     $this->context->what());
        $this->assertEquals('the environment', $this->context->where());
        $this->assertEquals('while testing',   $this->context->when());
    }
    /**
     * Context is also defined by behaviours
     * 
     * @return @test
     */
    public function contextIsAlsoDefinedByBehaviours()
    {
        $defaultId = $this->context->identify();
        $this->context->addBehaviour(
        	'normalize',
            array($this->context, 'normalize')
        );
        $this->assertNotEquals($defaultId, $this->context->identify());
        $this->context->delBehaviour('normalize');
        $this->assertEquals($defaultId, $this->context->identify());
    }
    /**
     * can set a behaviour once with get and default
     * 
     * @return @test
     */
    public function canSetABehaviourOnceWithGetAndDefault()
    {
        $this->assertFalse($this->context->hasBehaviour('test'));
        $this->context->addBehaviour(
        	'test',
            $this->context->getBehaviour('test', 'test')
        );
        $this->assertEquals('test', $this->context->getBehaviour('test', false));
        $this->context->addBehaviour(
        	'test',
            $this->context->getBehaviour('test', 'other behaviour')
        );
        $this->assertEquals('test', $this->context->getBehaviour('test', false));
    }
    /**
     * Behaviours contextualize function call
     * 
     * @return @test
     */
    public function behavioursContextualizeFunctionCall()
    {
        $this->context->describe('parameter', 'default');
        $func = create_function('', 'return "{parameter}";');
        $this->context->addBehaviour('getValueWithoutArgs', $func);
        $this->assertEquals(
        	'default',
            $this->context->proceed('getValueWithoutArgs')
        );
        $func = create_function('$arg', 'return $arg;');
        $this->context->addBehaviour('getValue', $func);
        $this->assertEquals(
        	'default',
            $this->context->proceed('getValue', array('{parameter}'))
        );
        $this->context->describe('parameter', 'other');
        $this->assertEquals(
        	'other',
            $this->context->proceed('getValue', array('{parameter}'))
        );
        
    }
    /**
     * Behaviours contextualize method call
     * 
     * @return @test
     */
    public function behavioursContextualizeMethodCallArguments()
    {
        $this->context->describe('parameter', '{context}');
        $this->context->describe('context', 'default');
        $cNormalize = array($this->context, 'normalize');
        $this->context->addBehaviour('normalize', $cNormalize);
        $this->assertAttributeEquals(
            array('normalize'=>$cNormalize), 'behaviours', $this->context
        );
        $this->assertEquals(
        	'default',
            $this->context->proceed('normalize', array('{parameter}'))
        );
        $this->context->describe('context', 'other');
        $this->assertEquals(
        	'other',
            $this->context->proceed('normalize', array('{parameter}'))
        );
        
    }
    /**
     * return a string with the first character to uppercase
     * 
     * @param string $string the string to 'normalize'
     * 
     * @return string the uppercased string
     */
    public function normalize($string)
    {
        return ucfirst($string);
    }
    /**
     * Behaviours contextualize method call objects
     * 
     * @return @test
     */
    public function behavioursContextualizeMethodCallObjects()
    {
        $this->context->describe('parameter', 'default');
        $this->context->describe('context',  $this->context);
        $this->context->describe('testcase', $this);
        $this->context->addBehaviour('normalize', array('{normalize}','normalize'));
        $this->context->describe('normalize', '{testcase}');
        $this->assertEquals(
        	'Default',
            $this->context->proceed('normalize', array('{parameter}'))
        );
        $this->context->describe('normalize', '{context}');
        $this->assertEquals(
        	'default',
            $this->context->proceed('normalize', array('{parameter}'))
        );
        
    }
    
    /**
     * Behaviours contextualize objects construction
     * 
     * @return @test
     */
    public function behavioursContextualizeObjectsConstruction()
    {
        $this->context->describe('parameter', array('default'));
        $this->context->describe('myObject',  'stdClass');
        $this->context->addBehaviour('testObject', '{myObject}');
        $myObject = $this->context->proceed('testObject');
        $this->assertTrue($myObject instanceof stdClass);
        $this->context->describe('myObject',  'ArrayObject');
        $myObject = $this->context->proceed('testObject', array('{parameter}'));
        $this->assertTrue($myObject instanceof ArrayObject);
        $this->assertEquals('default', $myObject[0]);
    }
    /**
     * contexts help to share objects
     * 
     * @return @test
     */
    public function contextsHelpToShareObjects()
    {
        $this->context->describe('myObject',  'stdClass');
        $this->context->addBehaviour('testObject', '{myObject}');
        $myObject1 = $this->context->proceedOnce('testObject');
        $this->assertTrue($myObject1 instanceof stdClass);
        $myObject1->property    = 'property'; 
        $myObject1->hasProperty = true;
        $myObject2 = $this->context->proceedOnce('testObject');
        $this->assertTrue($myObject2->hasProperty);
        $this->assertEquals('property', $myObject2->property);
        $this->assertSame($myObject1, $myObject2);
    }
    /**
     * know wether behaviours are classes or not
     * 
     * @return @test
     */
    public function knowWetherBehavioursAreClassesOrNot()
    {
        // test with system functions
        $this->context->addBehaviour('behaviour', '{myBehaviour}');
        $this->context->describe('myBehaviour',  'ucfirst');
        $this->assertFalse($this->context->isClass('behaviour'));
        $this->assertTrue($this->context->isCallable('behaviour'));
        // test with system class
        $this->context->describe('myBehaviour',  'stdClass');
        $this->assertTrue($this->context->isClass('behaviour'));
        $this->assertFalse($this->context->isCallable('behaviour'));
        // test with lambda function
        $func = create_function('', 'return true;');
        $this->context->describe('myBehaviour',  $func);
        $this->assertFalse($this->context->isClass('behaviour'));
        $this->assertTrue($this->context->isCallable('behaviour'));
        // test with user class
        $this->context->describe('myBehaviour',  'ATM_ContextTest');
        $this->assertTrue($this->context->isClass('behaviour'));
        $this->assertFalse($this->context->isCallable('behaviour'));
        // test with method
        $this->context->describe('myBehaviour',  array($this, 'normalize'));
        $this->assertFalse($this->context->isClass('behaviour'));
        $this->assertTrue($this->context->isCallable('behaviour'));
    }

    /**
     * descriptions can be recursives
     * 
     * @return @test 
     */
    public function recursivesDescriptionsCanBeUsedWithBehaviours()
    {
        $args1 = array(array(0,1,2));
        $args2 = array(array(2,1,0));
        $this->context->describe('arguments de "Array Object"', $args1);
        $this->context->describe('arguments de "Fixed Array"', $args2);
        $this->context->describe('arguments', '{arguments de "{object}"}');
        $this->context->addBehaviour('Array Object', 'ArrayObject');
        $this->context->addBehaviour('Fixed Array', array('SplFixedArray','fromArray'));
        $this->context->describe('object', 'Array Object');
        $this->assertEquals($args1, $this->context->about('arguments'));
        $array1 = $this->context->proceed('{object}', '{arguments de "{object}"}');
        $this->context->describe('object', 'Fixed Array');
        $this->assertEquals($args2, $this->context->about('arguments'));
        $array2 = $this->context->proceed('{object}', '{arguments}');
        $this->assertNotEquals($array1, $array2);
        try {
            $array1[] = 3;
            $array2[] = -1;
        } catch (RuntimeException $e) {
            $this->assertTrue(isset($array1[3]));
            $this->assertFalse(isset($array2[3]));
        }
        $this->assertTrue(isset($e));
    }    

}
?>
