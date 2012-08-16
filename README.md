ClosureForm
===========

Form generation library with heavy use of closures for... everything.

Examples can be found in the tests directory along with a set of unit tests (coverage 99%+).

```php
<?php

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
    if($form->isValid())
    {
        if(rand(1, 10) <= 5)
        {
            $form->addError('We have decided to randomly reject your registration. Sorry!');
            return false;
        }
        else {
            return true;
        }
    }
});

$form->addButton('login')->label('or Login')->action(function($form){
    header('Location:/some-login-page.php');
    exit();
});

/* process form using the relevant button action if submitted */

if($form->handleButtonActions())
{
    echo 'Thank You, '.$form->getField('email')->getSubmittedValue();
}

/* output form */

echo $form->render();

```

TODOs
===========
- CSRF Protection (not easy when most people use their own session handler). Session save handler could be passed as optional closure - otherwise native PHP session handling can be used.
- Radio Buttons
- Field Arrays (e.g. name[])
- Fieldsets
- Filters
