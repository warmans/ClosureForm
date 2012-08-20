<?php
namespace ClosureForm {
    /**
     * ClosureForm Form. The only component that the user is expected to use directly.
     *
     * @author warmans
     */
    class Form {

        private $_name;
        private $_attributes = array();
        private $_fields = array();
        private $_buttons = array();
        private $_rowTemplate;
        private $_internal_field_prefix = '_internal_';
        private $_valid = NULL;
        private $_generalErrors = array();
        private $_superglobalOverride;

        public function __construct($name='generic-form', array $attributes=array('method'=>'POST'))
        {
            $this->_name = $name;
            $this->_attributes = $attributes;
            $this->_rowTemplate = $this->_getDefaultRowTemplate();

            $this->addHiddenField($this->_internal_field_prefix.$name)->attributes(array('value'=>'1'));
        }

        /**
         * Check if the form has been submitted. This is based on the presence of an auto-generated
         * field in the in the relevant superglobal (POST, GET).
         * @return boolean
         */
        public function isSubmitted()
        {
            if($this->_superglobalOverride){
                //if the user has set a superglobal override we can only assume they want the form to appear submitted
                return TRUE;
            }

            //normal form submit - check for automatically created hidden field
            $submittedValues = $this->getSuperglobal();
            $formSubFieldName = $this->_internal_field_prefix.$this->getName();
            if(!empty($submittedValues[$formSubFieldName]) && $submittedValues[$formSubFieldName] == 1){
                return TRUE;
            }
            return FALSE;
        }

        /**
         * Check if validation has passed (and no external errors have been added). Validation is only run once.
         * @return boolean
         */
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

        /**
         * Causes the action given to the submitted button to be triggered.
         *
         * @return mixed - response of action Closure
         */
        public function handleButtonActions()
        {
            $submittedData = $this->getSuperglobal();
            foreach($this->_buttons as $button)
            {
                if(array_key_exists($button->getName(), $submittedData))
                {
                    return $button->trigger();
                }
            }
        }

        private function _getDefaultRowTemplate()
        {
            return function(FieldProxy $field)
            {
                return '<div class="form-row '.($field->isValid() ? '' : 'error-row').'">'.$field->render().'</div>';
            };
        }

        /**
         * Get the name of the form.
         * @return string
         */
        public function getName(){
            return $this->_name;
        }

        /**
         * Get a field by name. Throws Exception if field is not found.
         * @param string $name
         * @return FieldProxy
         * @throws \RuntimeException
         */
        public function getField($name)
        {
            if(!isset($this->_fields[$name]))
            {
                throw new \RuntimeException("Field $name does not exist");
            }
            return $this->_fields[$name];
        }

        /**
         * Get all the fields for the form
         * @return array
         */
        public function getFields(){
            return $this->_fields;
        }

        /**
         * Get all the buttons for the form
         * @return array
         */
        public function getButtons(){
            return $this->_buttons;
        }

       /**
        * Text type field.
        * @param string $name
        * @return FieldProxy
        */
        public function addTextField($name)
        {
            return $this->addInputField('text', $name);
        }

        /**
         * Hidden type field. Hidden fields do not render row or error elements either.
         * @param string $name
         * @return FieldProxy
         */
        public function addHiddenField($name)
        {
            return $this->addInputField('hidden', $name);
        }

        /**
         * Password type field.
         *
         * @param string $name
         * @return FieldProxy
         */
        public function addPasswordField($name)
        {
            return $this->addInputField('password', $name);
        }

        /**
         * Checkbox type field.
         * @param string $name
         * @return FieldProxy
         */
        public function addCheckboxField($name){
            $form = $this;
            $field = $this->addInputField('checkbox', $name);

            //this is field specific behaviour
            $field->preRender(function($field) use ($form){
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
            });

            $field->template(function($field){
                return '<label><input type="checkbox" name="'.$field->getName().'" '.$field->getAttributeString().'/> '.$field->getLabel().' </label>';
            });

            return $field;
        }

        /**
         * Select field. This method also takes an options array.
         * @param string $name
         * @param array $keyVals field options
         * @return FieldProxy
         */
        public function addSelectField($name, array $keyVals)
        {
            $form = $this;
            $field = new FieldProxy($this, $name, 'select');
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

        /**
         * Add an field with an arbritrary type.
         * @param string $type
         * @param string $name
         * @return FieldProxy
         */
        public function addInputField($type, $name)
        {
            $field = new FieldProxy($this, $name, $type);
            $field->template(
                function(FieldProxy $field) use ($type)
                {
                    $label = ($type == 'hidden') ? '' : '<label>'.$field->getLabel().'</label>';
                    return $label.'<input type="'.$type.'" name="'.$field->getName().'" '.$field->getAttributeString().'/>';
                }
            );

            return $this->_addField($name, $field);
        }

        /**
         * Textarea type field.
         * @param string $name
         * @return FieldProxy
         */
        public function addTextareaField($name)
        {
            $field = new FieldProxy($this, $name, 'textarea');
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
            $this->_fields[$name] = $field;
            return $field;
        }

       /**
         * Adds a button to the form. A button is similar to a field but can be assigned an action to perform
         * if the form is submitted using that button. You must only call handleButtonActions to trigger the relevant
         * action.
         *
         * @param string $name
         * @return \ClosureForm\Button
         */
        public function addButton($name)
        {
            return $this->_addButton($name, new ButtonProxy($this, $name));
        }

        protected function _addButton($name, ButtonProxy $field)
        {
            if(!\strlen($name))
            {
                throw new \RuntimeException('You cannot add a button with no name');
            }
            $this->_buttons[$name] = $field;
            return $field;
        }

        /**
         * Add an external error (i.e. not based on internal validation) to the form. If a field is specified the error
         * will be appended to that field's error array. Otherwise it'll just display as a generic error.
         * @param string $errorMsg
         * @param string $affectsField fieldname of affected field
         * @return type
         */
        public function addError($errorMsg, $affectsField=NULL)
        {
            if($affectsField)
            {
                if(array_key_exists($affectsField, $this->_fields))
                {
                    $field = $this->getField($affectsField);
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

        /**
         * Render the entire form including all errors, fields and buttons
         * @return type
         */
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
                if($field->getType() === 'hidden')
                {
                    //don't render row or errors for a hidden field
                    $output[] = $field->errorTemplate(function($value){ return; })->render();
                }
                else {
                    $output[] = $rowTemplate($field);
                }
            }
            if(count($this->getButtons())){

                $output[] = '<div class="button-row">';
                foreach($this->getButtons() as $button)
                {
                    $output[] = $button->render();
                }
                $output[] = '</div>';
            }
            $output[] = '</form>';

            return (string)implode(PHP_EOL, $output);
        }

        /**
         * Convert keyval pairs into an attribute string.
         * @param array $attributes
         * @return string
         */
        public function getAttributeString($attributes)
        {
            $attributeOutput = array();
            foreach($attributes as $attrName=>$attrValue)
            {
                $attributeOutput[] = $attrName.'="'.$attrValue.'"';
            }
            return implode(" ", $attributeOutput);
        }

        /**
         * Get the value of a specific form attribute.
         * @param string $name
         * @return type
         */
        public function getAttribute($name)
        {
            return empty($this->_attributes[$name]) ? NULL : $this->_attributes[$name];
        }

        /**
         * Override the superglobal specified by the form method e.g. rather than using _POST use $data.
         * @param array $data
         */
        public function setSuperglobalOverride(array $data)
        {
            $this->_superglobalOverride = $data;
        }

        /**
         * Get the POST or GET array depending on the form method attribute.
         * @return array
         * @throws \RuntimeException
         */
        public function getSuperglobal()
        {
            //allow superglobal to be overridden by manually created data array
            if($this->_superglobalOverride)
            {
                return $this->_superglobalOverride;
            }

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