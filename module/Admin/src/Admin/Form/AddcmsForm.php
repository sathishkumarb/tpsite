<?php

namespace Admin\Form;

use Zend\Form\Form;

class AddcmsForm extends Form{
    public function __construct($name = null)
	{
            // we want to ignore the name passed
            parent::__construct('addcms');
            $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');
            $this->add(array(
                    'name' => 'cmsid',
                    'type' => 'Hidden',                    
            ));
            $this->add(array(
                    'name' => 'cmstitle',
                    'type' => 'Text',                    
                    'attributes' => array( 
                        'id' => 'cmstitle', 
                        'placeholder' => 'Page Title',
                        'class'=>'form-control  input-lg',
                    )
            ));            
            
            $this->add(array(
                    'name' => 'cmsdesc',
                    'type' => 'Textarea',                    
                    'attributes' => array( 
                        'id' => 'cmsdesc', 
                        'placeholder' => 'Content',
                        'class'=>'form-control  input-lg',
                    )
            ));            
            
            $this->add(array(
                    'name' => 'keywords',
                    'type' => 'Text',                    
                    'attributes' => array( 
                        'id' => 'keywords', 
                        'placeholder' => 'Keywords',
                        'class'=>'form-control  input-lg',
                    )
            ));            
            
            $this->add(array(
                    'name' => 'metatag',
                    'type' => 'Textarea',                    
                    'attributes' => array( 
                        'id' => 'metatag', 
                        'placeholder' => 'Meta Tag',
                        'class'=>'form-control  input-lg',
                    )
            )); 
            
            $this->add(array(
                    'name' => 'metadesc',
                    'type' => 'Textarea',                    
                    'attributes' => array( 
                        'id' => 'metadesc', 
                        'placeholder' => 'Meta Description',
                        'class'=>'form-control  input-lg',
                    )
            ));            
            
            
            $this->add(array(
                    'name' => 'submitbutton',
                    'type' => 'Submit',                
                    'attributes' => array(
                        'value' => 'Add',
                        'id' => 'submitbutton',
                        'class' => 'btn btn-default saveaddridt paidtridt'
                    ),
            ));
	}    
}
?>
