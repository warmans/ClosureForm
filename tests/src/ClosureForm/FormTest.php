<?php
namespace ClosureForm\Test;

class FormTest extends \PHPUnit_Framework_TestCase {

    private $_form;

    public function setUp()
    {
        $this->_form = new \ClosureForm\Form('test-form', array('method'=>'post'));
    }


    private function _fakeFormSubmit($form)
    {
        $method = $form->getAttribute('method');
        if($method == 'post')
        {
            $_POST['_internal_'.$form->getName()] = 1;
        }
        else {
            $_GET['_internal_'.$form->getName()] = 1;
        }

        foreach($form->getFields() as $field)
        {
            if($method == 'post')
            {
                $_POST[$field->getName()] = $field->getAttribute('value');
            } else {
                $_GET[$field->getName()] = $field->getAttribute('value');
            }
        }
    }

    /**
     * @group current
     */
    public function testTextFieldReturnsProxy()
    {
        $this->assertTrue($this->_form->addTextField('text-field-1') instanceof \ClosureForm\Element\FieldProxy);
    }

   /**
    * @group current
    */
    public function testTextFieldRenderer()
    {
        $this->_form->addTextField('text-field-1');
        $this->assertContains('<input type="text" id="text-field-1" name="text-field-1" />', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testTextFieldRendererWithError()
    {
        $this->_form->addTextField('text-field-1')->validator(function($field){ return 'ERROR!'; });
        $this->_fakeFormSubmit($this->_form);
        $this->_form->isValid();
        $this->assertContains('<div class="error-msg"', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testMultipleValidatorErrors()
    {
        $this->_form->addTextField('text-field-1')
            ->validator(function($field){ return 'ERROR!'; })
            ->validator(function($field){ return 'FOOBARBAZ'; });

        $this->_fakeFormSubmit($this->_form);
        $this->_form->isValid();

        $output = $this->_form->render();

        $this->assertContains('ERROR!', $output);
        $this->assertContains('FOOBARBAZ', $output);

    }


   /**
     * @group current
     * @expectedException RuntimeException
     */
    public function testAddFieldWithBlankName()
    {
        $this->_form->addTextField('');
    }

    /**
     * @group current
     */
    public function testGetFieldReturnsProxy()
    {
        $this->_form->addTextField('text-field-1');
        $this->assertTrue($this->_form->getField('text-field-1') instanceof \ClosureForm\Element\FieldProxy);
    }

    /**
     * @group current
     * @expectedException RuntimeException
     */
    public function testGetUnknownFieldThrowsException()
    {
        $this->_form->getField('unknown');
    }

    /**
     * @group current
     */
    public function testGetFields()
    {
        $this->_form->addTextField('test-field');
        $fields = $this->_form->getFields();

        $this->assertTrue(is_array($fields));
        $this->assertTrue($fields['test-field'] instanceof \ClosureForm\Element\FieldProxy);
    }

    /**
     * @group current
     */
    public function testAttributes()
    {
        $attributes = array('id'=>'aform', 'class'=>'the-form');

        $form = new \ClosureForm\Form('test-form', $attributes);
        $this->assertEquals('id="aform" class="the-form"', $this->_form->getAttributeString($attributes));
    }

    /**
     * @group current
     */
    public function testRenderForm()
    {
        $this->_form->addTextField('text-field-1');
        $this->assertContains('<form name="test-form" method="post">', $this->_form->render());
    }

    /**
     * @group current
     */
    public function testIsSubmitted()
    {
        $this->_fakeFormSubmit($this->_form);
        $this->assertTrue($this->_form->isSubmitted());
    }

    /**
     * @group current
     */
    public function testIsSubmittedAsGet()
    {
        $form = new \ClosureForm\Form('get-form', array('method'=>'get'));
        $this->_fakeFormSubmit($form);
        $this->assertTrue($form->isSubmitted());
    }

    /**
     * @group current
     */
    public function testIsNotSubmitted()
    {
        $this->assertFalse($this->_form->isSubmitted());
    }

    /**
     * @group current
     */
    public function testGetSubmittedValueFromNonSubmittedForm()
    {
        $this->_form->addTextField('text-field-1');
        $this->assertNull($this->_form->getField('text-field-1')->getSubmittedValue());
    }

    /**
     * @group current
     * @expectedException RuntimeException
     */
    public function testGetGlobalFromFormWithNoMethodAttr()
    {
        $form = new \ClosureForm\Form('invalid-form', array());
        $form->getSuperglobal();
    }

    /**
     * @group current
     * @expectedException RuntimeException
     */
    public function testGetGlobalFromFormWithInvalidMethodAttr()
    {
        $form = new \ClosureForm\Form('invalid-form', array('method'=>'snail-mail'));
        $form->getSuperglobal();
    }

    /**
    * @group current
    */
    public function testPasswordField()
    {
        $this->_form->addPasswordField('pass');
        $this->assertContains('<input type="password" id="pass" name="pass" />', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testTextAreaField()
    {
        $this->_form->addTextareaField('ta')->attributes(array('value'=>'foo'));
        $this->assertContains('<textarea id="ta" name="ta" >foo</textarea>', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testCheckboxFieldFormNotSubmitted()
    {
        $field = $this->_form->addCheckboxField('c')->attributes(array('value'=>'foo'));
        $this->assertNotContains('checked="checked"', $field->render());
    }

    /**
    * @group current
    */
    public function testCheckboxFieldFormSubmittedButNotField()
    {
        $field = $this->_form->addCheckboxField('c')->attributes(array('value'=>'foo'));
        $this->_fakeFormSubmit($this->_form);
        unset($_POST[$field->getName()]);
        $this->assertNotContains('checked="checked"', $field->render());
    }

    /**
    * @group current
    */
    public function testCheckboxFieldSubmitted()
    {
        $field = $this->_form->addCheckboxField('c')->attributes(array('value'=>'foo'));
        $this->_fakeFormSubmit($this->_form);
        $this->assertContains('checked="checked"', $field->render());
    }

    /**
    * @group current
    */
    public function testFormIsMakedInvalidWithoutCallingIsValid()
    {
        $field = $this->_form->addTextField('t')->validator(function($value){
            return 'error!';
        });

        $this->_fakeFormSubmit($this->_form);

        $this->assertContains('error!', $this->_form->render());
    }

   /**
    * @group current
    */
    public function testMultipleIsValidCallsReturnsCachedResult()
    {
        $called = 0;
        $validator = function($value) use (&$called){
            $called++;
            return true;
        };

        $this->_form->addTextField('t')->validator($validator);
        $this->_fakeFormSubmit($this->_form);

        $this->_form->isValid();
        $this->_form->isValid();
        $this->_form->isValid();

        $this->assertEquals(1, $called);
    }

    /**
    * @group current
    */
    public function testSelectBoxRenderer()
    {
        $field = $this->_form->addSelectField('sel', array('yes'=>'Yes', 'no'=>'No'));
        $output = $field->render();
        $this->assertContains('<select id="sel" name="sel" >', $output);
        $this->assertContains('<option value="yes" >Yes</option>', $output);
    }

    /**
    * @group current
    */
    public function testSelectBoxSelectedAttribute()
    {
        $field = $this->_form->addSelectField('sel', array('yes'=>'Yes', 'no'=>'No'))->attributes(array('value'=>'no'));
        $this->_fakeFormSubmit($this->_form);
        $this->assertContains('<option value="no" selected="selected">No</option>', $field->render());
    }


    /**
    * @group current
    */
    public function testAddExternalError()
    {
        $field = $this->_form->addTextField('tb');
        $this->_form->addError('External Error', 'tb');
        $this->_fakeFormSubmit($this->_form);
        $this->assertContains('External Error', $field->render());
    }

    /**
    * @group current
    */
    public function testAddExternalErrorToField()
    {
        $field = $this->_form->addTextField('tb');
        $this->_form->addError('General External Error');
        $this->_fakeFormSubmit($this->_form);
        $this->assertContains('<div class="general-error">General External Error</div>', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testAddExternalErrorToFieldIfFieldNotFound()
    {

        $this->_form->addError('External Error For Unknown Field', 'foobarbaz');
        $this->_fakeFormSubmit($this->_form);
        $this->assertContains('<div class="general-error">External Error For Unknown Field</div>', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testAddButtonRendersButtonToForm()
    {
        $field = $this->_form->addButton('submit');
        $this->assertContains('<button name="submit" ></button>', $this->_form->render());
    }

    /**
    * @group current
    */
    public function testTriggerButtonAction()
    {
        $triggered = 0;

        $this->_form->addButton('submit')->action(function($form) use (&$triggered){
            $triggered++;
        });

        $this->_fakeFormSubmit($this->_form);
        $_POST['submit'] = 'submit';
        $this->_form->handleButtonActions();

        $this->assertEquals(1, $triggered);
    }

    /**
    * @group current
    */
    public function testUnknownButtonPressed()
    {
        $triggered = 0;

        $this->_form->addButton('submit')->action(function($form) use (&$triggered){
            $triggered++;
        });

        $this->_fakeFormSubmit($this->_form);
        $_POST['foobarbaz'] = 'submit'; //this isn't a button
        $this->_form->handleButtonActions();

        $this->assertEquals(0, $triggered);
    }

    /**
    * @group current
    * @expectedException RuntimeException
    */
    public function testAddButtonWithNoName()
    {
        $this->_form->addButton('');
    }

    /**
    * @group current
    */
    public function testSuperglobalOverride()
    {
        $fakeSuperglobal = array('foo'=>'bar');
        $this->_form->setSuperglobalOverride($fakeSuperglobal);
        $this->assertEquals($fakeSuperglobal, $this->_form->getSuperglobal());
    }

    /**
    * @group current
    */
    public function testSuperglobalOverrideCausesFormToAppearSubmitted()
    {
        $this->_form->setSuperglobalOverride(array('foo'=>'bar'));
        $this->assertTrue($this->_form->isSubmitted());
    }

}