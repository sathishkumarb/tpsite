<?php

namespace Admin\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;

class AdminSettingsForm extends Form
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
 
        parent::__construct('adminsettings', $options);
        	
	$this->setAttribute('method', 'post')
             ->setHydrator(new ClassMethods())
             ->setInputFilter(new InputFilter());
		
	$this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                                'id' => 'email',                                 
                                'class'=>'form-control',
                            ), 
            'options' => array( 
                            ), 
            ));	
        $this->add(array( 
            'name' => 'supportemail', 
            'type' => 'Zend\Form\Element\Email', 
            'attributes' => array( 
                                'id' => 'supportemail',                                 
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
                 'class' => 'btn btn-default saveaddridt'
             ),
        ));
    }
}

