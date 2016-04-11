<?php

namespace Admin\Form;

use Zend\Form\Form;

class EditcatForm extends Form{
    public function __construct($name = null)
	{
            // we want to ignore the name passed
            parent::__construct('editcat');
            $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
            $this->add(array(
                    'name' => 'catid',
                    'type' => 'Hidden',                    
            ));
            $this->add(array(
                    'name' => 'catname',
                    'type' => 'Text',                    
                    'attributes' => array( 
                        'id' => 'catname', 
                        'placeholder' => 'Category Name',
                        'class'=>'form-control  input-lg',
                    )
            ));
            
            $this->add(array(
                'name' => 'fileupload',                
                'attributes' => array(
                    'type'  => 'file',
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