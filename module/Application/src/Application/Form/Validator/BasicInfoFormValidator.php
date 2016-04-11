<?php
namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;
class BasicInfoFormValidator implements InputFilterAwareInterface 
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
                'name' => 'firstname',                 
                'required' => false,
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'StringLength', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min' => '2', 
                            'max' => '30', 
                        ), 
                    ), 
                ), 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'lastname',                 
                'required' => false, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'StringLength', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min' => '2', 
                            'max' => '30', 
                        ), 
                    ), 
                ), 
            ]));
			
            $inputFilter->add($factory->createInput([ 
                'name' => 'contactno', 
                'required' => false, 
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ), 
                'validators' => array( 
                    array ( 
                        'name' => 'StringLength', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min' => '10', 
                            'max' => '14', 
                        ), 
                    ),                    
                ), 
            ]));
            
            $this->inputFilter = $inputFilter;            
        }        
        return $this->inputFilter;
    }
    
}