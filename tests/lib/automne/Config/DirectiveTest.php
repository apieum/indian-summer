<?php

/**
 * File DirectiveTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Directive.php
 *
 */


/**
 * Test class for ATM_Config_Directive.
 * 
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Directive
 *
 */
class ATM_Config_DirectiveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Config_Directive
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
        include_once $baseDir.$relDir.'Directive.php';
        $this->object = new ATM_Config_Directive('test', 'directive', 'comment');
    }

    /**
     * a directive is defined by a name
     * 
     * @return @test
     */
    public function aDirectiveIsDefinedByAName()
    {
        $this->assertEquals('test', $this->object->getName());
        $this->object->setName('name');
        $this->assertEquals('name', $this->object->getName());
    }

    /**
     * a directive is defined by a value
     * 
     * @return @test
     */
    public function aDirectiveIsDefinedByAValue()
    {
        $this->assertEquals('directive', $this->object->getContent());
        $this->object->setContent('value');
        $this->assertEquals('value', $this->object->getContent());
    }

    /**
     * a directive can be commented
     * 
     * @return @test
     */
    public function aDirectiveCanBeCommented()
    {
        $expect = new ATM_Config_Comment('comment');
        $comment=$this->object->getComment();
        $this->assertEquals($expect, $comment);
        $expect->newLine('new line in comment');
        $this->object->annotate('new line in comment');
        $this->assertEquals($expect, $this->object->getComment());
        $expect = new ATM_Config_Comment('comment');
        $this->object->setComment($expect);
        $this->assertSame($expect, $this->object->getComment());
        $this->assertNotSame($expect, $comment);
    }
    
    /**
     * an object with method update can start ans top observing directives
     * 
     * @return @test
     */
    public function anObjectWithMethodUpdateCanStartAndStopObservingDirectives()
    {
        $object = new stdClass();
        $this->object->bind($object);
        $this->assertAttributeEquals(array(), 'observers', $this->object);
        $object = &$this->getMock('ATM_Config_Glue_Abstract', array('update'));
        $object->expects($this->at(0))
            ->method('update')
            ->with(
                $this->equalTo('bindObjects'),
                $this->equalTo($this->object),
                $this->equalTo($object)
            );
        $object->expects($this->at(1))
            ->method('update')
            ->with(
                $this->equalTo('unbindObjects'),
                $this->equalTo($this->object),
                $this->equalTo($object)
            );
        $this->object->bind($object);
        $expect = array(spl_object_hash($object)=>$object);
        $this->assertAttributeEquals($expect, 'observers', $this->object);
        $this->object->unbind($object);
        $this->assertAttributeEquals(array(), 'observers', $this->object);
    }
    /**
     * set append string
     * 
     * @return @test
     */
    public function setAppendString()
    {
        $this->assertAttributeEquals('', 'appendMethod', $this->object);
        $this->object->appendToMethod('string');
        $this->assertAttributeEquals('string', 'appendMethod', $this->object);
    }
    
}
?>
