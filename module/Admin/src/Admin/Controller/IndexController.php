<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * This class is used for Login, Log out, Forgot Password and Change Password.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mail as Mail;
use Zend\Mvc\MvcEvent;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class IndexController extends AbstractActionController {

    protected $em;
    protected $authservice;

    /* This function is like old init() */

    public function onDispatch(MvcEvent $e) {
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

    public function indexAction() {
        return new ViewModel();
    }

    /**
     * This function is used to login admin.
     * @Author Manu Garg
     */
    public function loginAction() {
        $this->layout('layout/layout-login');
        /* Check if Admin is already logged-in */
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if (!empty($username)) {
            return $this->redirect()->toRoute('admindashboard');
        }
        $error = "";
        $form = new AdminForms\LoginForm(); // create object of login form
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isPost()) {
            /* If request method is Post */
            $formValidator = new AdminForms\Validator\LoginFormValidator();
            /* Login Form Validator Object */
            $form->setInputFilter($formValidator->getInputFilter()); /* Apply filter to form */
            $form->setData($request->getPost()); /* set post data to form */
            $data = $this->getRequest()->getPost(); /* Fetch Post Variables */
            // check if form is valid
            if ($form->isValid()) {
                $em = $this->getEntityManager(); /* Call Entity Manager */
                /* Verify Admin Credentials */
                $results = $em->getRepository('Admin\Entity\Users')->verifyAdmin($data);
                if (!empty($results)) {
                    if ($results[0]['isForgotStatus'] != 2) {
                        $admin_session = new Container('admin');
                        $admin_session->userId = $results[0]['id'];
                        $admin_session->username = $results[0]['username'];
                        $admin_session->userRole = $results[0]['userRole'];//added by Yesh
                        if ($results[0]['isForgotStatus'] == 1) {
                            $tmpObj = $em->getRepository('\Admin\Entity\Users')->find($results[0]['id']);
                            $tmpObj->setIsForgotStatus(2);
                            $em->persist($tmpObj);
                            $em->flush();
                            $flashMessenger = $this->flashMessenger();
                            $flashMessenger->setNamespace('success');
                            $flashMessenger->addMessage("Old Password is the OTP you received in your Email");
                            $url = $this->url()->fromRoute('changepassword');
                            return $this->redirect()->toRoute('changepassword');
                        }
                        $flashMessenger = $this->flashMessenger();
                        $flashMessenger->setNamespace('success');
                        $flashMessenger->addMessage("You have been logged in successfully.");
                        $url = $this->getRequest()->getHeader('Referer')->getUri();
                        $this->redirect()->toUrl($url);
                    } else {
                        $error = "Your OTP has expired. Kindly regenrate your password using Forgot Password Link";
                    }
                } else {
                    $error = 'Sorry! You have entered an incorrect email or password. Please enter correct login details to proceed';
                }
            }
        }
        return new ViewModel(array(
            'error' => $error,
            'form' => $form,
        ));
    }

    /**
     * This function is used to logout admin.
     * @Author Manu Garg
     */
    public function logoutAction() {
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if (!empty($username)) {
            $admin_session->getManager()->getStorage()->clear('admin');
        }
        return $this->redirect()->toRoute('adminlogin');
    }

    /**
     *  This function is used for Dashboard.
     *  @author Manu Garg
     */
    public function dashboardAction() {
        /* checking if user logged in or not */
        $this->checkAdminLoggedInOrNot();
        $this->layout('layout/adminlayout');

        $this->layout()->pageTitle = 'Dashboard'; /* Setting page title */
        return new ViewModel();
    }

    /**
     *  This function is used for change password.
     *  @author Manu Garg
     */
    public function changepasswordAction() {
        /* checking if user logged in or not */
        $this->checkAdminLoggedInOrNot();
        $this->layout()->pageTitle = 'Change Password'; /* Setting page title */

        $form = new AdminForms\ChangePasswordForm(); // create object of login form
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isPost()) {
            /* If request method is Post */
            $formValidator = new AdminForms\Validator\ChangePasswordFormValidator();
            /* Change Password Form Validator Object */
            $form->setInputFilter($formValidator->getInputFilter()); /* Apply filter to form */
            $form->setData($request->getPost()); /* set post data to form */
            $data = $this->getRequest()->getPost(); /* Fetch Post Variables */
            // check if form is valid
            if ($form->isValid()) {
                $em = $this->getEntityManager(); /* Call Entity Manager */

                /* Check with passed password exist in DB */
                $adminUserObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('password' => md5($data['oldpassword']), 'status' => 1, 'userType' => 'A'));

                if (!empty($adminUserObj)) {
                    /* Set new password */
                    $adminUserObj->setPassword(md5($data['newpassword']));
                    $currentDate = date('Y-m-d H:i:s');
                    $adminUserObj->setUpdatedDate(date_create($currentDate));
                    $adminUserObj->setIsForgotStatus(0);
                    /* Set updated date of new user */
                    $em->persist($adminUserObj);
                    $em->flush(); /* Insert Object into DB */

                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');
                    $flashMessenger->addMessage("Your password has been changed successfully.");

                    $url = $this->url()->fromRoute('adminchangepassword');
                    $this->redirect()->toUrl($url);
                } else {
                    /* If old password doesn't match */
                    $error = 'Old Password is wrong.Please try again.';
                    return new ViewModel(array(
                        'error' => $error,
                        'form' => $form,
                    ));
                }
            }
        }
        return array('form' => $form);
    }

    /**
     * This function is used for Change Basic Settings
     * @return \Zend\View\Model\ViewModel
     * @author Manu Garg
     */
    public function adminsettingsAction() {
        $this->checkAdminLoggedInOrNot();
        $this->layout()->pageTitle = 'Admin Basic Details'; /* Setting page title */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $settingData = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_email'));
        $settingsupportData = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_support_email'));
        $form = new AdminForms\AdminSettingsForm(); // create object of login form
        $msg = '';
        if (!empty($settingData)) {
            $form->get('email')->setValue($settingData->getMetaValue());
            $form->get('supportemail')->setValue($settingsupportData->getMetaValue());
            $request = $this->getRequest();
            if ($request->isPost()) {
                /* If request method is Post */

                $formValidator = new AdminForms\Validator\SettingsFormValidator();
                /* Change Password Form Validator Object */
                $form->setInputFilter($formValidator->getInputFilter()); /* Apply filter to form */
                $form->setData($request->getPost()); /* set post data to form */
                $data = $this->getRequest()->getPost(); /* Fetch Post Variables */
                // check if form is valid

                if ($form->isValid()) {
                    //$setting_obj = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey'=>'admin_email'));
                    $currentDate = date_create(date('Y-m-d H:i:s'));
                    $settingData->setMetaValue($data['email']);
                    $settingData->setUpdatedDate($currentDate);
                    $settingsupportData->setMetaValue($data['supportemail']);
                    $settingsupportData->setUpdatedDate($currentDate);
                    $em->persist($settingData);
                    $em->persist($settingsupportData);
                    $em->flush(); /* Insert Object into DB */
                    $successMsg = "Details have been updated successfully.";
                    return new ViewModel(array(
                        'success' => $successMsg,
                        'form' => $form,
                    ));
                }
            }
        }
        return array('form' => $form);
    }

    /**
     * This function is used for Forgot Password.
     * @return \Zend\View\Model\ViewModel
     * @author Aditya Tayal
     */
    public function forgotpasswordAction() {
        $this->layout('layout/layout-login');
        $this->layout()->pageTitle = 'Forgot Password'; /* Setting page title */
        $form = new AdminForms\ForgotPasswordForm(); // create object of login form
        $request = $this->getRequest();
        if ($request->isPost()) {
            $formValidator = new AdminForms\Validator\ForgotPasswordFormValidator();
            /* Change Password Form Validator Object */
            $form->setInputFilter($formValidator->getInputFilter()); /* Apply filter to form */
            $form->setData($request->getPost()); /* set post data to form */
            $data = $this->getRequest()->getPost(); /* Fetch Post Variables */
            $inputemail = trim($data['emailaddr']);
            // check if form is valid
            if ($form->isValid()) {
                $em = $this->getEntityManager();
                $forgotpasData = $em->getRepository('Admin\Entity\Users')->getUserByEmail($inputemail, 'A');
                if (!empty($forgotpasData)) {
                    //Fetch User to update the usertype status to 2 and password with OTP
                    $tmpObj = $em->getRepository('\Admin\Entity\Users')->find($forgotpasData[0]['id']);
                    $pass = rand(100000, 999999);
                    $enc_pass = md5($pass);
                    $tmpObj->setIsForgotStatus(1);
                    $tmpObj->setPassword($enc_pass);
                    $em->persist($tmpObj);
                    $em->flush(); /* Insert Object into DB */
                    $fname = trim($tmpObj->getFirstname());
                    $lname = trim($tmpObj->getLastname());
                    $username = trim($tmpObj->getUsername());

                    //Fetching Email Template
                    $emailObj = $em->getRepository('Admin\Entity\Email')->findOneBy(array('keydata' => 'admin_forgot_pass', 'isActive' => 1));
                    $mailsub = $emailObj->getSubject();
                    $mailcontent = $emailObj->getContent();
                    //$forgotpasData->setIsForgotStatus(1);                                                           

                    $arrpTags = array(
                        '$FIRSTNAME' => $fname,
                        '$LASTNAME' => $lname,
                        '$PASSWORD' => $pass,
                        '$EMAIL' => $inputemail
                    );
                    foreach ($arrpTags as $key => $value) {
                        //replace the keywords of email body with values                        
                        $mailcontent = str_replace($key, trim($value), $mailcontent);
                    }
                    $mail = new Mail\Message();


                    $html = new MimePart($mailcontent);
                    $html->type = "text/html";

                    $body = new MimeMessage();
                    $body->setParts(array($html));

                    $mail->setBody($body);
                    $mail->setFrom('admin@tapetickets.com', 'Support');
                    if ($fname != "") {
                        $mail->addTo($inputemail, $fname);
                    } else {
                        $mail->addTo($inputemail, $username);
                    }
                    $mail->setSubject($mailsub);

                    $transport = new Mail\Transport\Sendmail();
                    $transport->send($mail);
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');
                    $flashMessenger->addMessage("Your One time password has been mailed to admin email address.");
                    $url = $this->url()->fromRoute('adminlogin');
                    $this->redirect()->toUrl($url);
                    //Generate random 6 digit pass Done
                    //Stor in temp var DOne
                    //Generate md5 code of that pass and store in password field of admin user Done
                    //Update the status Done
                    //Mail the unencrypoted pass                    
                } else {
                    $error = "This email address is not registered with us";
                    return new ViewModel(array(
                        'error' => $error,
                        'form' => $form,
                    ));
                }
            }
        }
        return array('form' => $form);
    }

    /**
     * This function is used for checkAdminLoggedInOrNot
     * @author Manu Garg
     * @return 
     */
    public function checkAdminLoggedInOrNot() {
        /* checking if user logged in or not */
        $admin_session = new Container('admin');
        $username = $admin_session->username;
        if (empty($username)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('adminlogin');
        }
    }

}
