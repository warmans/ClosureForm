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
        private $_fields = array();
        private $_hiddenFields = array();
        private $_rowTemplate;
        private $_internal_field_prefix = '_internal_';
        private $_valid = NULL;
        private $_generalErrors = array();

        public function __construct($name='generic-form', array $attributes=array('method'=>'POST'))
        {
            $this->_name = $name;
            $this->_attributes = $attributes;
            $this->_rowTemplate = $this->_getDefaultRowTemplate();

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
                return TRUE;
            }

            //general errors cause validation to fail after isValid has been run
            if(count($this->_generalErrors)){
                $this->_valid = FALSE;
            }

            //don't re-validate if validation has already run once
            if($this->_valid !== NULL)
            {
                return $this->_valid;
            }

            $this->_valid = true;
            foreach($this->getFields() as $field)
            {
                if($this->_valid == true){
                    $this->_valid = $field->isValid();
                }
            }

            return $this->_valid;
        }

        private function _getDefaultRowTemplate()
        {
            return function(FieldProxy $field)
            {
                return '<div class="form-row '.($field->isValid() ? '' : 'error-row').'">'.$field->render().'</div>';
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

        //TODO: When buttons have been implemented I think this can be removed
        public function addSubmitField($name)
        {
            return $this->addInputField('submit', $name);
        }

        //TODO: Add button - can we do $name, Closure $handler? maybe requires buttons to not be standard field proxies
        public function addButton($name)
        {

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
                return '<label><input type="checkbox" name="'.$field->getName().'" '.$field->getAttributeString().'/> '.$field->getLabel().' </label>';
            });
            return $field;
        }

        public function addSelectField($name, array $keyVals)
        {
            $form = $this;
            $field = new FieldProxy($this, $name);
            $field->template(
                function(FieldProxy $field) use ($keyVals, $form){
                    $output = array();
                    $value = ($form->isSubmitted()) ? $field->getSubmittedValue() : $field->extractAttribute('value');
                    $output[] = '<label>'.$field->getLabel().'</label><select name="'.$field->getName().'" '.$field->getAttributeString().'>';
                    foreach($keyVals as $submitValue=>$displayValue)
                    {
                        $selected = ($value == $submitValue) ? 'selected="selected"' : '';
                        $output[] = '<option value="'.$submitValue.'" '.$selected.'>'.$displayValue.'</option>';
                    }
                    $output[] = '</select>';
                    return  implode(PHP_EOL, $output);
                }
            );
            return $this->_addField($name, $field);
        }

        public function addInputField($type, $name)
        {
            $field = new FieldProxy($this, $name);
            $field->template(
                function(FieldProxy $field) use ($type)
                {
                    $label = ($type == 'hidden') ? '' : '<label>'.$field->getLabel().'</label>';
                    return $label.'<input type="'.$type.'" name="'.$field->getName().'" '.$field->getAttributeString().'/>';
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
                    return '<label>'.$field->getLabel().'</label><textarea name="'.$field->getName().'" '.$field->getAttributeString().'>'.$value.'</textarea>';
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

        public function addError($errorMsg, $affectsField=NULL)
        {
            if($affectsField)
            {
                if($field = $this->getField($affectsField)){
                    $field->addError($errorMsg);
                    return;
                }
            }

            //error not related to any field in particular
            $this->_addGeneralError($errorMsg);
        }

        private function _addGeneralError($errorMsg)
        {
            $this->_generalErrors[] = $errorMsg;
            return $this;
        }

        public function render()
        {
            $rowTemplate = $this->_rowTemplate;

            //always validate
            $this->isValid();

            $output = array('<form name="'.$this->getName().'" '.$this->getAttributeString($this->_attributes).'>');

            //generic errors
            foreach($this->_generalErrors as $error)
            {
                $output[] = '<div class="general-error">'.$error.'</div>';
            }

            //fields
            foreach($this->getFields() as $field)
            {
                if(in_array($field->getName(), $this->_hiddenFields))
                {
                    //don't render row or errors for a hidden field
                    $output[] = $field->errorTemplate(function($value){ return; })->render();
                }
                else {
                    $output[] = $rowTemplate($field);
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