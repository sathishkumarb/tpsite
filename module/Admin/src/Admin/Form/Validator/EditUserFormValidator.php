<?php

namespace Admin\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;

class EditUserFormValidator implements InputFilterAwareInterface{
    protected $inputFilter; 
    public function exchangeArray($data)
    {
        $this->profilename  = (isset($data['profilename']))  ? $data['profilename']     : null;         
    } 
    public function setInputFilter(InputFilterInterface $inputFilter) 
    { 
        throw new \Exception("Not used"); 
    }
    
    public function getInputFilter(){
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
                            'max' => '100', 
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
                            'max' => '100', 
                        ), 
                    ), 
                ), 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'phone',                 
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
                                'max' => '14', 
                            ),
                        ),
                    array ( 
                        'name' => 'Int',                            
                        ), 
                ), 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'country',                 
                'required'=> false,
                'filters' => array( 
                    array('name' => 'Int'),
                ),                 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'city',                 
                'required'=> false,
                'filters' => array( 
                   array('name' => 'Int'),
                ),                 
            ]));
            
            $inputFilter->add($factory->createInput([ 
                'name' => 'zip', 
                'required'=> false,
                'filters' => array( 
                    array('name' => 'StripTags'), 
                    array('name' => 'StringTrim'), 
                ),                 
                'validators' => array( 
                    array ( 
                        'name' => 'Int', 
                        'options' => array( 
                            'encoding' => 'UTF-8', 
                            'min' => '5', 
                            'max' => '6', 
                            
                        ), 
                    ), 
                ), 
            ]));
            
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}
            
?>