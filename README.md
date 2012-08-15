ClosureForm
===========

Incomplete Form generation library with heavy use of closures to achieve flexibility in final rendering.

E.g.

```php
<?php

$form = new \ClosureForm\Form('my-form', array('method'=>'POST');
$form->addTextField('foo')
    ->validator(function($value) {
         return ($value) ? NULL : 'Error: Required Field';
    })
    ->attributes(array('class'=>'required'));

if($form->isSubmitted() && $form->isValid())
{
    //do something
}

echo $form->render(); //output form including any errors

```
