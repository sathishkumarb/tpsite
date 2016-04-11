<?php 
namespace Application\Form;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class UserLocationForm extends Form implements ObjectManagerAwareInterface{
    protected $em;  
    
    public function __construct(ObjectManager $em, $country=null)
    {   
        
        $this->setObjectManager($em);
        // we want to ignore the name passed
        parent::__construct('userlocation');
        $this->setAttribute('method', 'post');                                
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
                'name' => 'submitbutton',
                'type' => 'Submit',                
                'attributes' => array(
                    'value' => 'Save',
                    'id' => 'submitbutton',
                    'class' => 'btn btn-blue'
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
}

?>
