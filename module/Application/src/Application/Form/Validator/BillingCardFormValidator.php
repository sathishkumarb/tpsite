<?php
namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;
class BillingCardFormValidator implements InputFilterAwareInterface 
{ 
    protected $inputFilter; 
    
    public function setInputFilter(InputFilterInterface $inputFilter) 
    { 
        throw new \Exception("Not used"); 
    }
    
    public function getInputFilter() 
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter(); 
            $factory = new InputFactory();
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'card_type',                 
                'required' => true,                                
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'cardno',                 
                'required' => true,                  
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),
            ]));
			
            $inputFilter->add($factory->createInput([ 
                'name' => 'title', 
                'required' => true,                 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'month', 
                'required' => true,                 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'year', 
                'required' => true,                 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),
            ]));
            
            $this->inputFilter = $inputFilter;            
        }        
        return $this->inputFilter;
    }
    
}