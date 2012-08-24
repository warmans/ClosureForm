Docs
====
ClosureForm
-----------
### Form ###
>    ClosureForm Form
>    
>    Lightweight form generation/handling library that gives the end user a lot of flexibility though the use
>    of Closures/Anonymous functions. Validations are Closures, field templates are Closures, buttons can be given
>    actions though Closures that are executed automatically on form submit.
>    
>    
>    `Link : https://github.com/warmans/ClosureForm`
>    
>    
>    `Author : warmans`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **__construct**( [string $name], [array $attributes] )</pre>
>    
>    
>    >    Construct a new form.
>    
>    `Param Note: $name defaults to 'generic-form'. The name of this particular form.`
>    
>    
>    `Param Note: $attributes defaults to 'Array'. Attributes are rendered as attt="val" in the form tag. You should set method, action etc. here`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **isSubmitted**(  )</pre>
>    
>    
>    >    Check if the form has been submitted. This is based on the presence of an auto-generated
>    >    field in the in the relevant superglobal (POST, GET).
>    
>    `Return : boolean `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **isValid**(  )</pre>
>    
>    
>    >    Check if validation has passed (and no external errors have been added). Validation is only run once.
>    
>    `Return : boolean `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **handleButtonActions**(  )</pre>
>    
>    
>    >    Causes the action given to the submitted button to be triggered.
>    
>    `Return : mixed - response of action Closure`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getName**(  )</pre>
>    
>    
>    >    Get the name of the form as defined in the constructor.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getField**( string $name )</pre>
>    
>    
>    >    Get a field by name. Throws Exception if field is not found.
>    
>    `Return : Element\FieldProxy `
>    
>    
>    `Throws : \RuntimeException`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getFields**(  )</pre>
>    
>    
>    >    Get all the fields added to the form.
>    
>    `Return : array Returns an array of FieldProxy elements`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getButtons**(  )</pre>
>    
>    
>    >    Get all the buttons for the form.
>    
>    `Return : array Returns and array of ButtonProxy elements`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addTextField**( string $name )</pre>
>    
>    
>    >    Text type field (i.e. a standard input).
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addHiddenField**( string $name )</pre>
>    
>    
>    >    Hidden field. Hidden fields do not render row or error elements either.
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addPasswordField**( string $name )</pre>
>    
>    
>    >    Password field (text field with obsucred characters)
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addCheckboxField**( string $name )</pre>
>    
>    
>    >    Checkbox type field.
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addSelectField**( string $name, array $keyVals )</pre>
>    
>    
>    >    Select field. This method also takes an options array.
>    
>    `Param Note: $keyVals field options in key=>val (value=>display) pairs`
>    
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addInputField**( string $type, string $name )</pre>
>    
>    
>    >    Field with a user defined type (e.g. text, password, etc.)
>    
>    `Param Note: $type The value given to the type attribute of the field.`
>    
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addTextareaField**( string $name )</pre>
>    
>    
>    >    Textarea type field.
>    
>    `Return : Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addButton**( string $name )</pre>
>    
>    
>    >    Adds a button to the form. A button is similar to a field but can be assigned an action to perform
>    >    if the form is submitted using that button. You must only call handleButtonActions to trigger the relevant
>    >    action.
>    
>    `Return : \ClosureForm\Button `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addError**( string $errorMsg, [string $affectsField] )</pre>
>    
>    
>    >    Add an external error (i.e. not based on internal validation) to the form. If a field is specified the error
>    >    will be appended to that field's error array. Otherwise it'll just display as a generic error.
>    >    Returns $this to allow error chaining.
>    
>    `Param Note: $affectsField fieldname of affected field`
>    
>    
>    `Return : Form `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **render**(  )</pre>
>    
>    
>    >    Render the entire form including all errors, fields and buttons and return as a string.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getAttributeString**( array $attributes )</pre>
>    
>    
>    >    Convert keyval pairs into an attribute string. Users should not need this.
>    
>    `Param Note: $attributes name=>val pairs`
>    
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getAttribute**( string $name )</pre>
>    
>    
>    >    Get the value of a specific form attribute or NULL if empty.
>    
>    `Return : mixed `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **setSuperglobalOverride**( array $data )</pre>
>    
>    
>    >    Override the superglobal specified by the form method e.g. rather than using _POST use $data.
>    
>    
>    -----------------
>    
>    <pre>`public` **getSuperglobal**(  )</pre>
>    
>    
>    >    Get the POST or GET array depending on the form method attribute.
>    
>    `Return : array `
>    
>    
>    `Throws : \RuntimeException`
>    
>    

ClosureForm\Element
-------------------
### FieldProxy ###
>    FieldProxy was initially used to provide a fluent interface for fields ultimately proxying the Form class.
>    At this point they no longer really fit the GoF taxonomy for a Proxy because all the field behaviour was moved
>    to the Field class by way of Closures.
>    
>    
>    `Author : warmans`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **__construct**( ClosureForm\Form $form, string $fieldName, [string $fieldType] )</pre>
>    
>    
>    >    Construct a new Field.
>    
>    `Param Note: $form The parent form`
>    
>    
>    `Param Note: $fieldType defaults to 'text'. `
>    
>    
>    `Throws : \RuntimeException`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **preRender**( Closure $preRenderAction )</pre>
>    
>    
>    >    A function that is executed before the field is rendered .
>    
>    `Return : \ClosureForm\Element\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **template**( Closure $template )</pre>
>    
>    
>    >    Override the template for the field.
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **errorTemplate**( Closure $template )</pre>
>    
>    
>    >    Override error template for the field.
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **attributes**( [array $attributes] )</pre>
>    
>    
>    >    Set multiple attributes at once. Attributes are rendered in the format of key="value"
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **attribute**( string $name, string $value )</pre>
>    
>    
>    >    Set the value of a specific attribute
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **label**( string $label )</pre>
>    
>    
>    >    Set the label for the field
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **validator**( Closure $validator )</pre>
>    
>    
>    >    Valdate the field using the supplied function. Returning an error message or FALSE will invalidate the field.
>    >    Returning anything else (e.g. NULL, TRUE, 0, 1) will not invalidate the field.
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getName**(  )</pre>
>    
>    
>    >    Get the name of the field.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getLabel**(  )</pre>
>    
>    
>    >    Get the field label (not including label tags).
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getType**(  )</pre>
>    
>    
>    >    Get the type of the field (e.g. text, password, textarea).
>    
>    `Return : tring `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getSubmittedValue**(  )</pre>
>    
>    
>    >    Get the submitted value for this field. If the form hasn't been submitted you will get NULL. If the fiels was
>    >    not set you will get FALSE (e.g. a non-checked checkbox).
>    
>    `Return : mixed `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getAttributeString**(  )</pre>
>    
>    
>    >    Get the attribute string for the field e.g. class="foo" id="bar".
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getAttribute**( string $name )</pre>
>    
>    
>    >    Get the value of a single attribute.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **extractAttribute**( string $name )</pre>
>    
>    
>    >    Get attribute value and remove from attribute array.
>    
>    `Return : mixed `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **isValid**(  )</pre>
>    
>    
>    >    Test the field against its validators.
>    
>    `Return : boolean `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getErrors**(  )</pre>
>    
>    
>    >    Get an array of errors or empty array if non were found.
>    
>    `Return : array `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **addError**( string $error )</pre>
>    
>    
>    >    Add an error to the field.
>    
>    `Return : \ClosureForm\FieldProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **render**(  )</pre>
>    
>    
>    >    Render the field and return it as a string.
>    
>    `Return : string `
>    
>    

### ButtonProxy ###
>    ButtonProxy - Buttons only offer a small subset of options of a field but do allow for an action Closure to be
>    set and called automatically by the form. As per FieldProxy buttons aren't really GoF Proxies anymore but
>    retain the name for histrorical reasons.
>    
>    
>    `Author : warmans`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **__construct**( ClosureForm\Form $form, string $buttonName )</pre>
>    
>    
>    >    Create a new Button Proxy
>    
>    `Param Note: $form The parent form`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **label**( type $label )</pre>
>    
>    
>    >    Set the button label (the text appearing IN the button).
>    
>    `Return : \ClosureForm\Element\ButtonProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **action**( Closure $action )</pre>
>    
>    
>    >    Define the action that is triggered when the button is pressed. This is optional - you can handle the form
>    >    in the usual way.
>    
>    `Return : \ClosureForm\Element\ButtonProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **template**( Closure $template )</pre>
>    
>    
>    >    Set the button template.
>    
>    `Return : \ClosureForm\Element\ButtonProxy `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getName**(  )</pre>
>    
>    
>    >    Get the button's name attribute value.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getLabel**(  )</pre>
>    
>    
>    >    Get the label value (the text shown on the button)
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **getAttributeString**(  )</pre>
>    
>    
>    >    Get the attribute array of the button as a string.
>    
>    `Return : string `
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **trigger**(  )</pre>
>    
>    
>    >    Call the action closure assigned to the button. This is called automatically by the form.
>    
>    `Return : boolean `
>    
>    
>    `Throws : \RuntimeException`
>    
>    
>    
>    -----------------
>    
>    <pre>`public` **render**(  )</pre>
>    
>    
>    >    Render the button and return as a string.
>    
>    `Return : string `
>    
>    
