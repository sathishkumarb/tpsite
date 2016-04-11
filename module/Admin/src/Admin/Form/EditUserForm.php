<?php

namespace Admin\Form;

use Zend\Form\Form;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EditUserForm extends Form implements ObjectManagerAwareInterface {

    protected $em;

    public function __construct(ObjectManager $em, $country) {

        $this->setObjectManager($em);
        // we want to ignore the name passed
        parent::__construct('edituser');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'firstname',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'firsttitle',
                'placeholder' => 'First Name',
                'class' => 'form-control input-lg',
            )
        ));
        $this->add(array(
            'name' => 'lastname',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'lasttitle',
                'placeholder' => 'Last Name',
                'class' => 'form-control input-lg',
            )
        ));
        $this->add(array(
            'name' => 'phone',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'phone',
                'placeholder' => 'Phone',
                'class' => 'form-control input-lg',
            )
        ));
        //Added by Yesh
        $this->add(array(
            'name' => 'roles',
            'type' => 'Select',
            'attributes' => array(
                'id' => 'country',
                'class' => 'form-control',
            ),
            'options' => array(
                'value_options' => $this->getUserRoles(),
                'empty_option' => '--- Select Role ---',
                'disable_inarray_validator' => true
            )
        ));
        $this->add(array(
            'name' => 'country',
            'type' => 'Select',
            'attributes' => array(
                'id' => 'country',
                'class' => 'form-control',
                'onChange' => 'getCity(this.value);'
            ),
            'options' => array(
                'value_options' => $this->getCountriesList(),
                'empty_option' => '--- Select Country ---',
                'disable_inarray_validator' => true
            )
        ));
        $this->add(array(
            'name' => 'city',
            'type' => 'Select',
            'attributes' => array(
                'id' => 'city',
                'class' => 'form-control',
            ),
            'options' => array(
                'value_options' => $this->getCityList($country),
                'empty_option' => '--- Select City ---',
                'disable_inarray_validator' => true
            )
        ));
        $this->add(array(
            'name' => 'zip',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'zip',
                'placeholder' => 'ZIP Code',
                'class' => 'form-control input-lg',
            )
        ));
        $this->add(array(
            'name' => 'submitbutton',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
                'id' => 'submitbutton',
                'class' => 'btn btn-default saveaddridt paidtridt'
            ),
        ));
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function setObjectManager(ObjectManager $em) {
        $this->em = $em;

        return $this;
    }

    public function getObjectManager() {
        return $this->em;
    }

    /**
     * added by Yesh
     * get user roles 
     * @return type array
     */
    public function getUserRoles() {
        $om = $this->getObjectManager();
        $result = $om->getRepository('Admin\Entity\UserRole')->findBy(array('status' => 1));
        $selectData = array();
        foreach ($result as $val) {
            $selectData[$val->getId()] = $val->getRoleName();
        }
        return $selectData;
    }

    public function getCountriesList() {
        $sm = $this->getObjectManager();
        $result = $sm->getRepository('Admin\Entity\Countries')->findBy(array('countryExist' => 1));
        $selectData = array();
        foreach ($result as $val) {
            $selectData[$val->getid()] = $val->getCountryName();
        }
        return $selectData;
    }

    public function getCityList($country) {
        $sm = $this->getObjectManager();
        $result = $sm->getRepository('Admin\Entity\City')->findBy(array('countryId' => $country));
        $selectData = array();
        foreach ($result as $val) {
            $selectData[$val->getid()] = $val->getCityName();
        }
        return $selectData;
    }

}

?>
