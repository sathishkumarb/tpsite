<?php
/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for common ajax function.
 */
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;

class CommonController extends AbstractActionController {

    protected $em;
    protected $authservice;

    /* This function is like old init() */

    public function __construct() {
        /* Set Default layout for all the actions */
        $this->layout('layout/adminlayout');
    }

    public function setEntityManager(EntityManager $em) {
        $this->em = $em;
    }

    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()
                    ->get('AuthService');
        }

        return $this->authservice;
    }

    public function indexAction() {
        return new ViewModel();
    }

    /**
     * This function is used for get state based on country id. 
     * @Author Vinod Kandwal
     */
    public function cityAction() {
        $request = $this->getRequest();
        $res = '<option value="">Select City</option>';
        if ($request->isPost()) {
            $postdata = $request->getPost();
            $country_id = $postdata['country_id'];
            if ($country_id != '') {
                $em = $this->getEntityManager(); /* Call Entity Manager */
                $objCity = $em->getRepository('Admin\Entity\City')->findBy(array('countryId' => $country_id));
                
                if (count($objCity) > 0) {
                    foreach ($objCity as $data) {
                        $cityname = $data->getCityName();
                        $cityId = $data->getId();
                        $res .= '<option value="' . $cityId . '">' . $cityname . '</option>';
                    }
                }
            }
        }
        echo $res;
        exit;
    }

    /**
     * This function is used for get layout based on layout_id. 
     * @Author Vinod Kandwal
     */
    public function getlayoutAction() {
        $request = $this->getRequest();  /* Fetching Request */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $url = $this->getRequest()->getBasePath();

        $response = 0;
        /* Check with passed password exist in DB */
        if ($request->isPost()) {
            $postdata = $request->getPost();
            $layout_id = $postdata['layout_id'];
            $objLayout = $em->getRepository('Admin\Entity\Layout')->find($layout_id);
            if (!empty($objLayout)) {
                $layoutPath = $objLayout->getLayoutImage();
                $response = '<img id="map" src="' . $url . '/uploads/layout/' . $layoutPath . '" alt="" style="margin: 0; border: none; position: absolute; top: 0; left: 0; z-index: 500">';
            }
        }
        echo $response;
        exit;
    }

}
