<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class SignupForm extends Form
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
 
        parent::__construct('usersignup', $options);
        
        $this->setAttribute('method', 'post')
        ->setHydrator(new ClassMethods())
        ->setInputFilter(new InputFilter());
        
        $this->setAttribute('class','form-horizontal');
	
        $this->add(array( 
            'name' => 'fname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'inputEmail1', 
                'placeholder' => 'Name',
		'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));        
        
        
	$this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'inputEmail3', 
                'placeholder' => 'E-mail',
		'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));		
	$this->add(array( 
            'name' => 'password', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array(
                'id' => 'inputPassword1',
                'placeholder' => 'Password',               
		'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));  
        $this->add(array( 
            'name' => 'confirmpassword', 
            'type' => 'Zend\Form\Element\Password', 
            'attributes' => array(
                'id' => 'inputPassword2',
                'placeholder' => 'Confirm Password',               
		'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));
	$this->add(array(
             'name' => 'signup',
             'type' => 'Submit',
             'attributes' => array(                 
                 'value' => 'Sign Up',
                 'id' => 'submitbutton',
                 'class' => 'btn btn-info btn-lg round-14'
             ),
        ));
    }
}

