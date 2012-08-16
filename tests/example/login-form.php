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

$form->addButton('submit')->label('Submit')->action(function($form){
    if($form->isValid()){
        if(rand(1, 10) <= 5){
            $form->addError('We have decided to randomly reject your registration. Sorry!');
            return false;
        } else {
            return true;
        }
    }
});

$form->addButton('login')->label('or Login')->action(function($form){
    //dont't validate - we don't care if the form is valid
    header('Location:/some-login-page.php');
    exit();
});

/* process form if submitted */

if($form->handleButtonActions())
{
    echo 'Thank You, '.$form->getField('email')->getSubmittedValue();
}

/* output form */

echo $form->render();