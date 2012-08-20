<?php
namespace ClosureForm\Test;

class FieldProxyTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {

    }

    protected function _getMockForm()
    {
        return $this->getMock('\ClosureForm\Form', array('setFieldAttributes'));
    }

    /**
     * @group current
     */
    public function testSetRenderer()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $fieldProxy->template(function($field){
            return '<input type="text" name="'.$field->getName().'" />';
        });
        $this->assertEquals('<input type="text" name="test-field" />', $fieldProxy->render());
    }

    /**
     * @group current
     */
    public function testSetAttributes()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $fieldProxy->attributes(array('id'=>'some-id'));
        $this->assertEquals('id="some-id"', $fieldProxy->getAttributeString());
    }

    /**
     * @group current
     */
    public function testTextFieldValidatorFails()
    {
        $_POST['test-field'] = 'submitted value';
        $_POST['_internal_generic-form'] = 1;

        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $fieldProxy->validator(function($value){
            return $value;
        });

        ;
        $this->assertFalse($fieldProxy->isValid());
        $this->assertContains($_POST['test-field'], $fieldProxy->getErrors());
    }
    /**
     * @group current
     */
    public function testTextFieldValidatorSuccess()
    {
        $_POST['test-field'] = 'submitted value';
        $_POST['_internal_generic-form'] = 1;

        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $fieldProxy->validator(function($value){
            return null;
        });

        $this->assertTrue($fieldProxy->isValid());
        $this->assertTrue(count($fieldProxy->getErrors()) == 0);
    }

    /**
     * @group current
     */
    public function testValidateNonSubmittedField()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $fieldProxy->validator(function($value){
            return 'Error!';
        });

        $this->assertTrue($fieldProxy->isValid());
        $this->assertTrue(count($fieldProxy->getErrors()) == 0);
    }

    /**
     * @group current
     */
    public function testGetSubmittedValueFromNonSubmittedForm()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $this->assertNull($fieldProxy->getSubmittedValue());
    }

    /**
     * @group current
     */
    public function testSetThenGetLabel()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');
        $this->assertEquals('my label', $fieldProxy->label('my label')->getLabel());
    }

    /**
     * @group current
     */
    public function testPreRenderActionIsExecuted()
    {
        $fieldProxy = new \ClosureForm\FieldProxy($this->_getMockForm(), 'test-field');

        $fieldName = NULL;
        $fieldProxy->preRender(function($field) use (&$fieldName){
            $fieldName = $field->getName();
        });

        $fieldProxy->render();

        $this->assertEquals('test-field', $fieldName);
    }

}