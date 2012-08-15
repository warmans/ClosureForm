<?php
include_once('../boot.php'); //use test bootstrap's autoloader

$form = new \ClosureForm\Form('signup-form', array('method'=>'post', 'action'=>'#', 'class'=>'form'));

/* username */

$form->addTextField('email')->label('Email Address')->validator(function($value){
    if(strlen($value) < 3){
        return 'Email cannot be less than 3 characters';
    }
    if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
        return "This is not a valid email address";
    }
});

/* password + confirmation */

$form->addPasswordField('password')->label('Password')->validator(function($value){
    if(strlen($value) < 5){
        return 'Password cannot be less than 5 characters';
    }

});
$form->addPasswordField('confirm_password')->label('Confirm Password')->validator(function($value) use ($form) {
    if($value != $form->getField('password')->getSubmittedValue()){
        return 'Password Confirmation did not match password';
    }
});

/* submit */

$form->addSubmitField('submit')->attribute('value', 'Login');

/* process form if submitted */

if($form->isSubmitted() && $form->isValid())
{
    if(rand(1, 10) <=5 ){
        $form->addError('We have decided to randomly reject your resignation. Sorry!');
    } else {
        echo 'Thank you';
    }
}

/* output form */

echo $form->render();