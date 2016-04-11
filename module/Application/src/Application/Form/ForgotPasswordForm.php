<?php

namespace Application\Form;

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
        
        $this->setAttribute('class','form-horizontal');
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
             'name' => 'forgotsubmit',
             'type' => 'Submit',
             'attributes' => array(                 
                 'value' => 'Submit',
                 'id' => 'forgotsubmit',
                 'class' => 'btn btn-info btn-lg round-14'
             ),
        ));
    }
}

