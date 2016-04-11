<?php

namespace Admin\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class LoginForm extends Form
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
 
        parent::__construct('adminlogin', $options);
        	
		$this->setAttribute('method', 'post')
        ->setHydrator(new ClassMethods())
        ->setInputFilter(new InputFilter());
		
		$this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'username', 
                'placeholder' => 'Email Address',
				'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        ));		
		$this->add(array( 
            'name' => 'password', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array( 
                'placeholder' => 'Password',               
				'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        ));  
		$this->add(array(
             'name' => 'signin',
             'type' => 'Submit',
             'attributes' => array(                 
                 'value' => 'Sign In',
                 'id' => 'submitbutton',
                 'class' => 'btn btn-success text-uppercase'
             ),
        ));
    }
}

