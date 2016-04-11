<?php

namespace Admin\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;

class EditEmailFormValidator implements InputFilterAwareInterface{
    protected $inputFilter; 
    public function exchangeArray($data)
    {
        $this->profilename  = (isset($data['profilename']))  ? $data['profilename']     : null; 
        $this->fileupload  = (isset($data['fileupload']))  ? $data['fileupload']     : null; 
    } 
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
                'name' => 'emailsub', 
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
                            'max' => '100', 
                        ), 
                    ), 
                ), 
            ]));
            $inputFilter->add($factory->createInput([ 
                'name' => 'emailcontent', 
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
                            'max' => '5000', 
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
