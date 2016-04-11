<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for Manage Category.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Validator\File\Size;
use Zend\Validator\File\Extension;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mvc\MvcEvent;

class CategoryController extends AbstractActionController {

    protected $em;
    protected $authservice;

    public function onDispatch(MvcEvent $e) {
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if (empty($username)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('adminlogin');
        }

        /* Set Default layout for all the actions */
        $this->layout('layout/adminlayout');

        return parent::onDispatch($e);
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

    /**
     * This function is used for list of all categories.
     * @return type
     * @author Aditya
     */
    public function indexAction() {
        $this->layout()->pageTitle = 'Category Listing'; /* Setting page title */
        $view = new ViewModel();
        return $view;
    }

    /**
     * This function is used for get all categories in grid.
     * @return type
     * @author Aditya
     */
    public function categorylistingdataAction() {
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $basePath = $this->getRequest()->getBasePath();
        $editUrl = $url = $this->url()->fromRoute('editcategory');
        //$viewUrl = $url = $this->url()->fromRoute('viewcategory');		
        $sqlArr['searchKey'] = $request->getQuery('sSearch');
        $sqlArr['sortcolumn'] = $request->getQuery('iSortCol_0');
        $sqlArr['sorttype'] = $request->getQuery('sSortDir_0');    // desc or asc 
        $sqlArr['iDisplayStart'] = $request->getQuery('iDisplayStart');  // offset
        $sqlArr['sEcho'] = $request->getQuery('sEcho');
        $sqlArr['limit'] = $request->getQuery('iDisplayLength');
        $basePath = $this->getRequest()->getBasePath();
        $userData = $em->getRepository('Admin\Entity\Categories')->getCategoryListing($sqlArr, $editUrl, $basePath);
        echo json_encode($userData);
        exit();
    }

    /**
     * This function is used to add Category.     
     * @author Aditya
     */
    public function addAction() {
        $this->layout()->pageTitle = 'Add Category'; /* Setting page title */
        $msg = "";
        $form = new AdminForms\AddcatForm();
        $form->get('submitbutton')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $formData = $request->getPost();
            $catname = $formData['catname'];
            $formValidator = new AdminForms\Validator\AddCatFormValidator();
            $form->setInputFilter($formValidator->getInputFilter());
            $postData = array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray());
            $form->setData($postData);
            if ($form->isValid()) {
                if ($postData['fileupload']['name'] != '') {
                    /* Check File Extension */
                    $adapter = new \Zend\File\Transfer\Adapter\Http();
                    $validatorSize = new \Zend\Validator\File\Size(1000000);
                    $validatorExt = new \Zend\Validator\File\Extension('jpg,jpeg,png,gif,PNG');

                    $results = array();
                    $results['size'] = $validatorSize->isValid($postData['fileupload']);
                    $results['ext'] = $validatorExt->isValid($postData['fileupload']);

                    $adapter->addValidators(array(
                        $validatorSize,
                        $validatorExt,
                            ), false, $postData['fileupload']);
                    $adapter->addValidator($validatorSize, false, $postData['fileupload']);
                    $adapter->addValidator($validatorExt, false, $postData['fileupload']);
                    $results['adapter'] = $adapter->isValid();
                    $size_valid = $results['size'];
                    $ext_valid = $results['ext'];
                    $error = '';
                    $flag = 1;
                    if ($size_valid == "") {
                        $error .= "Kindly upload image less than 1MB<br />";
                        $flag = 0;
                    }
                    if ($ext_valid == "") {
                        $error .= "Only jpg,gif,png files are allowed<br />";
                        $flag = 0;
                    }
                    if ($flag != 1) {
                        $flashMessenger = $this->flashMessenger();
                        $flashMessenger->setNamespace('error');
                        $msg = "Invalid file extension. Kindly upload only gif, jpg, bmp files";
                        return array('form' => $form, 'error' => $error);
                    }

                    $uploadsDir = getcwd() . '/public/uploads';
                    if (!file_exists($uploadsDir)) {
                        mkdir(($uploadsDir), 0777, true);
                    }
                    $uploadsDirPath = getcwd() . '/public/uploads/category';
                    if (!file_exists($uploadsDirPath)) {
                        mkdir(($uploadsDirPath), 0777, true);
                    }
                    $adapter = new \Zend\File\Transfer\Adapter\Http();
                    $adapter->setDestination($uploadsDirPath);
                    $destination = $adapter->getDestination();
                    $newFileName = date('dmyHis') . str_replace(" ", "", $postData['fileupload']['name']);
                    $adapter->addFilter('File\Rename', array(
                        'target' => $destination . '/' . $newFileName,
                        'overwrite' => true
                    ));
                    if ($adapter->receive($postData['fileupload']['name'])) {
                        $file = $adapter->getFilter('File\Rename')->getFile();
                    }
                }
                //$form->setInputFilter($profile->getInputFilter());
                $currentDate = date_create(date('Y-m-d H:i:s'));
                $catObj = new Entities\Categories();
                $catObj->setCategoryName($catname);
                $catObj->setCreatedDate($currentDate);
                $catObj->setModifiedDate($currentDate);
                $catObj->setStatus(1);
                $catObj->setIcon($newFileName);
                $catObj->setIsDeleted(0);
                $em->persist($catObj);
                $em->flush();
                $form->get('catname')->setValue('');
                $form->get('fileupload')->setValue('');
                $flashMessenger = $this->flashMessenger();
                $flashMessenger->setNamespace('success');
                $msg = "Category has been added successfully";
                $flashMessenger->addMessage($msg);

                return $this->redirect()->toRoute('categoryindex');
                //$flashMessenger->addMessage($msg);                 
            } else {
                print_r($form->getMessages());
            }
        }
        return array('form' => $form, 'success' => $msg);
    }

    /**
     * This function is used to delete category
     * @author Aditya
     */
    public function deleteAction() {
        $catid = $this->getEvent()->getRouteMatch()->getParam('catid');
        $em = $this->getEntityManager();
        $catObj = $em->getRepository('Admin\Entity\Categories')->findOneBy(array('id' => $catid));
        if ($catObj) {
            $catObj->setStatus(0);
            $em->flush();
            echo 1;
            exit();
        } else {
            echo 0;
            exit();
        }
    }

    /**
     * This function is used to edit Category.
     * @author Aditya
     */
    public function editAction() {
        $catid = $this->getEvent()->getRouteMatch()->getParam('catid');
        $this->layout()->pageTitle = 'Edit Category'; /* Setting page title */
        $em = $this->getEntityManager();
        $catData = $em->getRepository('Admin\Entity\Categories')->getCategoryById($catid);
        $form = new AdminForms\EditcatForm();
        $msg = "";
        $filename = "";
        $newFileName = "";
        if (!empty($catData)) {
            $form->get('catname')->setValue($catData[0]['categoryName']);
            $form->get('fileupload')->setValue($catData[0]['icon']);
            $form->get('submitbutton')->setValue('Update');
            $request = $this->getRequest();
            $filename = $catData[0]['icon'];
            if ($request->isPost()) {
                $formData = $request->getPost();
                $catname = $formData['catname'];
                $formValidator = new AdminForms\Validator\EditCatFormValidator();
                $form->setInputFilter($formValidator->getInputFilter());

                $postData = array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray());
                $form->setData($postData);
                if ($form->isValid()) {
                    if ($postData['fileupload']['name'] != '') {
                        $adapter = new \Zend\File\Transfer\Adapter\Http();
                        $validatorSize = new \Zend\Validator\File\Size(1000000);
                        $validatorExt = new \Zend\Validator\File\Extension('jpg,jpeg,png,PNG,gif');

                        $results = array();
                        $results['size'] = $validatorSize->isValid($postData['fileupload']);
                        $results['ext'] = $validatorExt->isValid($postData['fileupload']);

                        $adapter->addValidators(array(
                            $validatorSize,
                            $validatorExt,
                                ), false, $postData['fileupload']);
                        $adapter->addValidator($validatorSize, false, $postData['fileupload']);
                        $adapter->addValidator($validatorExt, false, $postData['fileupload']);
                        $results['adapter'] = $adapter->isValid();
                        $size_valid = $results['size'];
                        $ext_valid = $results['ext'];
                        $error = '';
                        $flag = 1;
                        if ($size_valid == "") {
                            $error .= "Kindly upload image less than 1MB<br />";
                            $flag = 0;
                        }
                        if ($ext_valid == "") {
                            $error .= "Only jpg,gif,png files are allowed<br />";
                            $flag = 0;
                        }
                        if ($flag != 1) {
                            $flashMessenger = $this->flashMessenger();
                            $flashMessenger->setNamespace('error');
                            $msg = "Invalid file extension. Kindly upload only gif, jpg, bmp files";
                            return array('form' => $form, 'error' => $error, 'catid' => $catid, 'imgpath' => $filename);
                        }
                        $uploadsDir = getcwd() . '/public/uploads';
                        if (!file_exists($uploadsDir)) {
                            mkdir(($uploadsDir), 0777, true);
                        }
                        $uploadsDirPath = getcwd() . '/public/uploads/category';
                        if (!file_exists($uploadsDirPath)) {
                            mkdir(($uploadsDirPath), 0777, true);
                        }
                        $adapter = new \Zend\File\Transfer\Adapter\Http();
                        $adapter->setDestination($uploadsDirPath);
                        $destination = $adapter->getDestination();
                        $newFileName = date('dmyHis') . str_replace(" ", "", $postData['fileupload']['name']);
                        $adapter->addFilter('File\Rename', array(
                            'target' => $destination . '/' . $newFileName,
                            'overwrite' => true
                        ));
                        $filename = $newFileName;
                        if ($adapter->receive($postData['fileupload']['name'])) {
                            $file = $adapter->getFilter('File\Rename')->getFile();
                        }
                    }
                    $currentDate = date_create(date('Y-m-d H:i:s'));
                    $catObj = $em->getRepository('\Admin\Entity\Categories')->find($catid);
                    $catObj->setCategoryName($catname);
                    $catObj->setModifiedDate($currentDate);
                    $catObj->setStatus(1);
                    $catObj->setIcon($filename);
                    $catObj->setIsDeleted(0);
                    $em->persist($catObj);
                    $em->flush();
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');
                    $msg = "Category has been updated successfully";
                    $flashMessenger->addMessage($msg);
                    return $this->redirect()->toRoute('categoryindex');
                }
            }
        }

        return array('form' => $form, 'success' => $msg, 'catid' => $catid, 'imgpath' => $filename);
    }

}

?>
