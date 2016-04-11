<?php

namespace Application\Form;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CheckoutForm extends Form
{
    /**
     * @var EntityManager
     */
    protected $em;    
	
    /**
     * @var FilterPluginManager
     */
    protected $fpm;
 
    
    public function __construct($userSavedCards, ObjectManager $em, $country=null,$basepath = "")	
    {	
        $this->setObjectManager($em);
        $visa_str = '<img src="'.$basepath.'assets/frontend/icons/icon-visa.png" />';
        $mastercard_str = '<img src="'.$basepath.'/assets/frontend/icons/img-master-card.png" />';
        $amex_str = '<img src="'.$basepath.'/assets/frontend/icons/img-american-express.png" />';
        $maestro_str = '<img src="'.$basepath.'/assets/frontend/icons/img-maestro.png" />';
        
        $curr_year = date("Y");
        $endyear = $curr_year+10;
        parent::__construct('checkoutform');        	
        $this->setAttribute('method', 'post')
            ->setHydrator(new ClassMethods())
            ->setInputFilter(new InputFilter());
        
        $this->add(array( 
            'name' => 'quantity', 
            'type' => 'Zend\Form\Element\Number', 
            'attributes' => array( 
                'id' => 'quantity', 
                'placeholder' => '',
                'class'=>'form-control valid',
                'readonly'=>'readonly'
            ), 
            'options' => array( 
            ), 
        ));	
        
        $this->add(array( 
            'name' => 'email', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'email', 
                'placeholder' => 'Email',
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));	
        
        $this->add(array( 
            'name' => 'phoneno', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'phoneno', 
                'placeholder' => '+971 55xxxxxxx',
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));	
        
        $this->add(array(
            'name' => 'saved_cards',
            'type' => 'select',                    
            'attributes' => array( 
                'id' => 'saved_cards',
                'class'=>'form-control',
            ),
            'options' => array(
                'value_options' => $this->getSavedCards($userSavedCards),
                'empty_option'  => 'Choose any Saved Card or new Card for Checkout',
                'disable_inarray_validator' => true
            )
        ));
        
        $this->add(array(
            'name' => 'card_type',
            'type' => 'radio',                    
            'attributes' => array( 
                'id' => 'card_type',
                "disabled"=>"disabled"
            ),
              'options' => array(
                  'value_options' => array("visa"=>$visa_str,"mastercard"=>$mastercard_str,"amex"=>$amex_str,"maestro"=>$maestro_str),
              )
        ));
        
        $this->add(array( 
            'name' => 'cardno', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'cardno',
                'placeholder' => 'Card Number',
                "disabled"=>"disabled",
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
    
        $this->add(array( 
            'name' => 'securityno', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'securityno',
                'placeholder' => 'Security Code',               
                'class'=>'form-control',
                "disabled"=>"disabled"
            ), 
            'options' => array( 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'month', 
            'type' => 'Zend\Form\Element\Number', 
            'attributes' => array( 
                'id' => 'month',                     
                'class'=>'form-control input-lg month',
                'placeholder'=>'MM',
                'min' => '0',
                'max' => '12',
                'step' => '1',
                "disabled"=>"disabled"
            ), 
            'options' => array( 
            ), 
        )); 
                
        $this->add(array( 
            'name' => 'year', 
            'type' => 'Zend\Form\Element\Number', 
            'attributes' => array( 
                'id' => 'year',                     
                'class'=>'form-control input-lg year',
                'placeholder'=>'YYYY',
                'min' => $curr_year,
                'max' => $endyear,
                'step' => '1',
                "disabled"=>"disabled"
            ), 
            'options' => array( 
            ), 
        )); 
        
        $this->add(array( 
            'name' => 'firstname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'firstname', 
                'placeholder' => 'First Name',
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        ));		
        
        $this->add(array( 
            'name' => 'lastname', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'lastname',
                'placeholder' => 'Last Name',               
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
       
        $this->add(array( 
            'name' => 'streetaddress', 
            'type' => 'Zend\Form\Element\Text', 
            'attributes' => array( 
                'id' => 'streetaddress',
                'placeholder' => 'Street Address',
                'class'=>'form-control',
            ), 
            'options' => array( 
            ), 
        )); 
                
        $this->add(array(
            'name' => 'city',
            'type' => 'Select',                    
            'attributes' => array( 
                'id' => 'city',                                         
                'class'=>'form-control',
            ),
            'options' => array(
                'value_options' => $this->getCityList($country),
                'empty_option'  => '--- Select City ---',
                'disable_inarray_validator' => true
            )
        ));  
        
        $this->add(array(
            'name' => 'country',
            'type' => 'Select',                    
            'attributes' => array( 
                'id' => 'country',                                         
                'class'=>'form-control',
                'onChange' => 'getCity(this.value);'
            ),
            'options' => array(
                'value_options' => $this->getCountriesList(),
                'empty_option'  => '--- Select Country ---',
                'disable_inarray_validator' => true
            )
        ));
        
        $this->add(array( 
            'name' => 'box-chk', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
                'id' => 'box-chk'                   
            ), 
            'options' => array( 
            ), 
        )); 

        $this->add(array( 
            'name' => 'cash-box-chk', 
            'type' => 'Zend\Form\Element\Checkbox', 
            'attributes' => array( 
                'id' => 'cash-box-chk'                   
            ), 
            'options' => array( 
            ), 
        )); 
        
        $this->add(array(
            'name' => 'savebtn',
            'type' => 'Submit',
            'attributes' => array(                 
                'value' => 'Continue',
                'id' => 'savebtn',
                'class' => 'btn-blue'
            ),
       ));
    }
    
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }
    public function setObjectManager(ObjectManager $em)
    {
        $this->em = $em;
 
        return $this;
    }
    public function getObjectManager()
    {
        return $this->em;
    }
    public function getCountriesList()
    {
        $sm = $this->getObjectManager();        
        $result = $sm->getRepository('Admin\Entity\Countries')->findBy(array('countryExist'=>1));
        $selectData = array(); 
        foreach ($result as $val) {
            $selectData[$val->getid()] = $val->getCountryName();
        }
        return $selectData;
    }
    public function getCityList($country){
        $sm = $this->getObjectManager();        
        $result = $sm->getRepository('Admin\Entity\City')->findBy(array('countryId'=>$country));
        $selectData = array(); 
        foreach ($result as $val) {
            $selectData[$val->getid()] = $val->getCityName();
        }
        return $selectData;
    }
    public function getSavedCards($userSavedCards){
        $savedCards = array();
        $savedCards['0'] = "New Card For Checkout";
        if(!empty($userSavedCards))
        {
            foreach($userSavedCards as $card){
                $savedCards[$card->getId()] = $card->getTitle();
            }
        }
        return $savedCards;
    }
}