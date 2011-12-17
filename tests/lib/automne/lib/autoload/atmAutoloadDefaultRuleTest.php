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
$fname=str_replace('Test', '', basename(__FILE__));
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

define('RULE_DIR', $baseDir.$relDir);

require_once $baseDir.$relDir.$fname;
$cContext = implode(DIRECTORY_SEPARATOR, array('..', 'core', 'atmCoreContext.php'));
require_once $baseDir.$relDir.$cContext;
/**
 * Test class for atmAutoloadDefaultRule.
 * 
 * @category AutomneTests
 * @package  Autoload
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmAutoloadRuleParamsTest
 *
 */
class atmAutoloadDefaultRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmAutoloadDefaultRule
     */
    protected $object;
    protected $context;
    protected $params=array(RULE_DIR, '@^atm[A-Z].*@', 'lib');
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
        $this->context= new atmCoreContext('defaut rule', 'tests');
        $this->object = new atmAutoloadDefaultRule($this->context, $this->params);
    }


    /**
     * default rule has params object
     * 
     * @test
     * @return null
     */
    public function defaultRuleHasParamsObject()
    {
        $params = $this->object->getParams();
        $this->assertTrue($params instanceof atmAutoloadRuleParams);
    }

    /**
     * know an entity if it path is cached
     * 
     * @test
     * @return null
     */
    public function knowAnEntityIfItPathIsCached()
    {
        $this->assertFalse($this->object->know('entity'));
        $params =& $this->object->getParams();
        $params->setCache('entity', RULE_DIR);
        $this->assertTrue($this->object->know('entity'));
    }

    /**
     * know an entity if filter result is cached and not empty
     * 
     * @test
     * @return null
     */
    public function knowAnEntityIfFilterResultIsCachedAndNotEmpty()
    {
        $this->assertFalse($this->object->know('entity'));
        $params =& $this->object->getParams();
        $params->setFilterCache('entity', array('result'));
        $this->assertTrue($this->object->know('entity'));
        $params->setFilterCache('entity', array());
        $this->assertFalse($this->object->know('entity'));
    }
    /**
     * know an entity if filter result not cached and not empty
     * 
     * @test
     * @return null
     */
    public function knowAnEntityIfFilterResultNotCachedAndNotEmpty()
    {
        $params =& $this->object->getParams();
        $this->assertFalse($params->getFilterCache('atmTest', false));
        $this->assertTrue(count($this->object->applyFilter('atmTest')) > 0);
        $this->assertTrue($this->object->know('atmTest'));
    }
    /**
     * filter result is cached if it and entity was not when calling know
     * 
     * @test
     * @return null
     */
    public function filterResultIsCachedIfItAndEntityWasNotWhenCallingKnow()
    {
        $params =& $this->object->getParams();
        $this->assertFalse($params->getFilterCache('Test', false));
        $this->assertFalse($params->getFilterCache('atmTest', false));
        $this->object->know('Test');
        $this->object->know('atmTest');
        $this->assertEquals(array(), $params->getFilterCache('Test', false));
        $this->assertEquals(
            array('atmTest'), 
            $params->getFilterCache('atmTest', false)
        );
    }
    /**
     * when filter an entity name result is cached
     * 
     * @test
     * @return null
     */
    public function whenFilterAnEntityNameResultIsCached()
    {
        $params =& $this->object->getParams();
        $expect = $this->object->applyFilter('atmTest');
        $this->assertNotEquals($expect, $params->getFilterCache('atmTest', false));
        $this->assertEquals($expect, $this->object->filter('atmTest'));
        $this->assertEquals($expect, $params->getFilterCache('atmTest', false));
    }
    /**
     * By default a rule can't find where is an entity file if not in cache
     * 
     * @test
     * @return null
     */
    public function byDefaultARuleCantFindWhereIsAnEntityFileIfNotInCache()
    {
        $this->assertFalse($this->object->whereIs('atmTest'));
        $this->assertFalse($this->object->cacheWhereIs('atmTest'));
    }
    /**
     * A rule can find where is an entity file if cached
     * 
     * @test
     * @return null
     */
    public function aRuleCanFindWhereIsAnEntityFileIfCached()
    {
        $params =& $this->object->getParams();
        $this->assertFalse($this->object->whereIs('atmTest'));
        $params->setCache('atmTest', true);
        $this->assertTrue($this->object->cacheWhereIs('atmTest'));
    }
    /**
     * when trying to load an entity results are cached
     * 
     * @test
     * @return null
     */
    public function whenTryingToLoadAnEntityResultsAreCached()
    {
        $params =& $this->object->getParams();
        $this->object->load('entity');
        $this->assertEquals(array(), $params->getFilterCache('entity', false));
        // don't ask where is as 'know' is false 
        $this->assertTrue($params->getCache('entity', true));
        $this->object->load('atmTest');
        $this->assertEquals(array('atmTest'), $params->getFilterCache('atmTest'));
        $this->assertEquals(false, $params->getCache('atmTest', true));
        
    }
    /**
     * return false if fail and true if succeed to load an entity 
     * 
     * @test
     * @return null
     */
    public function returnFalseIfFailAndTrueIfSucceedToLoadAnEntity()
    {
        $entity = 'testAutoloadDefaultRule';
        $entityFile = __DIR__.DIRECTORY_SEPARATOR.$entity.'.php';
        $this->assertTrue(file_exists($entityFile));
        $this->assertFalse($this->object->load($entity));
        $this->assertFalse(class_exists($entity));
        $this->object
            ->getParams()
            ->setCache($entity, $entityFile);
        $this->assertTrue($this->object->load($entity));
        $this->assertTrue(class_exists($entity));
    }

}
?>
