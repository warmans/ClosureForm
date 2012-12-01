<?php
namespace ClosureForm {
    /**
     * ClosureForm Form
     *
     * Lightweight form generation/handling library that gives the end user a lot of flexibility though the use
     * of Closures/Anonymous functions. Validations are Closures, field templates are Closures, buttons can be given
     * actions though Closures that are executed automatically on form submit.
     *
     * @link https://github.com/warmans/ClosureForm
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

        /**
         * Construct a new form.
         *
         * @param string $name The name of this particular form.
         * @param array $attributes Attributes are rendered as attt="val" in the form tag. You should set method, action etc. here
         */
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
         *
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
         *
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

        protected function _getDefaultRowTemplate()
        {
            return function(Element\FieldProxy $field)
            {
                return '<div class="form-row'.($field->isValid() ? '' : ' error-row').'">'.$field->render().'</div>';
            };
        }

        /**
         * Get the name of the form as defined in the constructor.
         *
         * @return string
         */
        public function getName(){
            return $this->_name;
        }

        /**
         * Get a field by name. Throws Exception if field is not found.
         *
         * @param string $name
         * @return Element\FieldProxy
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
         * Get all the fields added to the form.
         *
         * @return array Returns an array of FieldProxy elements
         */
        public function getFields(){
            return $this->_fields;
        }

        /**
         * Get all the buttons for the form.
         *
         * @return array Returns and array of ButtonProxy elements
         */
        public function getButtons(){
            return $this->_buttons;
        }

       /**
        * Text type field (i.e. a standard input).
        *
        * @param string $name
        * @return Element\FieldProxy
        */
        public function addTextField($name)
        {
            return $this->addInputField('text', $name);
        }

        /**
         * Hidden field. Hidden fields do not render row or error elements either.
         *
         * @param string $name
         * @return Element\FieldProxy
         */
        public function addHiddenField($name)
        {
            return $this->addInputField('hidden', $name);
        }

        /**
         * Password field (text field with obsucred characters)
         *
         * @param string $name
         * @return Element\FieldProxy
         */
        public function addPasswordField($name)
        {
            return $this->addInputField('password', $name);
        }

        /**
         * Checkbox type field.
         *
         * @param string $name
         * @return Element\FieldProxy
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
                return '<label for="'.$field->getName().'"><input type="'.$field->getType().'" id="'.$field->getName().'" name="'.$field->getName().'" '.$field->getAttributeString().'/> '.$field->getLabel().' </label>';
            });

            return $field;
        }

        /**
         * Select field. This method also takes an options array.
         *
         * @param string $name
         * @param array $keyVals field options in key=>val (value=>display) pairs
         * @return Element\FieldProxy
         */
        public function addSelectField($name, array $keyVals)
        {
            $fieldProxyClass = $this->_getFieldProxyClass();

            $form = $this;
            $field = new $fieldProxyClass($this, $name, 'select');
            $field->template(
                function(Element\FieldProxy $field) use ($keyVals, $form){
                    $output = array();
                    $value = ($form->isSubmitted()) ? $field->getSubmittedValue() : $field->extractAttribute('value');
                    $output[] = '<label for="'.$field->getName().'">'.$field->getLabel().'</label><select id="'.$field->getName().'" name="'.$field->getName().'" '.$field->getAttributeString().'>';
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
         * Field with a user defined type (e.g. text, password, etc.)
         *
         * @param string $type The value given to the type attribute of the field.
         * @param string $name
         * @return Element\FieldProxy
         */
        public function addInputField($type, $name)
        {
            $fieldProxyClass = $this->_getFieldProxyClass();

            $field = new $fieldProxyClass($this, $name, $type);
            $field->template(
                function(Element\FieldProxy $field) use ($type)
                {
                    $label = ($type == 'hidden') ? '' : '<label for="'.$field->getName().'">'.$field->getLabel().'</label>';
                    return $label.'<input type="'.$type.'" id="'.$field->getName().'" name="'.$field->getName().'" '.$field->getAttributeString().'/>';
                }
            );

            return $this->_addField($name, $field);
        }

        /**
         * Textarea type field.
         *
         * @param string $name
         * @return Element\FieldProxy
         */
        public function addTextareaField($name)
        {
            $fieldProxyClass = $this->_getFieldProxyClass();

            $field = new $fieldProxyClass($this, $name, 'textarea');
            $field->template(
                function(Element\FieldProxy $field){
                    $value = $field->extractAttribute('value');
                    return '<label for="'.$field->getName().'">'.$field->getLabel().'</label><textarea id="'.$field->getName().'" name="'.$field->getName().'" '.$field->getAttributeString().'>'.$value.'</textarea>';
                }
            );
            return $this->_addField($name, $field);
        }

        protected function _getFieldProxyClass()
        {
            return "ClosureForm\\Element\\FieldProxy";
        }

        protected function _addField($name, Element\FieldProxy $field)
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
            return $this->_addButton($name, new Element\ButtonProxy($this, $name));
        }

        protected function _addButton($name, Element\ButtonProxy $field)
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
         * Returns $this to allow error chaining.
         *
         * @param string $errorMsg
         * @param string $affectsField fieldname of affected field
         * @return Form
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

            return $this;
        }

        private function _addGeneralError($errorMsg)
        {
            $this->_generalErrors[] = $errorMsg;
            return $this;
        }

        /**
         * Render the entire form including all errors, fields and buttons and return as a string.
         *
         * @return string
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
         * Convert keyval pairs into an attribute string. Users should not need this.
         *
         * @param array $attributes name=>val pairs
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
         * Get the value of a specific form attribute or NULL if empty.
         *
         * @param string $name
         * @return mixed
         */
        public function getAttribute($name)
        {
            return empty($this->_attributes[$name]) ? NULL : $this->_attributes[$name];
        }

        /**
         * Override the superglobal specified by the form method e.g. rather than using _POST use $data.
         *
         * @param array $data
         */
        public function setSuperglobalOverride(array $data)
        {
            $this->_superglobalOverride = $data;
        }

        /**
         * Get the POST or GET array depending on the form method attribute.
         *
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