<?php

/**
 * Form
 *
 * @author Stefan
 */
namespace ClosureForm {

    class Form {

        private $_name;
        private $_attributes = array();

        private $_rowTemplate;
        private $_fieldErrorTemplate;

        private $_fields = array();
        private $_hiddenFields = array();
        private $_internal_field_prefix = '_internal_';

        public function __construct($name='generic-form', array $attributes=array('method'=>'POST'))
        {
            $this->_name = $name;
            $this->_attributes = $attributes;
            $this->_rowTemplate = $this->_getDefaultRowTemplate();
            $this->_fieldErrorTemplate = $this->_getFieldErrorTemplate();

            $this->addHiddenField($this->_internal_field_prefix.$name)->attributes(array('value'=>'1'));
        }

        public function isSubmitted()
        {
            $submittedValues = $this->getSuperglobal();
            $formSubFieldName = $this->_internal_field_prefix.$this->getName();
            if(!empty($submittedValues[$formSubFieldName]) && $submittedValues[$formSubFieldName] == 1){
                return TRUE;
            }
            return FALSE;
        }

        public function isValid()
        {
            if(!$this->isSubmitted()){
                return true;
            }
            $valid = true;
            foreach($this->getFields() as $field)
            {
                if($valid == true){
                    $valid = $field->isValid();
                }
            }
            return $valid;
        }

        private function _getDefaultRowTemplate($addCls=array())
        {
            return function($innerHtml, array $addCls=array())
            {
                return '<div class="form-row '.implode(" ",$addCls).'">'.$innerHtml.'</div>';
            };
        }
        public function _getFieldErrorTemplate(){
            return function(FieldProxy $field)
            {
                $output = array();
                foreach($field->getErrors() as $errorText){
                    $output[] = '<div class="error-msg">'.$errorText.'</div>';
                }
                return (count($output)) ? '<div class="field-errors">'.implode(PHP_EOL, $output).'</div>' : '';
            };
        }

        public function getName(){
            return $this->_name;
        }

        public function getField($name)
        {
            if(!isset($this->_fields[$name]))
            {
                throw new \RuntimeException("Field $name does not exist");
            }
            return $this->_fields[$name];
        }

        public function getFields(){
            return $this->_fields;
        }

        public function addTextField($name)
        {
            return $this->addInputField('text', $name);
        }

        public function addHiddenField($name)
        {
            return $this->addInputField('hidden', $name);
        }

        public function addPasswordField($name)
        {
            return $this->addInputField('password', $name);
        }

        public function addCheckboxField($name){
            $form = $this;
            $field = $this->addInputField('checkbox', $name);
            $field->template(function($field) use ($form){
                if($form->isSubmitted())
                {
                    if($field->getSubmittedValue() === FALSE)
                    {
                        //unset checked value if form is sumitted but no value is present
                        $field->extractAttribute('checked');
                    }
                    else {
                        if($field->getSubmittedValue()){
                            //always override checked value if submitted
                            $field->attribute('checked', 'checked');
                        }
                    }
                }
                return '<input type="checkbox" name="'.$field->getName().'" '.$field->getAttributeString().'/>';
            });
            return $field;
        }

        public function addInputField($type, $name)
        {
            $field = new FieldProxy($this, $name);
            $field->template(
                function(FieldProxy $field) use ($type){
                    return '<input type="'.$type.'" name="'.$field->getName().'" '.$field->getAttributeString().'/>';
                }
            );
            if($type == 'hidden')
            {
                $this->_hiddenFields[] = $name;
            }
            return $this->_addField($name, $field);
        }

        public function addTextareaField($name)
        {
            $field = new FieldProxy($this, $name);
            $field->template(
                function(FieldProxy $field){
                    $value = $field->extractAttribute('value');
                    return '<textarea name="'.$field->getName().'" '.$field->getAttributeString().'>'.$value.'</textarea>';
                }
            );
            return $this->_addField($name, $field);
        }

        protected function _addField($name, FieldProxy $field)
        {
            if(!\strlen($name))
            {
                throw new \RuntimeException('You cannot add a field with no name');
            }

            $this->_fields[$name] = $field;
            return $field;
        }

        public function render()
        {
            $rowTemplate = $this->_rowTemplate;
            $fieldErrorTemplate = $this->_fieldErrorTemplate;

            $output = array('<form name="'.$this->getName().'" '.$this->getAttributeString($this->_attributes).'>');
            foreach($this->getFields() as $field)
            {
                if(in_array($field->getName(), $this->_hiddenFields))
                {
                    $output[] = $field->render(); //don't render row or error data for a hidden field
                }
                else {
                    $output[] = $rowTemplate($field->render().$fieldErrorTemplate($field), (count($field->getErrors()) ? array('error-row') : array()));
                }
            }
            $output[] = '</form>';

            return (string)implode(PHP_EOL, $output);
        }

        public function getAttributeString($attributes)
        {
            $attributeOutput = array();
            foreach($attributes as $attrName=>$attrValue)
            {
                $attributeOutput[] = $attrName.'="'.$attrValue.'"';
            }
            return implode(" ", $attributeOutput);
        }

        public function getAttribute($name)
        {
            return !empty($this->_attributes[$name]) ? $this->_attributes[$name] : NULL;
        }

        public function getSuperglobal()
        {
            if(empty($this->_attributes['method'])){
                throw new \RuntimeException('Form method attribute is required');
            }
            switch(strtolower($this->_attributes['method']))
            {
                case 'post':
                    return $_POST;
                case 'get':
                    return $_GET;
                default:
                    throw new \RuntimeException('Invalid form method: '.$this->_attributes['method']);
            }
        }

    }
}