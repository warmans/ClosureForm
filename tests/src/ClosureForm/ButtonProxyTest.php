<?php
namespace ClosureForm\Test;

class ButtonProxyTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {

    }

    protected function _getMockForm()
    {
        return $this->getMock('\ClosureForm\Form', array('setFieldAttributes'));
    }

    protected function _getTestButton()
    {
        return new \ClosureForm\ButtonProxy($this->_getMockForm(), 'test-button');
    }

    /**
     * @group current
     */
    public function testSetAndGetLabel()
    {
        $button = $this->_getTestButton();
        $this->assertEquals('my label', $button->label('my label')->getLabel());
    }

    /**
     * @group current
     */
    public function testSetTemplate()
    {
        $button = $this->_getTestButton();
        $button->template(function($button){ return 'foo bar'; });

        $this->assertContains('foo bar', $button->render());
    }

    /**
     * @group current
     * @expectedException RuntimeException
     */
    public function testNoActionError()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder('\ClosureForm\Form')
                     ->disableOriginalConstructor()
                     ->getMock();

        // Configure the stub.
        $stub->expects($this->any())
             ->method('isSubmitted')
             ->will($this->returnValue(TRUE));

        $button = new \ClosureForm\ButtonProxy($stub, 'test-button');
        $button->trigger();
    }

    /**
     * @group current
     */
    public function testActionSetButFormNotSubmitted()
    {
        // Create a stub for the SomeClass class.
        $stub = $this->getMockBuilder('\ClosureForm\Form')
                     ->disableOriginalConstructor()
                     ->getMock();

        // Configure the stub.
        $stub->expects($this->any())
             ->method('isSubmitted')
             ->will($this->returnValue(FALSE));

        $callCount = 0;

        $button = new \ClosureForm\ButtonProxy($stub, 'test-button');
        $button->action(function($button) use (&$callCount) {
            $callCount++;
        });

        $result = $button->trigger();

        $this->assertFalse($result);
        $this->assertEquals(0, $callCount);
    }

}