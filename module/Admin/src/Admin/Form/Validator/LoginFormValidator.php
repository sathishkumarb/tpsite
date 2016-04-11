<?php
namespace Admin\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;
use Zend\Validator\EmailAddress;
class LoginFormValidator implements InputFilterAwareInterface 
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
                'name' => 'email', 
                'required' => true, 
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
                            'max' => '300', 
                        ), 
                    ),
                    array(
                        'name'    => 'EmailAddress',
                        'options' => array(                                
                            'messages' =>array(
                                EmailAddress::INVALID_FORMAT => "Enter Valid Email",
                                EmailAddress::INVALID_HOSTNAME =>"Enter Valid Email",
                                EmailAddress::INVALID =>"Enter Valid Email",                             
                            ),
                        ),
                    ),
                ), 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'password', 
                'required' => true, 
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
                            'max' => '300', 
                        ), 
                    ), 
                ), 
            ]));
            
            $this->inputFilter = $inputFilter;            
        }        
        return $this->inputFilter;
    }
    
}