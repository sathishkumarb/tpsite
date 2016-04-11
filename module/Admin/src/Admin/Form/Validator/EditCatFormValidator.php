<?php

namespace Admin\Form\Validator;

use Zend\InputFilter\Factory as InputFactory; 
use Zend\InputFilter\InputFilter; 
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterAwareInterface; 
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator as Validator;
use Zend\Validator\File\Extension;

class EditCatFormValidator implements InputFilterAwareInterface{
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
                'name' => 'catname', 
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
                            'max' => '50', 
                        ), 
                    ), 
                ), 
            ]));
            //$inputFilter = new InputFilter();
            // File Input
            //$fileInput = new FileInput('fileupload');            
            //$inputFilter->add($fileInput);
            /*$inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'fileupload',
                    'required' => true,
                ))
            );*/
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    } 
}

?>
