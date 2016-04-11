<?php

namespace Admin\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class ForgotPasswordForm extends Form
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
 
        parent::__construct('forgotpass', $options);
        	
		$this->setAttribute('method', 'post')
        ->setHydrator(new ClassMethods())
        ->setInputFilter(new InputFilter());
		
		$this->add(array( 
            'name' => 'emailaddr', 
            'type' => 'Text', 
            'attributes' => array( 
                'id' => 'emailaddr', 
                'placeholder' => 'Email Address',
                'class'=>'form-control  input-lg',
            ), 
            'options' => array( 
            ), 
        ));			
		$this->add(array(
             'name' => 'savebtn',
             'type' => 'Submit',
             'attributes' => array(                 
                 'value' => 'Recover Password',
                 'id' => 'submitbutton',
                 'class' => 'btn btn-success text-uppercase'
             ),
        ));
    }
}

