<?php

namespace Admin\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class ChangePasswordForm extends Form
{
	/**
     * @var EntityManager
     */
    protected $em;    
	
    /**
     * @var FilterPluginManager
     */
    protected $fpm;
 
    
    public function __construct($name = null, $options = array())	
	{	
 
        parent::__construct('adminchangepass', $options);
        	
		$this->setAttribute('method', 'post')
        ->setHydrator(new ClassMethods())
        ->setInputFilter(new InputFilter());
		
		$this->add(array( 
            'name' => 'oldpassword', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'id' => 'oldpassword', 
                'placeholder' => 'Old Password',
				'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        ));		
		$this->add(array( 
            'name' => 'newpassword', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
				'id' => 'newpassword', 
                'placeholder' => 'New Password',               
				'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        )); 
		$this->add(array( 
            'name' => 'confnewpass', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
				'id' => 'confnewpass', 
                'placeholder' => 'Confirm New Password',               
				'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        ));  
		$this->add(array(
             'name' => 'savebtn',
             'type' => 'Submit',
             'attributes' => array(                 
                 'value' => 'Save',
                 'id' => 'savebtn',
                 'class' => 'btn btn-default saveaddridt'
             ),
        ));
    }
}

