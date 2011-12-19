<?php
/**
 * File atmAutoloadRuleParamsTests.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleParams.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'RuleParams.php';
$cContext = implode(DIRECTORY_SEPARATOR, array('..', 'core', 'Context.php'));
require_once $baseDir.$relDir.$cContext;
/**
 * Tests for atmAutoloadRuleParams
 * 
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleParamsTest
 *
 */
class atmAutoloadRuleParamsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmAutoloadRuleParams
     */
    protected $object;
    protected $context;
    protected $params=array(__DIR__, '@.*@', 'lib');
    protected $baseDir;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     * @return null
     */
    protected function setUp()
    {
        global $baseDir;
        $this->baseDir = realpath($baseDir);
        $this->context= new atmCoreContext('subject', 'env');
        $this->object = new atmAutoloadRuleParams($this->context, $this->params);
    }

    /**
     * params contains base directory
     * 
     * @test
     * @return null
     */
    public function paramsContainsBaseDir()
    {
        $this->assertEquals(__DIR__, $this->object->getBaseDir());
        $this->object->setBaseDir($this->baseDir);
        $this->assertEquals($this->baseDir, $this->object->getBaseDir());
    }


    /**
     * params contains filter
     * 
     * @test 
     * @return null
     */
    public function paramsContainsFilter()
    {
        $this->assertEquals('@.*@', $this->object->getFilter());
        $this->object->setFilter('filter');
        $this->assertEquals('filter', $this->object->getFilter());
    }

    /**
     * params contains default type
     * 
     * @test 
     * @return null
     */
    public function paramsContainsDefaultType()
    {
        $this->assertEquals('lib', $this->object->getDefaultType());
        $this->object->setDefaultType('type');
        $this->assertEquals('type', $this->object->getDefaultType());
    }

    /**
     * Params have an identity based on initials values
     * 
     * @test
     * @return null
     */
    public function paramsHaveAnIdentityBasedOnInitialsValues()
    {
        $expect = strval($this->object);
        $this->object
            ->setBaseDir($this->baseDir)
            ->setFilter('filter')
            ->setDefaultType('type');
        $this->assertEquals($expect, strval($this->object));
    }
    /**
     * can instanciate params objects from another one
     * 
     * @test
     * @return null
     */
    public function canInstanciateParamsObjectsFromAnotherOne()
    {
        $cContext = new atmCoreContext('rule param', 'tests');
        $this->object
            ->setBaseDir($this->baseDir)
            ->setFilter('filter')
            ->setDefaultType('type');
        $newParams = new atmAutoloadRuleParams($cContext, $this->object);
        $this->assertNotEquals(strval($this->object), strval($newParams));
        $this->assertEquals($this->object->getBaseDir(), $newParams->getBaseDir());
        $this->assertEquals($this->object->getFilter(), $newParams->getFilter());
        $this->assertEquals('type', $newParams->getDefaultType());
        $this->assertNotEquals($this->object, $newParams);
        
    }
    /**
     * setBaseDir default is automne root
     * 
     * @test
     * @return null
     */
    public function setBaseDirDefaultIsAutomneRoot()
    {
        $this->object->setBaseDir();
        $expect = implode(
            DIRECTORY_SEPARATOR,
            array($this->baseDir, 'lib', 'automne')
        );
        $this->assertEquals($expect, $this->object->getBaseDir());
    }
    /**
     * can set filter cache once with get and default
     * 
     * @test
     * @return null
     */
    public function canSetFilterCacheOnceWithGetAndDefault()
    {
        $this->object->setFilterCache(
        	'entity',
            $this->object->getFilterCache('entity', 'filter')
        );
        $this->object->setFilterCache(
        	'entity',
            $this->object->getFilterCache('entity', 'default')
        );
        $this->assertEquals('filter', $this->object->getFilterCache('entity')); 
    }
    /**
     * Params cache identity depends on context
     * 
     * @test
     * @return null
     */
    public function paramsCacheIdentityDependsOnContext()
    {
        $expect = $this->object->getCacheId();
        $this->context->with('params');
        $this->assertNotEquals($expect, $this->object->getCacheId());
        $this->context->with('subject')->into('tests');
        $this->assertNotEquals($expect, $this->object->getCacheId());
        $this->context->into('env')->during('testing');
        $this->assertNotEquals($expect, $this->object->getCacheId());
        $this->context->during(atmCoreContext::DEFAULT_MOMENT);
        $this->assertEquals($expect, $this->object->getCacheId());
    }
    /**
     * params manages filter cache results
     * 
     * @test
     * @return null
     */
    public function paramsManageFilterCacheResults()
    {
        $fixture = array('filterResult');
        $this->assertFalse($this->object->hasFilterCache('entity'));
        $this->object->setFilterCache('entity', $fixture);
        $this->assertTrue($this->object->hasFilterCache('entity'));
        $this->assertEquals($fixture, $this->object->getFilterCache('entity'));
    }
    /**
     * filter cache depends on context
     * 
     * @test
     * @return null
     */
    public function filterCacheDependsOnContext()
    {
        $this->object->setFilterCache('entity', array('filterResult'));
        $this->context->with('params')->into('tests')->during('testing');
        $this->assertFalse($this->object->hasFilterCache('entity'));
        
    }
    /**
     * filter cache is cleared when setting params
     * 
     * @test
     * @return null
     */
    public function filterCacheIsClearedWhenSettingParams()
    {
        $this->object->setFilterCache('entity', array('filterResult'));
        $this->object->setFilter('filter');
        $this->assertFalse($this->object->hasFilterCache('entity'));
        
    }
    /**
     * params manages paths cache
     * 
     * @test
     * @return null
     */
    public function paramsManagePathsCache()
    {
        $this->assertFalse($this->object->getCache('entity', false));
        $this->object->setCache('entity', 'path');
        $this->assertEquals('path', $this->object->getCache('entity'), false);
    }
    /**
     * paths cache depends on context
     * 
     * @test
     * @return null
     */
    public function pathsCacheDependsOnContext()
    {
        $this->object->setCache('entity', 'path');
        $this->context->with('params')->into('tests')->during('testing');
        $this->assertFalse($this->object->getCache('entity'));
        
    }
    /**
     * paths cache is cleared when setting params
     * 
     * @test
     * @return null
     */
    public function pathsCacheIsClearedWhenSettingParams()
    {
        $this->object->setCache('entity', 'path');
        $this->object->setBaseDir($this->baseDir);
        $this->assertFalse($this->object->getCache('entity'));
        
    }
    
}
?>
