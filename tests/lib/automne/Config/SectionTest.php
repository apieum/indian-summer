<?php
/**
 * File SectionTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Section.php
 *
 */


/**
 * Test class for ATM_Config_Section.
 * 
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Section
 *
 */
class ATM_Config_SectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Config_Section
     */
    protected $object;

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
        include_once $baseDir.$relDir.'Section.php';
        $this->object = new ATM_Config_Section();
    }
    /**
     * can set an offset
     * 
     * @test
     * @return null
     */
    public function canSetAnOffset()
    {
        $this->object['name'] = 'value';
        $this->assertTrue(isset($this->object['name']));
    }
    /**
     * can set several config options with same name
     * 
     * @test
     * @return null
     */
    public function canSetSeveralConfigOptionsWithSameName()
    {
        $this->object['name'] = 'value0';
        $this->object['name'] = 'value1';
        $this->assertTrue(isset($this->object['name'][0]));
        $this->assertTrue(isset($this->object['name'][1]));
        $this->assertEquals('value0', $this->object['name'][0]->getContent());
        $this->assertEquals('value1', $this->object['name'][1]->getContent());
        
    }
    /**
     * unset all config options with the same name
     * 
     * @test
     * @return null
     */
    public function unsetAllConfigOptionsWithTheSameName()
    {
        $this->object['name'] = 'value0';
        $this->object['name'] = 'value1';
        $this->assertTrue(isset($this->object['name'][0]));
        $this->assertTrue(isset($this->object['name'][1]));
        unset($this->object['name']);
        $this->assertFalse(isset($this->object['name'][0]));
        $this->assertFalse(isset($this->object['name'][1]));
        
    }
    /**
     * when setting unknown Types value become a directive
     * 
     * @test
     * @return null
     */
    public function whenSettingUnknownTypesValueBecomeADirective()
    {
        $this->object['name'] = 'value';
        $this->assertContains('Directive', get_class($this->object['name'][0]));
        $this->object['name1'] = 0;
        $this->assertContains('Directive', get_class($this->object['name1'][0]));
    }
    /**
     * when setting an array value become a section
     * 
     * @test
     * @return null
     */
    public function whenSettingArrayValueBecomeASection()
    {
        $this->object['name'] = array();
        $this->assertContains('Section', get_class($this->object['name'][0]));
    }
    /**
     * when setting an ArrayObject value become a section
     * 
     * @test
     * @return null
     */
    public function whenSettingArrayObjectValueBecomeASection()
    {
        $this->object['name'] = new ArrayObject(array());
        $this->assertContains('Section', get_class($this->object['name'][0]));
    }
    /**
     * when setting a Config Object value become a section
     * 
     * @test
     * @return null
     */
    public function whenSettingConfigObjectValueStayAsIs()
    {
        $this->object['name'] = new ATM_Config_Comment('comment');
        $this->assertContains('Comment', get_class($this->object['name'][0]));
    }
    /**
     * can set an offset
     * 
     * @test
     * @return null
     */
    public function canUnsetAnOffset()
    {
        $this->object['name'] = 'value';
        $this->assertTrue(isset($this->object['name']));
        unset($this->object['name']);
        $this->assertFalse(isset($this->object['name']));
    }
    /**
     * can iterate over object
     * 
     * @test
     * @return null
     */
    public function canIterateOverObject()
    {
        $expect=array();
        for ($index=0;$index<5;$index++) {
            $name  = 'name'.$index;
            $value = 'value'.$index;
            $this->object[$name] = $value;
            $expect[$index]      = $value;
        }
        foreach ($this->object as $position=>$value) {
            $this->assertTrue(isset($expect[$position]));
            $this->assertEquals($expect[$position], $value->getContent());
        }
        $this->assertEquals(4, $position);
    }
}
?>
