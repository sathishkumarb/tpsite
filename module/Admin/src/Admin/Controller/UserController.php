<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * This class ic used for Manage Users.
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Admin\Form as AdminForms;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class UserController extends AbstractActionController {

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

    public function __construct() {
        
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
     *  This function is used for list of users.
     *  @author Manu Garg
     */
    public function listusersAction() {
        $this->layout('layout/adminlayout');
        $this->layout()->pageTitle = 'List User'; /* Setting page title */

        return new ViewModel();
    }

    /**
     * This function is used for ajaxuserslist - User Data in grid.
     * @return json -Json Data for Ajax Request
     * @author Manu Garg
     */
    public function ajaxuserslistAction() {
        $em = $this->getEntityManager();
        $basePath = $this->getRequest()->getBasePath();
        $request = $this->getRequest();
        $userChangeStatusUrl = $this->url()->fromRoute('userchangestatus');
        $userDeleteUrl = $this->url()->fromRoute('userdelete');
        $sqlArr['searchKey'] = $request->getQuery('sSearch');
        $sqlArr['sortcolumn'] = $request->getQuery('iSortCol_0');
        $sqlArr['sorttype'] = $request->getQuery('sSortDir_0');    // desc or asc 
        $sqlArr['iDisplayStart'] = $request->getQuery('iDisplayStart');  // offset
        $sqlArr['sEcho'] = $request->getQuery('sEcho');
        $sqlArr['limit'] = $request->getQuery('iDisplayLength');
        $userData = $em->getRepository('\Admin\Entity\Users')->getUsersListingAtAdminForDataTable($sqlArr, $userChangeStatusUrl, $userDeleteUrl, $basePath);
        echo json_encode($userData);
        exit();
    }

    /**
     * This function is used for userchangestatus - AJAX call to change the status of user. 
     * @return string 1-success
     */
    public function userchangestatusAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $changeType = $this->params('type');
        $userId = $this->params('userId');

        if (!empty($changeType) && !empty($userId)) {
            $userObj = $em->getRepository('\Admin\Entity\Users')->find($userId);
            if (!empty($userObj)) {
                if ($changeType == "active") {
                    $userObj->setStatus('1');
                } else {
                    $userObj->setStatus('0');
                }
                $userObj->setUpdatedDate(date_create(date('Y-m-d H:i:s')));
                $em->persist($userObj);
                $em->flush();
                echo "1";
                exit;
            } else {
                exit;
            }
        } else {
            exit;
        }
    }

    /**
     * This function is used for delete user.
     */
    public function userdeleteAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */

        $userId = $this->params('userId');
        if (empty($userId)) {
            echo "2";
        } else {
            $userObj = $em->getRepository('\Admin\Entity\Users')->find($userId);
            if (!empty($userObj)) {
                $userObj->setStatus(2);
                $em->persist($userObj);
                $em->flush();
                echo 1;
            } else {
                echo "3";
            }
        }
        exit();
        //return new ViewModel(array('users'=>$users));
    }

    /**
     * This function is used for edit user.
     */
    public function usereditAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $this->layout()->pageTitle = 'Edit User';
        $userId = $this->params('userId');
        $msg = '';
        if (empty($userId)) {
            echo "2";
        } else {
            $userObj = $em->getRepository('\Admin\Entity\Users')->find($userId);
            if (!empty($userObj)) {
                $firstName = $userObj->getFirstName();
                $lastName = $userObj->getLastName();
                $phone = $userObj->getPhone();
                $userRole = $userObj->getUserRole();
                $countryId = $userObj->getCountry();
                $cityId = $userObj->getCity();
                $zip = $userObj->getZipCode();
//                $countryObj = $em->getRepository('\Admin\Entity\Countries')->getCountryList();
                //$country = $countryObj->getCountryName();
                //$cityObj = $em->getRepository('\Admin\Entity\City')->find($cityId);            
                //$city = $country                
                $form = new AdminForms\EditUserForm($em, $countryId); // create object of login form
                $form->get('firstname')->setValue($firstName);
                $form->get('lastname')->setValue($lastName);
                $form->get('phone')->setValue($phone);
                $form->get('roles')->setValue($userRole);
                //$form->get('country')->setValueOptions($countryObj);
                $form->get('country')->setValue($countryId);
                $form->get('zip')->setValue($zip);
                if ($countryId != "" && $cityId != "") {
                    $form->get('city')->setValue($cityId);
                }
                $request = $this->getRequest();
                if ($request->isPost()) {
                    $formValidator = new AdminForms\Validator\EditUserFormValidator();
                    $form->setInputFilter($formValidator->getInputFilter());
                    $formdata = $request->getPost();
                    $form->setData($request->getPost()); /* set post data to form */
                    if ($form->isValid()) {
                        $fname = $formdata['firstname'];
                        $lname = $formdata['lastname'];
                        $phone = $formdata['phone'];
                        $roles = $formdata['roles'];
                        $country = $formdata['country'];
                        $city = $formdata['city'];
                        $zip = $formdata['zip'];
                        $userObj->setFirstName($fname);
                        $userObj->setLastName($lname);
                        $userObj->setPhone($phone);
                        $userObj->setUserRole($roles);
                        $userObj->setCountry($country);
                        $userObj->setCity($city);
                        $userObj->setZipCode($zip);
                        $em->persist($userObj);
                        $em->flush();
                        $flashMessenger = $this->flashMessenger();
                        $flashMessenger->setNamespace('success');
                        $msg = "User has been updated successfully";
                        $flashMessenger->addMessage($msg);
                        return $this->redirect()->toRoute('users');
                    }
                }
            } else {
                echo "3";
            }
        }
        return array('form' => $form, 'success' => $msg, 'userId' => $userId);
    }

    /**
     * This function is used for get city is ajax call.
     */
    public function getcityAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $countryId = $this->params('countryId');
        $city_str = "<option value=''>--- Select City ---</option>";
        if (($countryId == "") || ($countryId == 0)) {
            echo $city_str;
            die;
        }

        $CityObj = $em->getRepository('\Admin\Entity\City')->findBy(array('countryId' => $countryId));

        foreach ($CityObj as $val) {
            $city_str .= '<option value="' . $val->getid() . '">' . $val->getCityName() . '</option>';
            //$selectData[$val->getid()] = $val->getCityName();
        }
        echo $city_str;
        die;
    }

    /**
     * Function to add user from admin     
     * @author Aditya
     */
    public function useraddAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $this->layout()->pageTitle = 'Add User'; /* Setting page title */
        $msg = "";
        $form = new AdminForms\AddUserForm($em);
        $form->get('submitbutton')->setValue('Add');
        $request = $this->getRequest();
        $userObj = new Entities\Users();
        if ($request->isPost()) {
            $formValidator = new AdminForms\Validator\AddUserFormValidator();
            $form->setInputFilter($formValidator->getInputFilter());
            $formdata = $request->getPost();
            $form->setData($request->getPost()); /* set post data to form */
            if ($form->isValid()) {
                $checkIfEmailAlreadyExist = $em->getRepository('\Admin\Entity\Users')->checkIfEmailAlreadyExist($formdata['email']);
                if (empty($checkIfEmailAlreadyExist)) {
                    $pass = rand(100000, 999999);
                    $enc_pass = md5($pass);
                    $fname = $formdata['firstname'];
                    $lname = $formdata['lastname'];
                    $phone = $formdata['phone'];
                    $email = $formdata['email'];
                    $role = $formdata['roles'];
                    $country = $formdata['country'];
                    $city = $formdata['city'];
                    $zip = $formdata['zip'];
                    $userObj->setFirstName($fname);
                    $userObj->setLastName($lname);
                    $userObj->setPassword($enc_pass);
                    $userObj->setPhone($phone);
                    $userObj->setEmail($email);
                    $userObj->setUserType('N');
                    $userObj->setUserRole($role);
                    $userObj->setCountry($country);
                    $userObj->setCity($city);
                    $userObj->setZipCode($zip);
                    $userObj->setStatus(1);
                    $em->persist($userObj);
                    $em->flush();
                    $emailObj = $em->getRepository('Admin\Entity\Email')->findOneBy(array('keydata' => 'admin_register_user', 'isActive' => 1));
                    $mailsub = $emailObj->getSubject();
                    $mailcontent = $emailObj->getContent();
//                    $forgotpasData->setIsForgotStatus(1);
                    $arrpTags = array(
                        '$FIRSTNAME' => $fname,
                        '$LASTNAME' => $lname,
                        '$PASSWORD' => $pass,
                        '$EMAIL' => $email
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
                        $mail->addTo($email, $fname);
                    } else {
                        $mail->addTo($email, $username);
                    }
                    $mail->setSubject($mailsub);
                    $transport = new Mail\Transport\Sendmail();
                    $transport->send($mail);
                    $flashMessenger = $this->flashMessenger();
                    $flashMessenger->setNamespace('success');
                    $msg = "User has been added successfully";
                    $flashMessenger->addMessage($msg);
                    return $this->redirect()->toRoute('users');
                } else {
                    $error = "Email Id already exists";
                    return array('form' => $form, 'error' => $error);
                }
            }
        }
        return array('form' => $form, 'success' => $msg);
    }

    public function orderhistoryAction() {
        $this->layout()->pageTitle = 'User Booking History'; /* Setting page title */
        $userId = $this->params('userid');
        return array(
            'userId' => $userId
        );
    }

    /**
     * Function to display user booking history
     * @author Aditya
     */
    public function userbookinghistoryAction() {
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $request = $this->getRequest();
        $basePath = $this->getRequest()->getBasePath();
        $userId = $this->params('userid');
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
        if (!empty($userObj)) {
            $request = $this->getRequest();
            $sqlArr['searchKey'] = $request->getQuery('sSearch');
            $sqlArr['sortcolumn'] = $request->getQuery('iSortCol_0');
            $sqlArr['sorttype'] = $request->getQuery('sSortDir_0');    // desc or asc 
            $sqlArr['iDisplayStart'] = $request->getQuery('iDisplayStart');  // offset
            $sqlArr['sEcho'] = $request->getQuery('sEcho');
            $sqlArr['limit'] = $request->getQuery('iDisplayLength');
            $bookingObj = $em->getRepository('Admin\Entity\UserBooking')->getUsersOrderHistory($userId, $sqlArr, $basePath);
            echo json_encode($bookingObj);
        }
        exit();
    }

    public function userbookingdetailAction() {
        $this->layout()->pageTitle = 'Order Details'; /* Setting page title */
        $em = $this->getEntityManager(); /* Call Entity Manager */
        $orderId = $this->params('orderid');
        $order_details = array();
        $event_details = array();
        $billing_details = array();
        if (!isset($orderId) || ($orderId == "")) {
            return $this->redirect()->toRoute('users');
        }
        $orderObj = $em->getRepository('Admin\Entity\UserBooking')->findOneBy(array('id' => $orderId));
        if (empty($orderObj)) {
            return $this->redirect()->toRoute('users');
        }
        $seatObj = $em->getRepository('Admin\Entity\SeatOrder')->findBy(array('booking' => $orderObj));
        $seat_no = "";
        foreach ($seatObj as $seat) {
            $seat_no[$seat->getTicketType()][] = $seat->getSeatNo();
        }
        $order_details['orderid'] = $orderId;
        $order_details['txn_no'] = $orderObj->getBookingOrderNo();
        $order_details['booking_date'] = $orderObj->getBookingMadeDate()->format('M d, Y');
        $order_details['seats'] = $orderObj->getBookingSeatCount();
        $billing_details['name'] = $orderObj->getFirstName() . " " . $orderObj->getLastName();
        $billing_details['email'] = $orderObj->getEmail();
        $billing_details['phone'] = $orderObj->getPhoneNo();
        $billing_details['card'] = substr_replace($orderObj->getCardNo(), 'xxxx xxxx xxxx ', 0, 12);
        $billing_details['address'] = $orderObj->getStreetAddress() . ", " . $orderObj->getCity() . " ," . $orderObj->getCountry();
        $billing_details['amt_paid'] = $orderObj->getBookingTotalPrice();
        $event_details['name'] = $orderObj->getEvent()->getEventName();
        $event_details['datetime'] = $orderObj->getEventDate()->format('M d, Y') . "  " . $orderObj->getEventTime()->format('h:i A');
        return array('order_details' => $order_details, 'billing_details' => $billing_details, 'event_details' => $event_details, 'seat_details' => $seat_no);
    }

}
