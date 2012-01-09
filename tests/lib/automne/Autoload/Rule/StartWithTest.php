<?php
/**
 * File ATM_Autoload_StartWith_RuleTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_StartWith_RuleTest.php
 *
 */

$libDir = str_replace('tests'.DIRECTORY_SEPARATOR, '', __DIR__);
$automnePath =  array($libDir, '..', '..', 'Autoload','Autoload.php');
require_once implode(DIRECTORY_SEPARATOR, $automnePath);
ATM_Autoload::register();

/**
 * Test class for ATM_Autoload_StartWith_Rule.
 * 
 * @category AutomneTests
 * @package  Tests/Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Autoload_StartWith_RuleTest
 *
 */

class ATM_Autoload_StartWith_RuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Autoload_StartWith_Rule
     */
    protected $object;
    protected $baseDir;
    protected $libDir;

    /**
     * Sets up the fixture.
     * 
     * @return null
     */
    protected function setUp()
    {    
        $dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
        $relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
        $baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);
        $this->baseDir = $baseDir.'lib'.DIRECTORY_SEPARATOR.'automne';
        $this->libDir  = $baseDir.$relDir;
        $this->object  = new ATM_Autoload_StartWith_Rule(__DIR__, 'test', 'lib');
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
            ATM_Autoload_StartWith_Rule::initParams(__DIR__, 'test', 'lib')
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
        $params = ATM_Autoload_StartWith_Rule::initParams();
        $this->assertEquals('ATM', $params->getFilter());
        $this->assertNotEquals('ATM', $this->object->getParams()->getFilter());
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
        $pOfpDir = implode(DIRECTORY_SEPARATOR, array($this->libDir, '..', '..'));
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
        $mustBeFound = $start.'_Class';
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
        $mustBeFound = 'test_Package_StartWith_Type';
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
        $entity = $this->object->getParams()->getFilter().'_Name';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('Name', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->getType($entity));
        $this->assertEquals('', $this->object->whois($entity));
    }
    /**
     * return name package and whois with two words
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithTwoWords()
    {
        $entity = $this->object->getParams()->getFilter().'_Package_Name';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('Package', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->getType($entity));
        $entity = $this->object->getParams()->getFilter().'_Package_Type';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Package', $this->object->getName($entity));
        $this->assertEquals('Package', $this->object->getPackage($entity));
        $this->assertEquals('Type', $this->object->whois($entity));
    }
    /**
     * return name package and whois with three words
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithThreeWords()
    {
        $entity = $this->object->getParams()->getFilter().'_Package_Name_Type';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $this->assertEquals('Package', $this->object->getPackage($entity));
        $this->assertEquals('Type', $this->object->whois($entity));
        $entity = $this->object->getParams()->getFilter().'_Package_CompoundName';
        $this->object->cacheKnow($entity);
        $this->assertEquals('CompoundName', $this->object->getName($entity));
        $this->assertEquals('Package', $this->object->getPackage($entity));
        $this->assertEquals('lib', $this->object->getType($entity));
    }
    /**
     * return name package and whois with more than three words
     * 
     * @test
     * @return null
     */
    public function returnNamePackageAndWhoisForKnownClassesWithMoreThanThreeWords()
    {
        $entity = $this->object->getParams()
            ->getFilter().'_Package_Multi_Compound_Name_Type';
        $this->object->cacheKnow($entity);
        $this->assertEquals('Name', $this->object->getName($entity));
        $expPack = str_replace('_', DIRECTORY_SEPARATOR, 'Package_Multi_Compound');
        $this->assertEquals($expPack, $this->object->getPackage($entity));
        $this->assertEquals('Type', $this->object->whois($entity));
    }
    /**
     * return name package and whois with more than three words
     * 
     * @test
     * @return null
     */
    public function returnWhereisForKnownClassesWithMoreThanThreeWords()
    {
        $entity = $this->object->getParams()
            ->getFilter().'_Package_Multi_Compound_Name_Type';
        $this->object->cacheKnow($entity);
        $expPath = array('Package', 'Multi', 'Compound', 'Type','Name.php');
        $expPath = implode(DIRECTORY_SEPARATOR, $expPath);
        $this->assertContains($expPath, $this->object->whereIs($entity));
    }

    /**
     * can clear cache 
     * 
     * @test
     * @return null
     */
    public function canClearCache()
    {
        $entity = 'undefinedClass';
        $file=$this->object->implodePath(__DIR__, 'Type', 'undefined.php');
        $this->object->setCache($entity, $file);
        $this->assertTrue($this->object->cacheKnow($entity));
        $this->assertFalse($this->object->know($entity));
        $this->object->clearCache();
        $this->assertFalse($this->object->cacheKnow($entity));
        $this->object->load($entity);
        $this->assertFalse(class_exists($entity));
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
        $file=$this->object->implodePath(__DIR__, 'Type', 'undefined.php');
        $this->object->setCache($entity, $file);
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
        $entity = 'test_Package_StartWith_Type';
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
        $entity = 'test_Package_StartWith';
        $this->assertFalse(
            $this->object->getFilterCache($entity, false)
        );
        $this->assertFalse(
            $this->object->getCache($entity, false)
        );
        $this->assertFalse(class_exists($entity));
        $this->object->load($entity);
        $this->assertTrue(class_exists($entity));
        
    }
    /**
     * whereIs throw a logic exception if a file not exists
     * 
     * @return @test
     */
    public function throwLogicExceptionIfAFileNotExists()
    {
        $entity = $this->object->getParams()->getFilter().'_Package_Type';
        $this->object->cacheKnow($entity);
        $except=implode(DIRECTORY_SEPARATOR, array('Package', 'Type','Package.php'));
        $this->assertContains($except, $this->object->whereIs($entity));
        $entity = $this->object->getParams()->getFilter().'_Name_Type';
        $this->object->cacheKnow($entity);
        try {
            $this->object->whereIs($entity);
        } catch (LogicException $logicExc) {
            $except = implode(DIRECTORY_SEPARATOR, array('Name', '','Type.php'));
            $this->assertContains($except, $logicExc->getMessage());
        }
        $this->assertNotNull($logicExc);
    }
    
}
?>
