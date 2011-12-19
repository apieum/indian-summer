<?php
/**
 * File atmAutoloadStartWithTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadStartWithTest.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);
require_once $baseDir.$relDir.'StartWith.rule.php';
require_once $baseDir.$relDir
    .implode(DIRECTORY_SEPARATOR, array('..', 'core', 'Context.php'));

/**
 * Test class for atmAutoloadStartWith.
 * 
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadStartWithTest
 *
 */

class atmAutoloadStartWithTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmAutoloadStartWith
     */
    protected $object;
    protected $context;
    protected $params=array(__DIR__,'test', 'lib');
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
        $this->context = new atmCoreContext('startWith', 'test');
        $this->object  = new atmAutoloadStartWith($this->context, $this->params);
    }


    /**
     * can get params object without creating a rule
     * 
     * @test
     * @return null
     */
    public function canGetParamsObjectWithoutCreatingARule()
    {
        $this->assertEquals(
            $this->object->getParams(),
            atmAutoloadStartWith::initParams($this->context, $this->params)
        );
    }

    /**
     * When params are initialised if null are given rule set defaults
     * 
     * @test
     * @return object params
     */
    public function whenParamsAreInitialisedIfNullValuesAreGivenRuleSetsDefaults()
    {
        $params = atmAutoloadStartWith::initParams($this->context, array());
        $this->assertEquals('atm', $params->getFilter());
        $this->assertNotEquals('atm', $this->object->getParams()->getFilter());
        $this->assertEquals('lib', $params->getDefaultType());
        $this->assertEquals($this->baseDir, $params->getBaseDir());
        return $params;
        
    }
    /**
     * Default basedir is the parent of parent rule directory
     * 
     * @param object $params parameters object set with defaults
     * 
     * @test
     * @depends whenParamsAreInitialisedIfNullValuesAreGivenRuleSetsDefaults
     * @return null 
     */
    public function defaultBaseDirIsTheParentOfParentRuleDirectory($params)
    {
        $pOfpDir = implode(DIRECTORY_SEPARATOR, array($this->libDir, '..','..'));
        $this->assertEquals(realpath($pOfpDir), $params->getBaseDir());
    }

    /**
     * filter classes that start with params filter
     *  
     * @test
     * @return null
     */
    public function filterClassesThatStartWithParamsFilter()
    {
        $start       = $this->object->getParams()->getFilter();
        $mustBeFound = $start.'Class';
        $this->assertTrue($this->object->filter($mustBeFound)!=array());
        $mustNotFound= $start;
        $this->assertTrue($this->object->filter($mustNotFound)==array());
        $mustNotFound= $start.'classAbstract';
        $this->assertTrue($this->object->filter($mustNotFound)==array());
    }
    /**
     * know classes that start with params filter
     *  
     * @test
     * @return null
     */
    public function knowClassesThatStartWithParamsFilter()
    {
        $mustBeFound = 'testPackageStartWithType';
        $this->assertTrue($this->object->cacheKnow($mustBeFound));
        $this->assertTrue($this->object->cacheKnow($mustBeFound));
        $this->assertTrue($this->object->cacheKnow($mustBeFound));
        $mustNotFound= 'test';
        $this->assertFalse($this->object->cacheKnow($mustNotFound));
        $mustNotFound= 'testclassAbstract';
        $this->assertFalse($this->object->cacheKnow($mustNotFound));
    }
    /**
     * return name package and whois with one word
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithOneWord()
    {
        $entity = $this->object->getParams()->getFilter().'Name';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('name', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->whois($entity));
    }
    /**
     * return name package and whois with one word
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithTwoWords()
    {
        $entity = $this->object->getParams()->getFilter().'PackageName';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('package', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->whois($entity));
        $entity = $this->object->getParams()->getFilter().'NameType';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('name', $this->object->getPackage($entity));
        $this->assertEquals('types', $this->object->whois($entity));
    }
    /**
     * return name package and whois with one word
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithThreeWords()
    {
        $entity = $this->object->getParams()->getFilter().'PackageNameType';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('package', $this->object->getPackage($entity));
        $this->assertEquals('types', $this->object->whois($entity));
        $entity = $this->object->getParams()->getFilter().'PackageCompoundName';
        $this->object->cacheKnow($entity);
        $this->assertEquals('CompoundName', $this->object->getName($entity));
        $this->assertEquals('package', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->whois($entity));
    }
    /**
     * return name package and whois with one word
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithMoreThanThreeWords()
    {
        $entity = $this->object->getParams()
            ->getFilter().'PackageMultiCompoundNameType';
        $this->object->cacheKnow($entity);
        $this->assertEquals('MultiCompoundName', $this->object->getName($entity));
        $this->assertEquals('package', $this->object->getPackage($entity));
        $this->assertEquals('types', $this->object->whois($entity));
    }


    /**
     * load a file from path cache
     * 
     * @test
     * @return null
     */
    public function loadAFileFromPathCache()
    {
        $entity = 'undefinedClass';
        $file=implode(DIRECTORY_SEPARATOR, array(__DIR__, 'types','undefined.php'));
        $this->object->getParams()->setCache($entity, $file);
        $this->assertTrue($this->object->cacheKnow($entity));
        $this->assertFalse($this->object->know($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
    }
    /**
     * load a file from know cache
     * 
     * @test
     * @return null
     */
    public function loadAFileFromKnowCache()
    {
        $entity = 'testPackageStartWithType';
        $this->assertTrue($this->object->cacheKnow($entity));
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
        
    }
    /**
     * load a file not in cache
     * 
     * @test
     * @return null
     */
    public function loadAFileNotInCache()
    {
        $entity = 'testPackageStartWith';
        $this->assertFalse(
            $this->object->getParams()->getFilterCache($entity, false)
        );
        $this->assertFalse(
            $this->object->getParams()->getCache($entity, false)
        );
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
        
    }
    
}
?>
