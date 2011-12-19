<?php
/**
 * File atmAutoloadMinusAndContextTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadMinusAndContextTest.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);
require_once $baseDir.$relDir.'MinusAndContext.rule.php';
require_once $baseDir.$relDir
    .implode(DIRECTORY_SEPARATOR, array('..', 'context', 'Context.php'));

/**
 * Test class for atmAutoloadMinusAndContext.
 * 
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadMinusAndContextTest
 *
 */
class atmAutoloadMinusAndContextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmAutoloadOrdered
     */
    protected $object;
    protected $context;
    protected $params=array(__DIR__, null, 'types');
    protected $baseDir;
    protected $libDir;


    /**
     * Sets up the fixture.
     * 
     * @return null
     */
    protected function setUp()
    {
        global $baseDir, $relDir;
        $this->baseDir = $baseDir.'lib'.DIRECTORY_SEPARATOR.'automne';
        $this->libDir  = $baseDir.$relDir;
        $this->context = new atmContext('ordered', 'environment');
        $this->object  = new atmAutoloadMinusAndContext($this->context, $this->params);
    }
    /**
     * When params are initialised if null are given rule set defaults
     * 
     * @test
     * @return object params
     */
    public function whenParamsAreInitialisedIfNullValuesAreGivenRuleSetsDefaults()
    {
        $params = atmAutoloadMinusAndContext::initParams($this->context, array());
        $this->assertEquals(
            atmAutoloadMinusAndContext::$filter,
            $params->getFilter()
        );
        $this->assertEquals('lib', $params->getDefaultType());
        $this->assertEquals($this->baseDir, $params->getBaseDir());
        return $params;
        
    }
    /**
     * filter return an empty array if not known
     * 
     * @test
     * @return null
     */
    public function filterReturnAnEmptyArrayIfNotKnown()
    {
        $this->assertEquals(array(), $this->object->filter('startWithAMinuscule'));
        $this->assertEquals(array(), $this->object->filter('Aword'));
        $this->assertEquals(array(), $this->object->filter('AWord'));
        $this->assertEquals(array(), $this->object->filter('With1Number'));
        
    }

    /**
     * Filter return a name and a type if known
     * 
     * @test
     * @return null
     */
    public function filterReturnANameAndATypeIfKnown()
    {
        $this->assertEquals(
            array('name'=>'The', 'type'=>'words'),
            $this->object->filter('TheWord')
        );
        $this->assertEquals(
            array('name'=>'TheSentenceWithMore', 'type'=>'words'),
            $this->object->filter('TheSentenceWithMoreWord')
        );
    }
    /**
     * fail to load a file with context environment
     * 
     * @test
     * @return null
     */
    public function failToLoadAFileWithContextEnvironment()
    {
        $this->context->into('other env');
        $entity = 'MinusAndContextType';
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertFalse(class_exists($entity));
    }

    /**
     * load a file with context environment
     * 
     * @test
     * @return null
     */
    public function loadAFileWithContextEnvironment()
    {
        $entity = 'MinusAndContextType';
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
    }
    /**
     * load a file without context environment if in default only
     * 
     * @test
     * @return null
     */
    public function loadAFileWithoutContextEnvironmentIfInDefaultOnly()
    {
        $entity = 'MinusAndContextDefaultType';
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
    }
}
?>
