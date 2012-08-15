<?php
namespace ClosureForm {

    class FieldProxy {

        private $_form;

        private $_fieldTemplate;
        private $_errorTemplate;

        private $_fieldName;
        private $_attributes = array();
        private $_validator;

        private $_valid = NULL;
        private $_errors = array();

        public function __construct(Form $form, $fieldName)
        {
            $this->_form = $form;
            $this->_fieldName = $fieldName;
        }

        /*fluent interface*/

        public function template(\Closure $template)
        {
            $this->_fieldTemplate = $template;
            return $this;
        }

        public function errorTemplate(\Closure $template)
        {
            $this->_errorTemplate = $template;
            return $this;
        }

        public function attributes(array $attributes=array())
        {
            $this->_attributes = $attributes;
            return $this;
        }

        public function attribute($name, $value){
            $this->_attributes[$name] = $value;
            return $this;
        }

        public function validator(\Closure $validator)
        {
            $this->_validator = $validator;
        }

        /*end fluent interface*/

        public function _getDefaultErrorTemplate(){
            return function(FieldProxy $field)
            {
                $output = array();
                foreach($field->getErrors() as $errorText){
                    $output[] = '<div class="error-msg">'.$errorText.'</div>';
                }
                return (count($output)) ? '<div class="field-errors">'.implode(PHP_EOL, $output).'</div>' : '';
            };
        }

        public function getName()
        {
            return $this->_fieldName;
        }

        public function getSubmittedValue()
        {
            if(!$this->_form->isSubmitted()){
                return NULL;
            }
            $submittedValues = $this->_form->getSuperglobal();
            return (isset($submittedValues[$this->getName()])) ? $submittedValues[$this->getName()] :  FALSE;
        }

        public function getAttributeString()
        {
            return $this->_form->getAttributeString($this->_attributes);
        }

        public function getAttribute($name)
        {
            return (isset($this->_attributes[$name])) ? $this->_attributes[$name] : NULL;
        }

        /**
         * Get attribute value and remove from attribute array
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
            if($validator = $this->_validator)
            {
                $error = $validator($this->getSubmittedValue());
                if($error)
                {
                    $this->_errors[] = $error;
                    $this->_valid = FALSE;
                    return $this->_valid;
                }
            }
            return $this->_valid;
        }

        public function getErrors()
        {
            return $this->_errors;
        }

        public function render()
        {
            $this->isValid();//always validate

            $templateRenderer = $this->_fieldTemplate;
            $errorRenderer = ($this->_errorTemplate) ?: $this->_getDefaultErrorTemplate();

            return $templateRenderer($this).$errorRenderer($this);
        }
    }
}