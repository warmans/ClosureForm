<?php
namespace ClosureForm {
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

        public function __construct(\ClosureForm\Form $form, $buttonName)
        {
            $this->_form = $form;
            $this->_name = $buttonName;
        }

        public function label($label){
            $this->_label = $label;
            return $this;
        }

        public function action(\Closure $action)
        {
            $this->_action = $action;
            return $this;
        }

        public function template(\Closure $template)
        {
            $this->_template = $template;
            return $this;
        }

        public function getName()
        {
            return $this->_name;
        }

        public function getLabel()
        {
            return $this->_label;
        }

        public function getAttributeString(){
            return $this->_form->getAttributeString($this->_attributes);
        }

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