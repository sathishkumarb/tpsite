<?php
namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;
class ChangePasswordFormValidator implements InputFilterAwareInterface 
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
                'name' => 'oldpassword', 
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
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'newpassword', 
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
			
			$inputFilter->add($factory->createInput([ 
                'name' => 'confnewpass', 
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
					array ( 
                        'name' => 'Identical', 
                        'options' => array( 
                            'token' => 'newpassword',
							'messages' => array(
         \Zend\Validator\Identical::NOT_SAME => "New Password and Confirm Password should be same",
		 
     )
                        ), 
                    ), 
                ), 
            ]));
            
            $this->inputFilter = $inputFilter;            
        }        
        return $this->inputFilter;
    }
    
}