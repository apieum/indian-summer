<?php

/**
 * File CommentTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Confing_Comment.php
 *
 */
$dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
$relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
$baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);

require_once $baseDir.$relDir.'Comment.php';

/**
 * Test class for ATM_Confing_Comment.
 * 
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Confing_Comment
 *
 */
class ATM_Confing_CommentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Confing_Comment
     */
    protected $object;

    /**
     * Sets up fixtures.
     * 
     * @return null
     */
    protected function setUp()
    {
        $this->object = new ATM_Confing_Comment('');
    }

    /**
     * test if we can split a multilines string into an array of lines
     * 
     * @test
     * @return null
     */
    public function canSplitMultilinesStringIntoArrayOfLines()
    {
        $oneLine   = ' line 1 ';
        $this->assertEquals(array($oneLine), $this->object->splitLines($oneLine));
        $multiLine = "line 1 \n line 2 \n\n line 4";
        $expect = array('line 1 ',' line 2 ', '', ' line 4');
        $this->assertEquals($expect, $this->object->splitLines($multiLine));
        $this->object->setDelimiters('<p>', '</p>');
        $multiLine = "<p>line 1 </p><p> line 2 </p><p></p><p> line 4</p>";
        $this->assertEquals($expect, $this->object->splitLines($multiLine));
        $this->object->setDelimiters('@', '@\n');
        $multiLine = "@line 1 @
@ line 2 @
@@
@ line 4@
";
        $this->assertEquals($expect, $this->object->splitLines($multiLine));
    }
    /**
     * can add new lines
     * 
     * @test
     * @return null
     */
    public function canAddNewLines()
    {
        $expect = array('', 'line 1 ');
        $this->object->newLine('line 1 ');
        $this->assertAttributeEquals($expect, 'lines', $this->object);
        $expect = array('', 'line 1 ',' line 2 ', '', ' line 4');
        $this->object->newLine(" line 2 \n\n line 4");
        $this->assertAttributeEquals($expect, 'lines', $this->object);
    }
    /**
     * can edit an existing line
     * 
     * @test
     * @return null
     */
    public function canEditAnExistingLine()
    {
        $this->object->editLine(0, "line 1 ");
        $this->assertAttributeEquals(array("line 1 "), 'lines', $this->object);
        $expect = array('line 1 ',' line 2 ', '', ' line 4');
        $this->object->editLine(0, "line 1 \n line 2 \n\n line 4");
        $this->assertAttributeEquals($expect, 'lines', $this->object);
        
    }
    /**
     * can edit a line that not exists
     * 
     * @test
     * @return null
     */
    public function canEditALineThatNotExists()
    {
        $this->object->editLine(2, "line 1 ");
        $this->assertAttributeEquals(array('','',"line 1 "), 'lines', $this->object);
        $expect = array('line 1 ',' line 2 ', '', ' line 4', '', 'line 1 ');
        $this->object->editLine(0, "line 1 \n line 2 \n\n line 4");
        $this->assertAttributeEquals($expect, 'lines', $this->object);
        
    }
    /**
     * can edit an existing line with getter
     * 
     * @test
     * @return null
     */
    public function canEditAnExistingLineWithGetter()
    {
        $line =& $this->object->getLine(0);
        $line = 'line 1 ';
        $this->assertAttributeEquals(array("line 1 "), 'lines', $this->object);
        
    }
    /**
     * can set a line if not exists with getter
     * 
     * @test
     * @return null
     */
    public function canSetALineIfNotExistsWithGetter()
    {
        $line =& $this->object->getLine(0, 'line 1 ');
        $line = $line;
        $this->assertAttributeEquals(array(""), 'lines', $this->object);
        $this->object->editLine(0, $this->object->getLine(0, 'line 1 '));
        $this->assertAttributeEquals(array(""), 'lines', $this->object);
        $this->object->editLine(1, $this->object->getLine(1, 'line 1 '));
        $this->assertAttributeEquals(array("", 'line 1 '), 'lines', $this->object);
        
    }
    /**
     * can set multiples lines with getter
     * 
     * @test
     * @return null
     */
    public function canSetMultiplesLinesWithGetter()
    {
        $this->assertSame(array(""), $this->object->getLines());
        $lines =& $this->object->getLines();
        $lines = array('line 1 ', ' line 2 ');
        $this->assertAttributeEquals($lines, 'lines', $this->object);
    }
    
    /**
     * can replace a line
     * 
     * @test
     * @return null
     */
    public function canReplaceALine()
    {
        $this->assertSame(array(""), $this->object->getLines());
        $this->object->replaceLine('line 1 ');
        $lines =& $this->object->getLines();
        $this->assertEquals(array('line 1 '), $lines);
        $lines = array('line 1 ',' line 2 ', '', ' line 4');
        $this->object->replaceLine('line 3', 3);
        $this->assertSame('line 3', $this->object->getLine(3));
        $this->assertNotSame('line 4', $this->object->getLine(4));
        $this->object->replaceLine('line 4', 4);
        $this->assertSame('line 4', $this->object->getLine(4));
        $this->object->replaceLine("\n line 4", 3);
        $this->assertAttributeEquals($lines, 'lines', $this->object);
    }
    /**
     * can replace lines
     * 
     * @test
     * @return null
     */
    public function canReplaceLines()
    {
        $this->assertSame(array(""), $this->object->getLines());
        $lines = array('line 1 ',' line 2 ', '', ' line 4');
        $this->object->replaceLines($lines);
        $this->assertSame($lines, $this->object->getLines());
        $this->object->replaceLines(array('line 2', 'line 3'), 1);
        $lines[1]='line 2';
        $lines[2]='line 3';
        $this->assertSame($lines, $this->object->getLines());
    }
    /**
     * can insert a line
     * 
     * @test
     * @return null
     */
    public function canInsertALine()
    {
        $this->assertSame(array(""), $this->object->getLines());
        $this->object->insertLine('line 1 ', 0);
        $this->assertEquals(array('line 1 ', ''), $this->object->getLines());
        $lines = array('line 1 ',' line 2 ', '', ' line 4');
        $this->object->insertLine(' line 2 ', 1);
        $this->assertSame(' line 2 ', $this->object->getLine(1));
        $this->object->insertLine(' line 4', 3);
        $this->assertSame(' line 4', $this->object->getLine(3));
        $this->assertAttributeEquals($lines, 'lines', $this->object);
    }
    /**
     * can replace lines
     * 
     * @test
     * @return null
     */
    public function canInsertLines()
    {
        $this->assertSame(array(""), $this->object->getLines());
        $lines = array('line 1 ',' line 2 ', '', ' line 4');
        $this->object->insertLines(array('line 1 ',' line 2 '));
        $this->object->insertLines(array(' line 4'), 3);
        $this->assertSame($lines, $this->object->getLines());
        $this->object->spliceLines(array(''));
        $this->assertSame(array(""), $this->object->getLines());
        $this->object->insertLine("line 1 \n line 2 ");
        $this->object->insertLine(" line 4", 3);
        $this->assertSame($lines, $this->object->getLines());
    }
}
?>
