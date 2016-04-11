<?php

namespace Application\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class BasicInfoForm extends Form
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
 
        parent::__construct('userbasicinfo', $options);
        	
		$this->setAttribute('method', 'post')
        ->setHydrator(new ClassMethods())
        ->setInputFilter(new InputFilter());
		
		$this->add(array( 
            'name' => 'firstname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'firstname', 
                'placeholder' => 'First Name',
				'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));		
		$this->add(array( 
            'name' => 'lastname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
				'id' => 'lastname', 
                'placeholder' => 'Last Name',               
				'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
                $this->add(array( 
                'name' => 'email', 
                'type' => 'Zend\Form\Element\Text', 
                'attributes' => array( 
                                    'id' => 'email', 
                                    'readonly' => 'readonly',                
                                    'class'=>'form-control',
                ), 
                'options' => array( 
                ), 
            )); 
                
		$this->add(array( 
            'name' => 'contactno', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
				'id' => 'contactno', 
                'placeholder' => 'Contact Number',               
				'class'=>'form-control',
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
                 'class' => 'btn btn-blue saveaddridt'
             ),
        ));
    }
}