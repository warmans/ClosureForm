<?php
namespace ClosureForm\Element {
    /**
     * ButtonProxy - Buttons only offer a small subset of options of a field but do allow for an action Closure to be
     * set and called automatically by the form. As per FieldProxy buttons aren't really GoF Proxies anymore but
     * retain the name for histrorical reasons.
     *
     * @author warmans
     */
    class ButtonProxy {

        private $_form;
        private $_name;
        private $_attributes = array();
        private $_label;
        private $_action;
        private $_template;

        /**
         * Create a new Button Proxy
         *
         * @param \ClosureForm\Form $form The parent form
         * @param string $buttonName
         */
        public function __construct(\ClosureForm\Form $form, $buttonName)
        {
            $this->_form = $form;
            $this->_name = $buttonName;
        }

        /**
         * Set the button label (the text appearing IN the button).
         *
         * @param type $label
         * @return \ClosureForm\Element\ButtonProxy
         */
        public function label($label){
            $this->_label = $label;
            return $this;
        }

        /**
         * Define the action that is triggered when the button is pressed. This is optional - you can handle the form
         * in the usual way.
         *
         * @param \Closure $action
         * @return \ClosureForm\Element\ButtonProxy
         */
        public function action(\Closure $action)
        {
            $this->_action = $action;
            return $this;
        }

        /**
         * Set the button template.
         *
         * @param \Closure $template
         * @return \ClosureForm\Element\ButtonProxy
         */
        public function template(\Closure $template)
        {
            $this->_template = $template;
            return $this;
        }

        /**
         * Get the button's name attribute value.
         *
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * Get the label value (the text shown on the button)
         *
         * @return string
         */
        public function getLabel()
        {
            return $this->_label;
        }

        /**
         * Get the attribute array of the button as a string.
         *
         * @return string
         */
        public function getAttributeString(){
            return $this->_form->getAttributeString($this->_attributes);
        }

        /**
         * Call the action closure assigned to the button. This is called automatically by the form.
         *
         * @return boolean
         * @throws \RuntimeException
         */
        public function trigger()
        {
            if($this->_form->isSubmitted()){
                $action = $this->_action;
                if($action instanceof \Closure){
                    return $action($this->_form);
                } else{
                    throw new \RuntimeException('No/Invalid Action Defined For Button');
                }
            }
            return FALSE;
        }

        /**
         * Render the button and return as a string.
         * 
         * @return string
         */
        public function render()
        {
            $template = $this->_template ?: $this->_getDefaultButtonTemplate();
            return $template($this);
        }

        private function _getDefaultButtonTemplate(){
            return function($button){
                return '<button name="'.$button->getName().'" '.$button->getAttributeString().'>'.$button->getLabel().'</button>';
            };
        }

    }

}