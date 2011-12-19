<?php

/**
 * File atmContextTests.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmContext.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'ContextTemplate.php';

/**
 * Test class for atmContextTemplate.
 * 
 * @category AutomneTests
 * @package  Tests/Context
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     atmContextTest
 *
 */
class atmContextTemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var atmContextTemplate
     */
    protected $object;

    /**
     * Sets up the fixture
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new atmContextTemplate('context', 'getter');
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
