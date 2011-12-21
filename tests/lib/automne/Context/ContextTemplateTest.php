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
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'ContextTemplate.php';

/**
 * Test class for ATM_Context_Template.
 * 
 * @category AutomneTests
 * @package  Tests/Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_ContextTest
 *
 */
class ATM_Context_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Context_Template
     */
    protected $object;

    /**
     * Sets up the fixture
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new ATM_Context_Template('context', 'getter');
    }

    /**
     * test markOff
     * 
     * @test
     * @return null
     */
    public function testMarkOff()
    {
        $this->assertEquals('\{string\}', $this->object->markOff('string'));
    }

    /**
     * test sanitize
     * 
     * @test
     * @return null
     */
    public function testSanitize()
    {
        $this->assertEquals('{string}', $this->object->sanitize('\{string\}'));
    }
}
?>
