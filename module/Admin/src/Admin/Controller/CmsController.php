<?php
/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for Manage CMS.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mvc\MvcEvent;

class CmsController extends AbstractActionController{
    protected $em;
    protected $authservice;
	
    public function onDispatch(MvcEvent $e)
    {
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if(empty($username)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('adminlogin');
        }
        
        /* Set Default layout for all the actions */
        $this->layout('layout/adminlayout');
        
        return parent::onDispatch($e);
    }
	
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->em;
    }
    
    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }
         
        return $this->authservice;
    }
    /* Cms Listing page
     * @author Aditya 
     */
    public function indexAction()
    {   
        $this->layout()->pageTitle = 'CMS Listing'; /* Setting page title */       
        return new ViewModel();        
    }
    
    /**
     * This function is used for add CMS.
     * @return type
     * @author Aditya
     */
    public function addAction(){
        $this->layout()->pageTitle = 'Add CMS'; /* Setting page title */       
        $msg = "";        
        $basePath = $this->getRequest()->getBasePath();
        $tinymce_url = $basePath."/assets/admin/scripts/vendor/tinymce/tinymce.min.js";        
        //$this->view->headScript()->appendFile($tinymce_url);
        $form = new AdminForms\AddcmsForm();
        $form->get('submitbutton')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {                 
            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $formData = $request->getPost();            
            $cms_title = $formData['cmstitle'];	
            $cms_desc = $formData['cmsdesc'];
            $cms_keywords = $formData['keywords'];
            $cms_metatag = $formData['metatag'];
            $cms_metadesc = $formData['metadesc'];
            
            $formValidator = new AdminForms\Validator\AddCmsFormValidator();	
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($request->getPost()); /* set post data to form */            
            if ($form->isValid()) { 
                $currentDate = date_create(date('Y-m-d H:i:s'));                            
                $cmsObj = new Entities\Cms();
                $cmsObj->setPageTitle($cms_title);
                $cmsObj->setContent($cms_desc);
                $cmsObj->setKeywords($cms_keywords);
                $cmsObj->setMetaTag($cms_metatag);
                $cmsObj->setMetaDesc($cms_metadesc);
                $cmsObj->setCreatedDate($currentDate);
                $cmsObj->setModifiedDate($currentDate);
                $cmsObj->setStatus(1);                                
                $em->persist($cmsObj);
                $em->flush();                         
                $form->get('cmstitle')->setValue('');
                $form->get('cmsdesc')->setValue('');
                $form->get('keywords')->setValue('');
                $form->get('metatag')->setValue('');
                $form->get('metadesc')->setValue('');                
                
                $flashMessenger = $this->flashMessenger();
                $flashMessenger->setNamespace('success');                
                $msg = "Category has been added successfully";                
                //$flashMessenger->addMessage($msg);                 
                $flashMessenger->addMessage($msg);                
                return $this->redirect()->toRoute('cmsindex');
            }
        }
        return array('form' => $form,'success'=>$msg,'tinymce'=>$tinymce_url);	      
    }
    
    /**
     * This function is used for Edit Cms.
     * @param $cmsid
     * @return mixed
     * @author Aditya
     */
    public function editAction(){  
        $cmsid = $this->getEvent()->getRouteMatch()->getParam('cmsid');               
        $this->layout()->pageTitle = 'Edit CMS'; /* Setting page title */
        $em = $this->getEntityManager();                
        $cmsData = $em->getRepository('Admin\Entity\Cms')->getCmsById($cmsid);                       
        $form = new AdminForms\EditCmsForm();
        $msg = '';
        if(!empty($cmsData)){            
            $form->get('cmstitle')->setValue( $cmsData[0]['pageTitle']);
            $form->get('cmsdesc')->setValue( $cmsData[0]['content']);                        
            $form->get('keywords')->setValue($cmsData[0]['keywords']);
            $form->get('metatag')->setValue($cmsData[0]['metaTag']);
            $form->get('metadesc')->setValue($cmsData[0]['metaDesc']);
            $form->get('submitbutton')->setValue('Update');
            $request = $this->getRequest();
            if ($request->isPost()) {                            
                $formData = $request->getPost();			                	                
                $formValidator = new AdminForms\Validator\EditCmsFormValidator();	
                $form->setInputFilter($formValidator->getInputFilter());                
                $form->setData($request->getPost()); /* set post data to form */
                if ($form->isValid()) {
                    $currentDate = date_create(date('Y-m-d H:i:s'));                            
                    $cmsObj = $em->getRepository('\Admin\Entity\Cms')->find($cmsid);
                    $cmsObj->setPageTitle($formData['cmstitle']);
                    $cmsObj->setContent($formData['cmsdesc']);
                    $cmsObj->setKeywords($formData['keywords']);
                    $cmsObj->setMetaTag($formData['metatag']);
                    $cmsObj->setMetaDesc($formData['metadesc']);                    
                    $cmsObj->setModifiedDate($currentDate);
                    $cmsObj->setStatus(1);                                
                    $em->persist($cmsObj);
                    $em->flush();                    
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');                
                    $msg = "CMS has been updated successfully";                
                    $flashMessenger->addMessage($msg);                
                    return $this->redirect()->toRoute('cmsindex');
                }
            }
        }        
        
        return array('form' => $form,'success'=>$msg,'cmsid'=>$cmsid);	        
    }    
    
    /**
     * This function is used for listing of CMS.
     * @author Aditya
     */
    public function cmslistingAction(){  
        $this->layout()->pageTitle = 'CMS Listing'; /* Setting page title */       
        
        $em = $this->getEntityManager();
        
        $request = $this->getRequest();                 
        $basePath = $this->getRequest()->getBasePath();	
        $editUrl = $url = $this->url()->fromRoute('editcms');		
        	
        $sqlArr['searchKey'] =  $request->getQuery('sSearch');
        $sqlArr['sortcolumn'] =  $request->getQuery('iSortCol_0');        
        $sqlArr['sorttype'] =  $request->getQuery('sSortDir_0');    // desc or asc 
        $sqlArr['iDisplayStart'] =  $request->getQuery('iDisplayStart');  // offset
        $sqlArr['sEcho'] =  $request->getQuery('sEcho');
        $sqlArr['limit'] =  $request->getQuery('iDisplayLength'); 	
        $basePath = $this->getRequest()->getBasePath();
        $cmsData   = $em->getRepository('Admin\Entity\Cms')->getCmsListing($sqlArr, $editUrl, $basePath);              
        echo json_encode($cmsData);
        exit();
    } 
    
}
?>
