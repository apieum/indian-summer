<?php
/**
 * File CollectionTest.php
 *
 * PHP version 5.2
 *
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection.php
 *
 */


/**
 * Test class for ATM_Config_Collection.
 * 
 * @category AutomneTests
 * @package  Tests/Config
 * @author   Gregory Salvan <gregory.salvan@apieum.com>
 * @license  GPL v.2
 * @link     ATM_Config_Collection
 *
 */
class ATM_Config_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ATM_Config_Collection
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * 
     * @return null
     */
    protected function setUp()
    {
        $dirs=explode('tests'.DIRECTORY_SEPARATOR, __DIR__);
        $relDir=array_pop($dirs).DIRECTORY_SEPARATOR;
        $baseDir=implode('tests'.DIRECTORY_SEPARATOR, $dirs);
        include_once $baseDir.$relDir.'Collection.php';
        include_once $baseDir.$relDir.'Comment.php';
        $this->object = new ATM_Config_Collection();
    }

    /** 
     * Throw Exception when setting a value that's not instance of $objectsType
     * 
     * @return @test
     */
    public function throwExceptionWhenSettingValueThatsNotInstanceOfObjectsType()
    {
        ATM_Config_Collection::$objectsType='stdClass';
        $object = $this->getMock('ATM_Config_Abstract', array('getName'));
        $expect = false;
        try {
            $this->object[]=$object;
        } catch (InvalidArgumentException $exception) {
            $this->assertContains('stdClass', $exception->getMessage());
            $expect = true;
        }
        $this->assertTrue($expect);
        $this->assertFalse(isset($this->object['name']));
        ATM_Config_Collection::$objectsType='ATM_Config_Abstract';
        try {
            $this->object[]=$object;
        } catch (InvalidArgumentException $exception) {
            $this->assertContains('ATM_Config_Abstract', $exception->getMessage());
            $expect = false;
        }
        $this->assertTrue($expect);
        $this->assertTrue(isset($this->object[0]));
    }


    /**
     * Can set the same offset several times
     * 
     * @return @test
     */
    public function canSetTheSameOffsetSeveralTimes()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $this->assertNotSame($object0, $object1);
        $this->object[0]=$object0;
        $this->assertSame($object0, $this->object[0]);
        $this->object[0]=$object1;
        $this->assertSame($object1, $this->object[0]);
    }
    /**
     * Can append an object and unset an offset
     * 
     * @return @test
     */
    public function canAppendAnObjectAndUnsetAnOffset()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $this->object->append($object0);
        $this->assertTrue(isset($this->object[0]));
        unset($this->object[0]);
        $this->assertFalse(isset($this->object[0]));
    }

    /**
     * can unset offsets with a value
     * 
     * @return @test
     */
    public function canUnsetOffsetsWithAValue()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $expect = array($object0, $object1, $object0);
        $this->assertAttributeEquals($expect, 'content', $this->object);
        $this->object->searchAndUnset($object0);
        $this->assertAttributeEquals(array($object1), 'content', $this->object);
        $this->assertAttributeEquals(
            array(get_class($object1)), 'classes', $this->object
        );
        $this->assertAttributeEquals(
            array($object1->getName()), 'names', $this->object
        );
    }

    /**
     * can replace a value by another
     * 
     * @return @test
     */
    public function canReplaceAValueByAnother()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $expect = array($object0, $object1, $object0);
        $this->assertAttributeEquals($expect, 'content', $this->object);
        $this->object->searchAndReplace($object0, $object1);
        $expect = array($object1, $object1, $object1);
        $this->assertAttributeEquals($expect, 'content', $this->object);
    }
    
    /**
     * can return a binded collection for a given class
     * 
     * @return @test
     */
    public function canReturnABindedCollectionForAGivenClass()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->assertNotEquals(get_class($object0), get_class($object1));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $collection = $this->object->filterClasses(get_class($object0));
        $this->assertSame($object0, $collection[0]);
        $this->assertSame($object0, $collection[1]);
        $this->assertFalse(isset($collection[2]));
        // set all $object0 to $object1 in this object
        $collection[0]=$object1;
        $this->assertSame($object1, $collection[0]);
        $this->assertSame($object1, $this->object[0]);
        $this->assertSame($object1, $this->object[1]);
        $this->assertSame($object0, $this->object[2]);
        $this->object[0]=$object0;
        $this->assertSame($object1, $collection[0]);
    }
    /**
     * can return a binded collection for a given name
     * 
     * @return @test
     */
    public function canReturnABindedCollectionForAGivenName()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = clone $object0;
        $object0->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('object0'));
        $object1->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('object1'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $collection = $this->object->filterNames('object0');
        $this->assertSame($object0, $collection[0]);
        $this->assertSame($object0, $collection[1]);
        $this->assertFalse(isset($collection[2]));
        // set all $object0 to $object1 in this object
        $collection[0]=$object1;
        $this->assertSame($object1, $collection[0]);
        $this->assertSame($object1, $this->object[0]);
        $this->assertSame($object1, $this->object[1]);
        $this->assertSame($object0, $this->object[2]);
        $this->object[0]=$object0;
        $this->assertSame($object1, $collection[0]);
    }
    /**
     * can append a value to observers
     * 
     * @return @test
     */
    public function canAppendAValueToObservers()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->object->append($object0);
        $this->object->append($object1);
        $collection = $this->object->filterClasses(get_class($object0));
        $collection->append($object0, true);
        $this->assertEquals(2, count($collection));
        $this->assertEquals(3, count($this->object));
        $this->assertSame($object0, $this->object[2]);
    }
    /**
     * can have a copy of properties
     * 
     * @return @test
     */
    public function canHaveACopyOfProperties()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $properties = array('content', 'objIds', 'names', 'classes');
        foreach ($properties as $property) {
            $this->object->iterateOn($property);
            $expect = $this->object->getArrayCopy();
            $this->assertAttributeEquals($expect, $property, $this->object);
        }
    }
    /**
     * can Iterate over properties
     * 
     * @return @test
     */
    public function canIterateOverProperties()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $this->object->iterateOn('objIds');
        $expect = $this->object->getArrayCopy();
        foreach ($this->object as $position=>$objectId) {
            $this->assertInternalType('string', $objectId);
            $this->assertEquals($expect[$position], $objectId);
        }
        $this->assertEquals(2, $position);
    }
    /**
     * cannot Iterate over not set properties
     * 
     * @return @test
     */
    public function iteratorSubjectMustBeAnExistingPropertyAndDefaultIsContent()
    {
        $this->assertAttributeEquals('content', 'itSubject', $this->object);
        $this->object->iterateOn('notset');
        $this->assertAttributeEquals('content', 'itSubject', $this->object);
        $this->object->iterateOn('objIds');
        $this->assertAttributeEquals('objIds', 'itSubject', $this->object);
    }
    /**
     * collections are seekableIterator
     * 
     * @return @test
     */
    public function collectionsAreSeekableIterator()
    {
        $this->assertInstanceOf('SeekableIterator', $this->object);
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->object->append($object0);
        $this->object->append($object1);
        $this->object->append($object0);
        $this->object->rewind();
        $this->assertEquals($object0, $this->object->current());
        $this->assertEquals($object1, $this->object->next());
        $this->assertEquals($object1, $this->object->current());
        $this->object->seek(0);
        $this->assertEquals($object0, $this->object->current());
        $this->object->seek(1);
        $this->assertEquals($object1, $this->object->current());
        try {
            $this->object->seek(10);
        } catch(OutOfBoundsException $e) {
            $this->assertContains('position 10', $e->getMessage());
        }
        $this->assertTrue(isset($e));
    }
    /**
     * cannot search offsets on not existing property
     * 
     * @return @test
     */
    public function cannotSearchOffsetsOnNotExistingProperty()
    {
        try {
            $this->object->searchOffsets('offset', 'property that not exists');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('property that not exists', $e->getMessage());
        }
        $this->assertTrue(isset($e));
    }
    /**
     * can merge two collections
     * 
     * @return @test
     */
    public function canMergeTwoCollection()
    {
        $object0 = $this->getMock('ATM_Config_Abstract', array('getName'));
        $object1 = $this->getMock('ATM_Config_Comment', array(), array('comment'));
        $this->object->append($object0);
        $collection = new ATM_Config_Collection(array($object1, $object0));
        $this->object->merge($collection);
        $this->assertEquals($object0, $this->object[0]);
        $this->assertEquals($object1, $this->object[1]);
        $this->assertEquals($object0, $this->object[2]);
    }
}
?>
