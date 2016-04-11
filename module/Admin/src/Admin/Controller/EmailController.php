<?php
/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for Manage Email Template.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mvc\MvcEvent;

class EmailController extends AbstractActionController{
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
    /* This function is used for Email Templates Listing.
     * @author Aditya 
     */
    public function indexAction()
    {
        $this->layout()->pageTitle = 'Email Templates Listing'; /* Setting page title */       
        return new ViewModel();         
    }
    /**
     *  This function is used for Fetch all Email Templates.
     *  @author Aditya
     */
    public function emaillistingdataAction(){
        $this->layout()->pageTitle = 'Email Listing'; /* Setting page title */               
        $em = $this->getEntityManager();        
        $request = $this->getRequest();                 
        $basePath = $this->getRequest()->getBasePath();	
        $editUrl = $url = $this->url()->fromRoute('editemail');		        
        $sqlArr['searchKey'] =  $request->getQuery('sSearch');
        $sqlArr['sortcolumn'] =  $request->getQuery('iSortCol_0');        
        $sqlArr['sorttype'] =  $request->getQuery('sSortDir_0');    // desc or asc 
        $sqlArr['iDisplayStart'] =  $request->getQuery('iDisplayStart');  // offset
        $sqlArr['sEcho'] =  $request->getQuery('sEcho');
        $sqlArr['limit'] =  $request->getQuery('iDisplayLength'); 	
        $basePath = $this->getRequest()->getBasePath();
        $emailData   = $em->getRepository('Admin\Entity\Email')->getEmailListing($sqlArr, $editUrl, $basePath);              
        echo json_encode($emailData);
        exit();
    }
    
    /**
     * This function is used for Edit Email Template.
     * @param emailtemplateid
     * @return type
     * @author Aditya
     */
    public function editemailAction(){
        $emailid = $this->getEvent()->getRouteMatch()->getParam('emailid');               
        $this->layout()->pageTitle = 'Edit Email Template'; /* Setting page title */
        $em = $this->getEntityManager();                
        $emailData = $em->getRepository('Admin\Entity\Email')->getEmailById($emailid);                               
        $form = new AdminForms\EditemailForm();
        $msg = '';        
        if(!empty($emailData)){            
            $form->get('emailsub')->setValue( $emailData[0]['subject']);
            $form->get('emailcontent')->setValue( $emailData[0]['content']);          
            $form->get('submitbutton')->setValue('Update');
            $request = $this->getRequest();
            if ($request->isPost()) {                            
                $formData = $request->getPost();			                	                
                $formValidator = new AdminForms\Validator\EditemailFormValidator();	
                $form->setInputFilter($formValidator->getInputFilter());                                
                $form->setData($request->getPost()); 
                if ($form->isValid()) {
                    $currentDate = date_create(date('Y-m-d H:i:s'));                            
                    $emailObj = $em->getRepository('\Admin\Entity\Email')->find($emailid);
                    $emailObj->setSubject($formData['emailsub']);
                    $emailObj->setContent($formData['emailcontent']);
                    $emailObj->setModifiedDate($currentDate);                    
                    $em->persist($emailObj);
                    $em->flush();                    
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');                
                    $msg = "Email Template has been updated successfully";                
                    $flashMessenger->addMessage($msg);                
                    return $this->redirect()->toRoute('emailindex');
                }
            }
        }               
        return array('form' => $form,'success'=>$msg,'emailid'=>$emailid);
    }    
    
}
?>
   