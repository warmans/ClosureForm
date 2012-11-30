<?php
namespace ClosureForm\Element {
    /**
     * FieldProxy was initially used to provide a fluent interface for fields ultimately proxying the Form class.
     * At this point they no longer really fit the GoF taxonomy for a Proxy because all the field behaviour was moved
     * to the Field class by way of Closures.
     *
     * @author warmans
     */
    class FieldProxy {

        /**
         * Parent form.
         * @var type
         */
        private $_form;

        /**
         * Invoked immediatly before render.
         * @var \Closure
         */
        private $_preRenderAction;

        /**
         * Template for field. Passed an instace of form and expected to return markup.
         * @var \Closure
         */
        private $_fieldTemplate;

        /**
         * Template for field error. Default is provided.
         * @var \Closure
         */
        private $_errorTemplate;

        private $_fieldType;
        private $_fieldName;
        private $_fieldLabel;

        private $_attributes = array();
        private $_validators = array();

        private $_valid = NULL;
        private $_errors = array();

        /**
         * Construct a new Field.
         *
         * @param \ClosureForm\Form $form The parent form
         * @param string $fieldName
         * @param string $fieldType
         * @throws \RuntimeException
         */
        public function __construct(\ClosureForm\Form $form, $fieldName, $fieldType='text')
        {
            if(!$fieldName)
            {
                throw new \RuntimeException('You cannot create a field with no name');
            }

            $this->_form = $form;
            $this->_fieldName = $fieldName;
            $this->_fieldType = $fieldType;
        }

        /**
         * A function that is executed before the field is rendered .
         *
         * @param \Closure $preRenderAction
         * @return \ClosureForm\Element\FieldProxy
         */
        public function preRender(\Closure $preRenderAction)
        {
            $this->_preRenderAction = $preRenderAction;
            return $this;
        }

        /**
         * Override the template for the field.
         *
         * @param \Closure $template
         * @return \ClosureForm\Element\FieldProxy
         */
        public function template(\Closure $template)
        {
            $this->_fieldTemplate = $template;
            return $this;
        }

        /**
         * Override error template for the field.
         *
         * @param \Closure $template
         * @return \ClosureForm\Element\FieldProxy
         */
        public function errorTemplate(\Closure $template)
        {
            $this->_errorTemplate = $template;
            return $this;
        }

        /**
         * Set multiple attributes at once. Attributes are rendered in the format of key="value"
         *
         * @param array $attributes
         * @return \ClosureForm\Element\FieldProxy
         */
        public function attributes(array $attributes=array())
        {
            $this->_attributes = $attributes;
            return $this;
        }

        /**
         * Set the value of a specific attribute
         *
         * @param string $name
         * @param string $value
         * @return \ClosureForm\Element\FieldProxy
         */
        public function attribute($name, $value){
            $this->_attributes[$name] = $value;
            return $this;
        }

        /**
         * Set the label for the field
         *
         * @param string $label
         * @return \ClosureForm\Element\FieldProxy
         */
        public function label($label)
        {
            $this->_fieldLabel = $label;
            return $this;
        }

        /**
         * Valdate the field using the supplied function. Returning an error message or FALSE will invalidate the field.
         * Returning anything else (e.g. NULL, TRUE, 0, 1) will not invalidate the field.
         *
         * @param \Closure $validator
         * @return \ClosureForm\Element\FieldProxy
         */
        public function validator(\Closure $validator)
        {
            $this->_validators[] = $validator;
            return $this;
        }

        protected function _getDefaultErrorTemplate(){
            return function(FieldProxy $field)
            {
                $output = array();
                foreach($field->getErrors() as $errorText){
                    $output[] = '<div class="error-msg">'.$errorText.'</div>';
                }
                return (count($output)) ? '<div class="field-errors">'.implode(PHP_EOL, $output).'</div>' : '';
            };
        }

        /**
         * Get the name of the field.
         *
         * @return string
         */
        public function getName()
        {
            return $this->_fieldName;
        }

        /**
         * Get the field label (not including label tags).
         *
         * @return string
         */
        public function getLabel()
        {
            return $this->_fieldLabel;
        }

        /**
         * Get the type of the field (e.g. text, password, textarea).
         *
         * @return tring
         */
        public function getType(){
            return $this->_fieldType;
        }

        /**
         * Get the submitted value for this field. If the form hasn't been submitted you will get NULL. If the fiels was
         * not set you will get FALSE (e.g. a non-checked checkbox).
         *
         * @return mixed
         */
        public function getSubmittedValue()
        {
            if(!$this->_form->isSubmitted()){
                return NULL;
            }
            $submittedValues = $this->_form->getSuperglobal();
            return (isset($submittedValues[$this->getName()])) ? $submittedValues[$this->getName()] :  FALSE;
        }

        /**
         * Get the attribute string for the field e.g. class="foo" id="bar".
         *
         * @return string
         */
        public function getAttributeString()
        {
            return $this->_form->getAttributeString($this->_attributes);
        }

        /**
         * Get the value of a single attribute.
         *
         * @param string $name
         * @return string
         */
        public function getAttribute($name)
        {
            return (isset($this->_attributes[$name])) ? $this->_attributes[$name] : NULL;
        }

        /**
         * Get attribute value and remove from attribute array.
         *
         * @param string $name
         * @return mixed
         */
        public function extractAttribute($name)
        {
            if(isset($this->_attributes[$name]))
            {
                $value = $this->_attributes[$name];
                unset($this->_attributes[$name]);
                return $value;
            }
            return NULL;
        }

        /**
         * Test the field against its validators.
         *
         * @return boolean
         */
        public function isValid()
        {
            if(!$this->_form->isSubmitted())
            {
                return TRUE;
            }

            //don't re-run validation or duplicate errors will be created
            if($this->_valid !== NULL)
            {
                return $this->_valid;
            }

            $this->_valid = TRUE;
            foreach($this->_validators as $validator)
            {
                $error = $validator($this->getSubmittedValue());
                if(is_string($error) || $error === FALSE)
                {
                    $this->_errors[] = ($error) ?: 'Value was invalid';
                    $this->_valid = FALSE;
                }
            }
            return $this->_valid;
        }

        /**
         * Get an array of errors or empty array if non were found.
         *
         * @return array
         */
        public function getErrors()
        {
            return $this->_errors;
        }

        /**
         * Add an error to the field.
         *
         * @param string $error
         * @return \ClosureForm\Element\FieldProxy
         */
        public function addError($error){
            $this->_errors[] = $error;
            return $this;
        }

        /**
         * Render the field and return it as a string.
         *
         * @return string
         */
        public function render()
        {
            //always validate
            $this->isValid();

            //repopulate submitted fields
            if($this->_form->isSubmitted()){
                $this->attribute('value', $this->getSubmittedValue());
            }

            //Some fields have unusual logic (e.g. checkboxes) preRender actions allow this to occur without complicating
            //the render template
            ($this->_preRenderAction) ? $this->_preRenderAction->__invoke($this) : null;

            $errorRenderer = ($this->_errorTemplate) ?: $this->_getDefaultErrorTemplate();

            return (($this->_fieldTemplate) ? $this->_fieldTemplate->__invoke($this) : '').$errorRenderer($this);
        }
    }
}