ClosureForm
===========

Form generation library with heavy use of closures for... everything.

Examples can be found in the tests directory along with a set of unit tests (coverage 99%+).

```php
<?php

$form = new \ClosureForm\Form('signup-form', array('method'=>'post', 'action'=>'#', 'class'=>'form'));

/* username */

$form->addTextField('email')
    ->label('Email Address')
    ->validator(function($value){
        //first validator
        if(strlen($value) < 3){
            return 'Email cannot be less than 3 characters';
        }
    })
    ->validator(function($value){
        //second validator
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
    //example of using another field's value in a field validator via the USE keyword
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

```

Outputs (without the tabs but including line breaks):

````
<form name="signup-form" method="post" action="#" class="form">
    <input type="hidden" id="_internal_signup-form" name="_internal_signup-form" value="1"/>
    <div class="form-row"><label for="email">Email Address</label><input type="text" id="email" name="email" /></div>
    <div class="form-row"><label for="password">Password</label><input type="password" id="password" name="password" /></div>
    <div class="form-row"><label for="confirm_password">Confirm Password</label><input type="password" id="confirm_password" name="confirm_password" /></div>
    <div class="button-row">
        <button name="submit" >Submit</button>
        <button name="login" >or Login</button>
    </div>
</form>
````

TODO
===========
- CSRF Protection (not easy when most people use their own session handler). Session save handler could be passed as optional closure - otherwise native PHP session handling can be used.
- Radio Buttons
- Field Arrays (e.g. name[])
- Fieldsets
- Filters

Maybe TODO
===========
- Automatic closure binding to form scope http://www.php.net/manual/en/closure.bind.php (5.4 only+) - 5.4 is still too rare in the wild to add this.