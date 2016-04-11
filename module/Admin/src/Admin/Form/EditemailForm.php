<?php

namespace Admin\Form;

use Zend\Form\Form;

class EditemailForm extends Form{
    public function __construct($name = null)
	{
            // we want to ignore the name passed
            parent::__construct('editemail');
            $this->setAttribute('method', 'post');
            $this->setAttribute('enctype','multipart/form-data');
            $this->add(array(
                    'name' => 'emailid',
                    'type' => 'Hidden',                    
            ));
            $this->add(array(
                    'name' => 'emailsub',
                    'type' => 'Text',                    
                    'attributes' => array( 
                        'id' => 'emailsub', 
                        'placeholder' => 'Email Subject',
                        'class'=>'form-control  input-lg',
                    )
            ));            
            
            $this->add(array(
                    'name' => 'emailcontent',
                    'type' => 'Textarea',                    
                    'attributes' => array( 
                        'id' => 'emailcontent', 
                        'placeholder' => 'Content',
                        'class'=>'form-control  input-lg',
                    )
            ));                        
                                                                 
            
            $this->add(array(
                    'name' => 'submitbutton',
                    'type' => 'Submit',                
                    'attributes' => array(
                        'value' => 'Save',
                        'id' => 'submitbutton',
                        'class' => 'btn btn-default saveaddridt paidtridt'
                    ),
            ));
	}    
}
?>
