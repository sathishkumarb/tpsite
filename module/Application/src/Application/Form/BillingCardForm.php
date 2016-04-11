<?php

namespace Application\Form;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BillingCardForm extends Form
{
    /**
     * @var EntityManager
     */
    protected $em;    
	
    /**
     * @var FilterPluginManager
     */
    protected $fpm;
 
    
    public function __construct($basepath = "")	
    {	
        $visa_str = '<img src="'.$basepath.'assets/frontend/icons/icon-visa.png" />';
        $mastercard_str = '<img src="'.$basepath.'/assets/frontend/icons/img-master-card.png" />';
        $amex_str = '<img src="'.$basepath.'/assets/frontend/icons/img-american-express.png" />';
        $maestro_str = '<img src="'.$basepath.'/assets/frontend/icons/img-maestro.png" />';
        
        $curr_year = date("Y");
        $endyear = $curr_year+10;
        parent::__construct('usercardinfo');        	
        $this->setAttribute('method', 'post')
            ->setHydrator(new ClassMethods())
            ->setInputFilter(new InputFilter());

        $this->add(array(
            'name' => 'card_type',
            'type' => 'radio',                    
            'attributes' => array( 
                'id' => 'cardtype'                
            ),
              'options' => array(
                  'value_options' => array("visa"=>$visa_str,"mastercard"=>$mastercard_str,"maestro"=>$maestro_str,"amex"=>$amex_str),
              )
        ));
        
        $this->add(array( 
            'name' => 'cardno', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'cardno',
                'placeholder' => 'Card Number',               
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'title', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'title',
                'placeholder' => 'Title',               
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'month', 
            'type' => 'Zend\Form\Element\Number', 
            'attributes' => array( 
                'id' => 'month',                     
                'class'=>'form-control',
                'placeholder'=>"MM",
                'min' => '0',
                'max' => '12',
                'step' => '1',
            ), 
            'options' => array( 
            ), 
        )); 
                
        $this->add(array( 
            'name' => 'year', 
            'type' => 'Zend\Form\Element\Number', 
            'attributes' => array( 
                'id' => 'year',                     
                'class'=>'form-control',
                 'placeholder'=>"YYYY",
                'min' => $curr_year,
                'max' => $endyear,
                'step' => '1',
            ), 
            'options' => array( 
            ), 
        )); 
        
        
        $this->add(array(
            'name' => 'savebtn',
            'type' => 'Submit',
            'attributes' => array(                 
                'value' => 'Add Card',
                'id' => 'savebtn',
                'class' => 'btn-blue'
            ),
       ));
    }   
}