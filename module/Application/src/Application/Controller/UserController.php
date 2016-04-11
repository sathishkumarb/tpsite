<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DOMPDFModule\View\Model\PdfModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Zend\Filter\Encrypt;
use Zend\Session\SessionManager;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Session\Container;
use Admin\Entity as Entities;
use Application\Form as Forms;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Zend\Mail as Mail;
use Zend\Mail\Message;
use Zend\Mime as Mime;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Config\Config; //added by Yesh
use Zend\Barcode\Barcode; //added by Yesh

class UserController extends AbstractActionController {

    protected $em;
    protected $authservice;

    public function onDispatch(MvcEvent $e) {
        /* Set Default layout for all the actions */
        $this->layout('layout/layout');
        $em = $this->getEntityManager();
        $cities = $em->getRepository('\Admin\Entity\City')->findBy(array('supported' => 1));
        $categories = $em->getRepository('\Admin\Entity\Categories')->findBy(array('status' => 1));
        $this->layout()->cities = $cities;
        $this->layout()->categories = $categories;
        $user_session = new Container('user');
        $userid = $user_session->userId;
        if (empty($userid)) {
            return $this->redirect()->toRoute('home');
        } else {
            $msg = 'You are already logged in.';
            $status = 1;
            $this->layout()->setVariable('userId', $user_session->userId);
            $this->layout()->setVariable('username', $user_session->userName);
            $username = $user_session->userName;
            $tmp_user = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId);
            $city = $tmp_user->getCity();
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $this->layout()->userCity = $cityObj->getCityName();
            }
        }
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
	
	/** Function to send ticket email
     * @author Sathish
     */
	public function sendticketemailAction(){
        $request = $this->getRequest();
        $em = $this->getEntityManager();
        
        if ($request->isPost()) {
            $postedData = $request->getPost();
			
			$user_session = new Container('user');   
			$orderStatus = $user_session->orderStatus;

			$adminemailObj = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_email'));
			$supportemailObj = $em->getRepository('Admin\Entity\Settings')->findOneBy(array('metaKey' => 'admin_support_email'));

			$adminemail = $adminemailObj->getMetaValue();
			$supportemail = $supportemailObj->getMetaValue();

			if (!empty($user_session->userId)) {
				
				ini_set('allow_url_include', 'on');

				$commonPlugin = $this->Common();
                $base_url = $commonPlugin->getBasePathOfProj(); //return http://aditya.devstage/tapetickets/public/        
                $dompdfUrl = $_SERVER['DOCUMENT_ROOT'] . "/tapetickets/public/assets/frontend/classes/dompdf/dompdf_config.inc.php";
                $file_location = $_SERVER['DOCUMENT_ROOT'] . "/tapetickets/public/uploads/ticketpdf/test1.pdf";
                $css_url = $base_url . 'assets/frontend/css/theme-style.css';
                $img_url = $base_url . 'assets/frontend/';
				$base_url = $commonPlugin->getBasePathOfProj(); //return http://aditya.devstage/tapetickets/public/        

				$css_url = $base_url . 'assets/frontend/css/theme-style.css';
				$img_url = $base_url . 'assets/frontend/';
				//require_once $dompdfUrl;


				$em = $this->getEntityManager();
				
				$seat_obj = array();
				$booking_obj = array();
				$event_obj = array();
				$order_obj = array();
				$seatsData = array(); //booked seats array
				$userData = array();
				$bookingData = array();
				$eventData = array();
				$ticket_type = "";
				$seat_number = "";
				$i = 1;
				$bookingId = $postedData['bookingid'];
				$zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->findBy(array('userId' => $user_session->userId, 'bookingId' => $bookingId));
				if (!empty($zoneSeatsObj)) {
					$status = TRUE;
					$eventId = $zoneSeatsObj[0]->getEventId();
					$scheduleId = $zoneSeatsObj[0]->getScheduleId();
				} else {
					$view = FALSE; //disable view order msg
					//return $this->redirect()->toRoute('home');
				}
				//data objects
        	$usersObj = $em->getRepository('\Admin\Entity\Users')->find($user_session->userId); //get Users Info
        	$userBookingObj = $em->getRepository('\Admin\Entity\UserBooking')->find($bookingId);  //get User Booking info
        	$eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId); //get Event info
        	$eventScheduleObj = $em->getRepository('\Admin\Entity\EventSchedule')->find($scheduleId); //get Event Schedule info
        /**
         * if !empty($zoneSeatsObj)
         */
        
            //create userInfo Array
            $userData['salutation'] = $usersObj->getSalutation();
            $userData['firstname'] = $usersObj->getFirstName();
            $userData['lastname'] = $usersObj->getLastName();
            $userData['nationality'] = $usersObj->getNationality();
            $userData['email'] = $usersObj->getEmail();
            if($usersObj->getDateOfBirth()) $userData['dateofbirth'] = $usersObj->getDateOfBirth()->format('m-d-Y');
            $userData['internationalcode'] = $usersObj->getInternationalCode();
            $userData['areacode'] = '04'; //have to have a field in table
            $userData['phonenumber'] = $usersObj->getPhone();
            $userData['addressline1'] = $usersObj->getAddresslineOne();
            $userData['addressline2'] = $usersObj->getAddresslineTwo();
            $userData['addressline3'] = $usersObj->getAddresslineThree();
            $city = $usersObj->getCity();
            //get city if not empty
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $userData['city'] = $cityObj->getCityName();
            }
            $userData['state'] = 'dubai'; //have to have a field in table
            $userData['countrycode'] = 'AE'; //have to have a field in table
            //create bookingInfo Array
            $bookingData['orderId'] = $userBookingObj->getId();
            $bookingData['EventTime'] = $userBookingObj->getEventTime()->format('h:i A');
            $bookingData['EventDate'] = $userBookingObj->getEventDate()->format('F d, Y');
            $bookingData['CardType'] = $userBookingObj->getCardType();
            $bookingData['CardNo'] = $userBookingObj->getCardNo();
            $bookingData['ExpiryMonth'] = $userBookingObj->getExpiryMonth();
            $bookingData['ExpiryYear'] = $userBookingObj->getExpiryYear();
            $bookingData['FirstName'] = $userBookingObj->getFirstName();
            $bookingData['LastName'] = $userBookingObj->getLastName();
            $bookingData['BookingMadeDate'] = $userBookingObj->getBookingMadeDate()->format('F d, Y');
            $bookingData['totalAmount'] = $userBookingObj->getBookingTotalPrice();
            $bookingData['status'] = $userBookingObj->getStatus();
            if ($bookingData['status'] === 5) {
                $error = TRUE;
            }
            //create Event Info Array
            $eventData['Performancecode'] = $eventObj->getPerfCode();
            $eventData['EventName'] = $eventObj->getEventName();
            $eventData['EventDesc'] = $eventObj->getEventDesc();
            $eventData['EventAddress'] = $eventObj->getEventAddress();
            $eventData['EventZip'] = $eventObj->getEventZip();
            $eventData['EventVenueTitle'] = $eventObj->getEventVenueTitle();
            $eventData['EventArtist'] = $eventObj->getEventArtist();
            $city = $eventObj->getEventCity();
            //get city if not empty
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $eventData['EventCity'] = $cityObj->getCityName();
                $eventCity = $eventData['EventCity'];
                if ($eventCity === 'Dubai') {
                    $checked = TRUE;
                }
            }
            $eventData['EventImageMedium'] = $eventObj->getEventImageMedium(); //added by Sathish - 13012016
            $tmpObj = (object) array(); //tmp array object
            $tmpSeats = array(); //tmp array
            $zoneId = 0;
            $loop = 0;
            $count = count($zoneSeatsObj);
				
            //creating an object using booked zone seats
            foreach ($zoneSeatsObj as $row) {
                $loop ++; //count loop
                //first time OR when $zoneId equal with current Id
                if (($zoneId === 0) || ($zoneId === $row->getZoneId())) {
                    $zoneId = $row->getZoneId(); //current zoneId
                    $seatId = $row->getId();
                    $eventName = $eventObj->getEventName();
                    $eventDateTime = $userBookingObj->getEventDate()->format('dmy') . '' . $userBookingObj->getEventTime()->format('Gi');
                    $rowColId = $row->getRowId() . '_' . $row->getColId();
                    $seatLabel = $row->getSeatLabel(); //get seatLabel
                    $barCode = '';
                    if ($orderStatus) {
                        //if new
                        $barCode = $this->getNewCustomBarCode($bookingId, $eventName, $eventDateTime, $zoneId, $seatId, $rowColId);
                    } else {
                        //if old
                        $barCode = $row->getBarcode();
                        $checked = FALSE; //
                        $view = FALSE;
                    }
                    array_push($tmpSeats, array('seatLabel' => $seatLabel, 'barCode' => $barCode, 'rowColId' => $rowColId, 'seatId' => $seatId)); //push to tmpSeats array
                } else {
                    $mapZoneObj = $em->getRepository('\Admin\Entity\MapZone')->find($zoneId); //get map zone info
                    $tmpObj->zoneTitle = $mapZoneObj->getZoneTitle(); //create tmp key and value
                    $tmpObj->zoneDtcm = $mapZoneObj->getZoneDtcm(); //create tmp key and value
                    $tmpObj->seatIds = $tmpSeats; //create tmp key and value
                    $tmpObj->zonePrice = $mapZoneObj->getZonePrice(); //create tmp key and value
                    array_push($seatsData, $tmpObj); //push to seatsData array
                    $tmpObj = (object) array(); //make null tmp object
                    $tmpSeats = []; //make null tmp seats array
                    $zoneId = $row->getZoneId(); //current zoneId
                    $seatId = $row->getId();
                    $eventName = $eventObj->getEventName();
                    $eventDateTime = $userBookingObj->getEventDate()->format('dmy') . '' . $userBookingObj->getEventTime()->format('Gi');
                    $rowColId = $row->getRowId() . '_' . $row->getColId();
                    $seatLabel = $row->getSeatLabel(); //get seatLabel
                    if ($orderStatus) {
                        //if new
                        $barCode = $this->getNewCustomBarCode($bookingId, $eventName, $eventDateTime, $zoneId, $seatId, $rowColId);
                    } else {
                        //if old
                        $barCode = $row->getBarcode();
                        $checked = FALSE; //
                        $view = FALSE;
                    }
                    array_push($tmpSeats, array('seatLabel' => $seatLabel, 'barCode' => $barCode, 'rowColId' => $rowColId, 'seatId' => $seatId)); //push to tmpSeats array
                }
                //last time
                if ($loop === $count) {
                    $mapZoneObj = $em->getRepository('\Admin\Entity\MapZone')->find($zoneId); //get map zone info
                    $tmpObj->zoneTitle = $mapZoneObj->getZoneTitle(); //create tmp key and value
                    $tmpObj->zoneDtcm = $mapZoneObj->getZoneDtcm(); //create tmp key and value
                    $tmpObj->seatIds = $tmpSeats; //create tmp key and value
                    $tmpObj->zonePrice = $mapZoneObj->getZonePrice(); //create tmp key and value
                    array_push($seatsData, $tmpObj); //push to seatsData array
                    $tmpObj = (object) array(); //make null tmp object
                    $tmpSeats = []; //make null tmp seats array
                }
            }
				
                    $date = date('F d, Y', strtotime($bookingData['EventDate']));
                    $bookingDate = date('F d Y', strtotime($bookingData['BookingMadeDate']));
                    $time = date('h:i A', strtotime($bookingData['EventTime']));
    				$uri = $this->getRequest()->getUri();
                    $basePath = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
                    $this->renderer = $this->getServiceLocator()->get('ViewRenderer');  
                    $body = $this->renderer->render('application/user/sendticketemail.phtml', array('seatsData'=>$seatsData,'bookingData'=>$bookingData,'date'=>$date,'time'=>$time,'eventData'=>$eventData,'basePath'=>$basePath));
				    $htmlPart = new MimePart($body);
                    $htmlPart->type = "text/html";
                      $textPart = new MimePart($body);
                      $textPart->type = "text/plain";
                      $body = new MimeMessage();
                      $body->setParts(array($textPart, $htmlPart));
                      $message = new Mail\Message();
  $message->setFrom($adminemail);
$userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
                $emailSubject = "Tape Tickets Event ticket(s)";  
                //$message->addTo("anpmtp@gmail.com");
  if (!empty($user_session->userId)) {
                    $message->addTo($userObj->getEmail(), $userObj->getFirstName() . " " . $userObj->getLastName());
                } else {
                /* Need to replace with admin email if need to send email to admin */
                    $message->addTo($supportemail, "tapetickets");
                }
  //$message->addTo($userObj->getEmail(), $userObj->getFirstName() . " " . $userObj->getLastName());
  //$message->addReplyTo($reply);      

  //$message->setSender("Tapeticket");
  $message->setSubject($emailSubject);
  $message->setEncoding("UTF-8");
  $message->setBody($body);
  $message->getHeaders()->get('content-type')->setType('multipart/alternative');
				
				// To set view variables
        //$eng = $this->getServiceLocator()->get('viewpdfrenderer')->getEngine();
        //die($tmphtml);
        //'default_paper_size' => 'letter'
        //$eng->set_base_path($css_url);

        //$eng->load_html($tmphtml);
       // $eng->render();
        //$pdfCode = $eng->output();
			
				// To set view variables
				// set basic email data including sender, receiver, message and subject line			
                
				
				 
				
				$transport = new Mail\Transport\Sendmail();
				$transport->send($message);
				echo $userObj->getEmail()."==".$userObj->getFirstName();
				exit;
				}
        	
    	}
	}

    /** Function to change user password
     * @author Aditya
     */
    public function userchangepasswordAction() {
        //$this->checkUserLoggedInOrNot();                
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        if (!empty($userid)) {
            $msg = 'You are already logged in.';
            $status = 1;
            $this->layout()->setVariable('userId', $user_session->userId);
        } else {
            $this->layout()->setVariable('userId', '');
            $msg = "Kindly Login to Change Password";
            $tmp_arr = json_encode(array('status' => 0, 'msg' => $msg));
        }
        $form = new Forms\ChangePasswordForm(); // create object of login form        
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            /* If request method is Post */
            $formValidator = new Forms\Validator\ChangePasswordFormValidator();
            /* Change Password Form Validator Object */
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($formData);
            $data = array(
                'oldpassword' => $formData['oldpassword'],
                'newpassword' => $formData['newpassword']
            );
            // check if form is valid
            if ($form->isValid()) {
                $em = $this->getEntityManager(); /* Call Entity Manager */

                /* Check with passed password exist in DB */
                //$userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('password' => md5($data['oldpassword']), 'status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
                $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
                $passError = 1;
                if (!empty($userObj)) {
                    $oldPassword = $userObj->getPassword();
                    if ($oldPassword == md5($data['oldpassword'])) {
                        $passError = 0;
                    }
                }
                if (!empty($userObj) && ($passError == 0)) {
                    /* Set new password */
                    $userObj->setPassword(md5($data['newpassword']));
                    $currentDate = date('Y-m-d H:i:s');
                    $userObj->setUpdatedDate(date_create($currentDate));
                    $userObj->setIsForgotStatus(0);
                    /* Set updated date of new user */
                    $em->persist($userObj);
                    $em->flush(); /* Insert Object into DB */
                    $fname = $userObj->getFirstName();
                    $lname = $userObj->getLastName();
                    if (($fname == "") && ($lname == "")) {
                        $fname = "User";
                        $lname = "";
                    }
                    $arrpTags = array(
                        '$FIRSTNAME' => $fname,
                        '$LASTNAME' => $lname,
                        '$EMAIL' => $userObj->getEmail()
                    );
                    $commonPlugin = $this->Common();
                    $emailSent = $commonPlugin->sendEmail($arrpTags, 'frontend_change_password', $user_session->userId);
                    $msg = "Your password has been changed successfully.";
                    $tmp_arr = json_encode(array('status' => 1, 'msg' => $msg));
                } else {
                    /* If old password doesn't match */
                    if (!empty($userObj)) {
                        if ($userObj->getFbUser() == 1) {
                            $error = "Old Password is wrong. Please check OTP in Welcome e-mail for old password.";
                        } else {
                            $error = 'Old Password is wrong. Please try again.';
                        }
                    } else {
                        $error = 'Old Password is wrong. Please try again.';
                    }
                    $tmp_arr = json_encode(array('status' => 1, 'msg' => $error));
                }
            } else {
                $tmp_arr = json_encode(array('status' => 1, 'msg' => 'Please check the Form'));
            }
        }
        echo $tmp_arr;
        die;
    }

    /** Function to display User Profile
     * @author Aditya     
     */
    public function userprofileAction() {
        //$this->checkUserLoggedInOrNot();
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        $this->layout()->pageTitle = "User Profile";
        $em = $this->getEntityManager();
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $ChangepasswordForm = new Forms\ChangePasswordForm();
        $BasicinfoForm = new Forms\BasicInfoForm();
        $countryId = $userObj->getCountry();
        $cityId = $userObj->getCity();
        $UserLocationForm = new Forms\UserLocationForm($em, $countryId);
        $BasicinfoForm->get('firstname')->setValue($userObj->getFirstName());
        $BasicinfoForm->get('lastname')->setValue($userObj->getLastName());
        $BasicinfoForm->get('email')->setValue($userObj->getEmail());
        $BasicinfoForm->get('contactno')->setValue($userObj->getPhone());
        if ($countryId != "") {
            $UserLocationForm->get('country')->setValue($countryId);
        }
        if ($cityId != "") {
            $UserLocationForm->get('city')->setValue($cityId);
        }
        //$form = new Forms\ChangePasswordForm(); // create object of login form                        
        return array('changepassword' => $ChangepasswordForm, 'basicinfo' => $BasicinfoForm, 'locationinfo' => $UserLocationForm, 'username' => $username);
    }

    /** Function to update Basic Information on User Profile Page
     * @author Aditya
     */
    public function userbasicinfoAction() {
        $this->checkUserLoggedInOrNot();
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $user_session = new Container('user');
        $userId = $user_session->userId;
        $form = new Forms\BasicInfoForm(); // create object of login form        
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            /* If request method is Post */
            $formValidator = new Forms\Validator\BasicInfoFormValidator();
            /* Change Password Form Validator Object */
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($formData);
            // check if form is valid
            if ($form->isValid()) {
                $em = $this->getEntityManager(); /* Call Entity Manager */
                /* Check with user exist in DB */
                $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userId));
                if (!empty($userObj)) {
                    /* Set new password */
                    $userObj->setFirstName($formData['firstname']);
                    $userObj->setLastName($formData['lastname']);
                    $userObj->setPhone($formData['contactno']);
                    $currentDate = date('Y-m-d H:i:s');
                    $userObj->setUpdatedDate(date_create($currentDate));
                    /* Set updated date of new user */
                    $em->persist($userObj);
                    $em->flush(); /* Insert Object into DB */
                    $arrpTags = array(
                        '$FIRSTNAME' => $formData['firstname'],
                        '$LASTNAME' => $formData['lastname']
                    );
                    $commonPlugin = $this->Common();
                    $emailSent = $commonPlugin->sendEmail($arrpTags, 'frontend_basicinfo_update', $userId);

                    $msg = "Your Basic Informations has been updated.";
                    $tmp_arr = json_encode(array('status' => 1, 'msg' => $msg));
                } else {
                    $error = 'User is not Active';
                    $tmp_arr = json_encode(array('status' => 0, 'msg' => $error));
                }
            } else {
                $tmp_arr = json_encode(array('status' => 0, 'msg' => 'Please check the Form'));
            }
        }
        echo $tmp_arr;
        die;
    }

    /** Function to Update user Location on usre profile page
     *  @author Aditya
     */
    public function userlocationAction() {
        $this->checkUserLoggedInOrNot();
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $em = $this->getEntityManager();
        $form = new Forms\UserLocationForm($em); // create object of login form        
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            /* If request method is Post */
            $form->setData($formData);
            // check if form is valid            
            $em = $this->getEntityManager(); /* Call Entity Manager */
            /* Check with user exist in DB */
            $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
            if (!empty($userObj)) {
                /* Set new password */
                $userObj->setCountry($formData['country']);
                $userObj->setCity($formData['city']);
                $currentDate = date('Y-m-d H:i:s');
                $userObj->setUpdatedDate(date_create($currentDate));
                /* Set updated date of new user */
                $em->persist($userObj);
                $em->flush(); /* Insert Object into DB */
                $msg = "Location Informations has been updated.";
                $tmp_arr = json_encode(array('status' => 1, 'msg' => $msg));
            } else {
                $error = 'User is not Active';
                $tmp_arr = json_encode(array('status' => 0, 'msg' => $error));
            }
        }
        echo $tmp_arr;
        die;
    }

    /** Function to get city of respective country
     *  @param CountryId
     *  @author Aditya
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

    /** Function to check is user is logged in or not while AJAX
     *  @author Aditya
     */
    public function checkUserLoggedInOrNot() {
        $user_session = new Container('user');
        $userid = $user_session->userId;
        if (empty($userid)) {
            /* if not logged in redirect the user to login page */
            $tmp_arr = json_encode(array('status' => -1, 'msg' => "Not Logged in"));
            echo $tmp_arr;
            die;
        } else {
            $this->layout()->setVariable('userId', $user_session->userId);
        }
    }

    /**
     * Function to display billing address on payment page     
     * @author Aditya Tayal
     */
    public function userbillinginfoAction() {
        $this->checkUserLoggedInOrNot();
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $em = $this->getEntityManager();
        $form = new Forms\BillingAddressForm($em); // create object of login form        
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            $form->setData($formData);
            // check if form is valid            
            $em = $this->getEntityManager(); /* Call Entity Manager */
            /* Check with user exist in DB */
            $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
            $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
            if (!empty($billingObj)) {
                $billingObj = $billingObj[0];
                $billingObj->setFirstName($formData['firstname']);
                $billingObj->setLastName($formData['lastname']);
                $billingObj->setAddress($formData['streetaddress']);
                $billingObj->setCountry($formData['country']);
                $billingObj->setCity($formData['city']);
                $currentDate = date('Y-m-d H:i:s');
                $billingObj->setUpdatedDate(date_create($currentDate));
                /* Set updated date of new user */
                $em->persist($billingObj);
                $em->flush(); /* Insert Object into DB */
                $msg = "Billing Address has been updated.";
            } else {
                $billingObj = new Entities\BillingAddress();
                $billingObj->setFirstName($formData['firstname']);
                $billingObj->setLastName($formData['lastname']);
                $billingObj->setAddress($formData['streetaddress']);
                $billingObj->setCountry($formData['country']);
                $billingObj->setUser($userObj);
                $billingObj->setCity($formData['city']);
                $currentDate = date('Y-m-d H:i:s');
                $billingObj->setCreatedDate(date_create($currentDate));
                $billingObj->setUpdatedDate(date_create($currentDate));
                $em->persist($billingObj);
                $em->flush();
            }
            $tmp_arr = json_encode(array('status' => 1, 'msg' => 'Billing Address has been updated successfully'));
        }
        echo $tmp_arr;
        die;
    }

    /**
     * Payment Detail page     
     * @author Aditya
     */
    public function paymentdetailsAction() {
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        $this->layout()->pageTitle = "Payment Details";
        if (empty($userid)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('home');
        }
        $em = $this->getEntityManager();
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $billingObj = $em->getRepository('Admin\Entity\BillingAddress')->findBy(array('user' => $userObj));
        if (!empty($billingObj)) {
            $billingObj = $billingObj[0];
            $country = $billingObj->getCountry();
        } else {
            $country = "";
        }
        $BillingInfoForm = new Forms\BillingAddressForm($em, $country);
        if (!empty($billingObj)) {
            $fname = $billingObj->getFirstName();
            $lname = $billingObj->getLastName();
            $street_addr = $billingObj->getAddress();
            $country = $billingObj->getCountry();
            $city = $billingObj->getCity();
            $BillingInfoForm->get('firstname')->setValue($fname);
            $BillingInfoForm->get('lastname')->setValue($lname);
            $BillingInfoForm->get('streetaddress')->setValue($street_addr);
            if ($country != "") {
                $BillingInfoForm->get('country')->setValue($country);
            }
            if ($city != "") {
                $BillingInfoForm->get('city')->setValue($city);
            }
        }
        $commonPlugin = $this->Common();
        $basepath = $commonPlugin->getBasePathOfProj();
        $cardObj = new Forms\BillingCardForm($basepath);
        $savedCardsObj = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('user' => $userObj));
        $savedCard = "";
        foreach ($savedCardsObj as $obj) {
            $savedCard[$obj->getId()] = $obj->getTitle();
        }
        //$form = new Forms\ChangePasswordForm(); // create object of login form                        
        return array('billinginfo' => $BillingInfoForm, 'usercardinfo' => $cardObj, 'username' => $username, "savedcard" => $savedCard);
    }

    /**
     * Function to add a card on payment settings page
     * @author Aditya
     */
    public function userCardInfoAction() {
        $this->checkUserLoggedInOrNot();
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        $this->layout()->pageTitle = "Payment Details";
        $em = $this->getEntityManager();
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $formData = $this->getRequest()->getPost()->toArray();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $form = new Forms\BillingCardForm(); // create object of login form        
        /* 'options' => array(
          'value_options' => array("visa"=>$visa_str,"mastercard"=>$mastercard_str,"maestro"=>$maestro_str),
          )
         */
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            /* If request method is Post */
            $form->setData($formData);

            // check if form is valid            
            $formValidator = new Forms\Validator\BillingCardFormValidator();
            $form->setInputFilter($formValidator->getInputFilter());
            $form->setData($formData);
            if ($form->isValid()) {
                $em = $this->getEntityManager(); /* Call Entity Manager */
                /* Check with user exist in DB */
                $cardObj = new Entities\UserCardDetails();
                $cardObj->setTitle($formData['title']);
                $cardObj->setCardType($formData['card_type']);
                $cardObj->setCardNo($formData['cardno']);
                $cardObj->setCardExpiryMonth($formData['month']);
                $cardObj->setCardExpiryYear($formData['year']);
                $cardObj->setUser($userObj);
                $cardObj->setIsDeleted(0);
                $currentDate = date('Y-m-d H:i:s');
                $cardObj->setCreatedDate(date_create($currentDate));
                $cardObj->setUpdatedDate(date_create($currentDate));
                $em->persist($cardObj);
                $em->flush();
                $tmp_arr = json_encode(array('status' => 1, 'msg' => 'Card has been added successfully'));
            } else {
                $tmp_arr = json_encode(array('status' => 0, 'msg' => 'Please check form'));
            }
        }
        echo $tmp_arr;
        die;
    }

    /**
     * Function to get carddetails
     * @author Aditya
     */
    public function getCardDetailsAction() {
        $this->checkUserLoggedInOrNot();
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $id = $this->params('cardid');
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $cardObj = $em->getRepository('Admin\Entity\UserCardDetails')->findOneBy(array('isDeleted' => 0, 'id' => $id, 'user' => $userObj));
        if (!empty($cardObj)) {
            $card['cardno'] = base64_encode($cardObj->getCardNo());
            $card['cardtype'] = $cardObj->getCardType();
            $card['title'] = $cardObj->getTitle();
            $card['month'] = $cardObj->getCardExpiryMonth();
            $card['year'] = $cardObj->getCardExpiryYear();
            $status = 1;
        } else {
            $status = 0;
        }
        echo json_encode(array('card' => $card, 'status' => $status));
        die;
    }

    /**
     * Function to update saved card details
     * @author Aditya
     */
    public function updateCardDetailsAction() {
        $this->checkUserLoggedInOrNot();
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $em = $this->getEntityManager();
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $form = new Forms\BillingCardForm(); // create object of login form        
        $request = $this->getRequest();  /* Fetching Request */
        if ($request->isXmlHttpRequest()) {
            $formData = $this->getRequest()->getPost()->toArray();
            /* If request method is Post */
            $form->setData($formData);
            // check if form is valid            
            $em = $this->getEntityManager(); /* Call Entity Manager */
            /* Check with user exist in DB */
            if ($formData['cardid'] != "") {
                $cardObj = $em->getRepository('Admin\Entity\UserCardDetails')->findBy(array('isDeleted' => 0, 'id' => trim($formData['cardid']), 'user' => $userObj));
                if (!empty($cardObj)) {
                    /* Set new password */
                    $cardObj[0]->setTitle($formData['title']);
                    $cardObj[0]->setCardType($formData['card_type']);
                    $cardObj[0]->setCardNo($formData['card_no']);
                    $cardObj[0]->setCardExpiryMonth($formData['month']);
                    $cardObj[0]->setCardExpiryYear($formData['year']);
                    $cardObj[0]->setIsDeleted(0);
                    $currentDate = date('Y-m-d H:i:s');
                    $cardObj[0]->setUpdatedDate(date_create($currentDate));
                    $em->persist($cardObj[0]);
                    $em->flush();
                    $msg = "Card has been updated successfully";
                    $status = 1;
                    $tmp_arr = json_encode(array('status' => 1, 'msg' => $msg));
                } else {
                    $msg = 'No card is associated with this';
                    $status = 0;
                }
            } else {
                $msg = 'Select valid card';
                $status = 0;
            }
        }
        $tmp_arr = json_encode(array('status' => $status, 'msg' => $msg));
        echo $tmp_arr;
        die;
    }

    /**
     * Function to display ticket preview
     * @author Aditya
     * Edit by Yesh
     */
    public function ticketpreviewAction() {
        $this->layout('layout/eventlayout');
        $this->layout()->pageTitle = "Payment Details";
        $user_session = new Container('user');
        $em = $this->getEntityManager();
        $userId = $user_session->userId;
//        $user_session->orderStatus = FALSE; //oldData=FALSE, newData=TRUE
        $orderStatus = $user_session->orderStatus;
        $bookingId = $this->params('bookingid');
        $basketID = '';
        $orderID = '';
        $eventId = '';
        $scheduleId = '';
        $eventCity = '';
        $dtcmError = FALSE;
        $checked = FALSE;
        $status = FALSE;
        $update = FALSE;
        $view = TRUE;
        $error = FALSE;
        $seatsData = array(); //booked seats array
        $userData = array();
        $bookingData = array();
        $eventData = array();
        // if not logged in redirect the user to login page
        if (empty($userId)) {
            return $this->redirect()->toRoute('home');
        }
        //get Zone Seats info
        $zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->findBy(array('userId' => $userId, 'bookingId' => $bookingId));
        if (!empty($zoneSeatsObj)) {
            $status = TRUE;
            $eventId = $zoneSeatsObj[0]->getEventId();
            $scheduleId = $zoneSeatsObj[0]->getScheduleId();
        } else {
            $view = FALSE; //disable view order msg
            //return $this->redirect()->toRoute('home');
        }
        //data objects
        $usersObj = $em->getRepository('\Admin\Entity\Users')->find($userId); //get Users Info
        $userBookingObj = $em->getRepository('\Admin\Entity\UserBooking')->find($bookingId);  //get User Booking info
        $eventObj = $em->getRepository('\Admin\Entity\Event')->find($eventId); //get Event info
        $eventScheduleObj = $em->getRepository('\Admin\Entity\EventSchedule')->find($scheduleId); //get Event Schedule info
        /**
         * if !empty($zoneSeatsObj)
         */
        if ($status) {
            //create userInfo Array
            $userData['salutation'] = $usersObj->getSalutation();
            $userData['firstname'] = $usersObj->getFirstName();
            $userData['lastname'] = $usersObj->getLastName();
            $userData['nationality'] = $usersObj->getNationality();
            $userData['email'] = $usersObj->getEmail();
            if($usersObj->getDateOfBirth()) $userData['dateofbirth'] = $usersObj->getDateOfBirth()->format('m-d-Y');
            $userData['internationalcode'] = $usersObj->getInternationalCode();
            $userData['areacode'] = '04'; //have to have a field in table
            $userData['phonenumber'] = $usersObj->getPhone();
            $userData['addressline1'] = $usersObj->getAddresslineOne();
            $userData['addressline2'] = $usersObj->getAddresslineTwo();
            $userData['addressline3'] = $usersObj->getAddresslineThree();
            $city = $usersObj->getCity();
            //get city if not empty
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $userData['city'] = $cityObj->getCityName();
            }
            $userData['state'] = 'dubai'; //have to have a field in table
            $userData['countrycode'] = 'AE'; //have to have a field in table
            //create bookingInfo Array
            $bookingData['orderId'] = $userBookingObj->getId();
            $bookingData['EventTime'] = $userBookingObj->getEventTime()->format('h:i A');
            $bookingData['EventDate'] = $userBookingObj->getEventDate()->format('F d, Y');
            $bookingData['CardType'] = $userBookingObj->getCardType();
            $bookingData['CardNo'] = $userBookingObj->getCardNo();
            $bookingData['ExpiryMonth'] = $userBookingObj->getExpiryMonth();
            $bookingData['ExpiryYear'] = $userBookingObj->getExpiryYear();
            $bookingData['FirstName'] = $userBookingObj->getFirstName();
            $bookingData['LastName'] = $userBookingObj->getLastName();
            $bookingData['BookingMadeDate'] = $userBookingObj->getBookingMadeDate()->format('F d, Y');
            $bookingData['totalAmount'] = $userBookingObj->getBookingTotalPrice();
            $bookingData['status'] = $userBookingObj->getStatus();
            if ($bookingData['status'] === 5) {
                $error = TRUE;
            }
            //create Event Info Array
            $eventData['Performancecode'] = $eventObj->getPerfCode();
            $eventData['EventName'] = $eventObj->getEventName();
            $eventData['EventDesc'] = $eventObj->getEventDesc();
            $eventData['EventAddress'] = $eventObj->getEventAddress();
            $eventData['EventZip'] = $eventObj->getEventZip();
            $eventData['EventVenueTitle'] = $eventObj->getEventVenueTitle();
            $eventData['EventArtist'] = $eventObj->getEventArtist();
            $city = $eventObj->getEventCity();
            //get city if not empty
            if (!empty($city)) {
                $cityObj = $em->getRepository('\Admin\Entity\City')->find($city);
                $eventData['EventCity'] = $cityObj->getCityName();
                $eventCity = $eventData['EventCity'];
                if ($eventCity === 'Dubai') {
                    $checked = TRUE;
                }
            }
            $eventData['EventImageMedium'] = $eventObj->getEventImageMedium(); //added by Sathish - 13012016
            $tmpObj = (object) array(); //tmp array object
            $tmpSeats = array(); //tmp array
            $zoneId = 0;
            $loop = 0;
            $count = count($zoneSeatsObj);
            //creating an object using booked zone seats
            foreach ($zoneSeatsObj as $row) {
                $loop ++; //count loop
                //first time OR when $zoneId equal with current Id
                if (($zoneId === 0) || ($zoneId === $row->getZoneId())) {
                    $zoneId = $row->getZoneId(); //current zoneId
                    $seatId = $row->getId();
                    $eventName = $eventObj->getEventName();
                    $eventDateTime = $userBookingObj->getEventDate()->format('dmy') . '' . $userBookingObj->getEventTime()->format('Gi');
                    $rowColId = $row->getRowId() . '_' . $row->getColId();
                    $seatLabel = $row->getSeatLabel(); //get seatLabel
                    $barCode = '';
                    if ($orderStatus) {
                        //if new
                        $barCode = $this->getNewCustomBarCode($bookingId, $eventName, $eventDateTime, $zoneId, $seatId, $rowColId);
                    } else {
                        //if old
                        $barCode = $row->getBarcode();
                        $checked = FALSE; //
                        $view = FALSE;
                    }
                    array_push($tmpSeats, array('seatLabel' => $seatLabel, 'barCode' => $barCode, 'rowColId' => $rowColId, 'seatId' => $seatId)); //push to tmpSeats array
                } else {
                    $mapZoneObj = $em->getRepository('\Admin\Entity\MapZone')->find($zoneId); //get map zone info
                    $tmpObj->zoneTitle = $mapZoneObj->getZoneTitle(); //create tmp key and value
                    $tmpObj->zoneDtcm = $mapZoneObj->getZoneDtcm(); //create tmp key and value
                    $tmpObj->seatIds = $tmpSeats; //create tmp key and value
                    $tmpObj->zonePrice = $mapZoneObj->getZonePrice(); //create tmp key and value
                    array_push($seatsData, $tmpObj); //push to seatsData array
                    $tmpObj = (object) array(); //make null tmp object
                    $tmpSeats = []; //make null tmp seats array
                    $zoneId = $row->getZoneId(); //current zoneId
                    $seatId = $row->getId();
                    $eventName = $eventObj->getEventName();
                    $eventDateTime = $userBookingObj->getEventDate()->format('dmy') . '' . $userBookingObj->getEventTime()->format('Gi');
                    $rowColId = $row->getRowId() . '_' . $row->getColId();
                    $seatLabel = $row->getSeatLabel(); //get seatLabel
                    if ($orderStatus) {
                        //if new
                        $barCode = $this->getNewCustomBarCode($bookingId, $eventName, $eventDateTime, $zoneId, $seatId, $rowColId);
                    } else {
                        //if old
                        $barCode = $row->getBarcode();
                        $checked = FALSE; //
                        $view = FALSE;
                    }
                    array_push($tmpSeats, array('seatLabel' => $seatLabel, 'barCode' => $barCode, 'rowColId' => $rowColId, 'seatId' => $seatId)); //push to tmpSeats array
                }
                //last time
                if ($loop === $count) {
                    $mapZoneObj = $em->getRepository('\Admin\Entity\MapZone')->find($zoneId); //get map zone info
                    $tmpObj->zoneTitle = $mapZoneObj->getZoneTitle(); //create tmp key and value
                    $tmpObj->zoneDtcm = $mapZoneObj->getZoneDtcm(); //create tmp key and value
                    $tmpObj->seatIds = $tmpSeats; //create tmp key and value
                    $tmpObj->zonePrice = $mapZoneObj->getZonePrice(); //create tmp key and value
                    array_push($seatsData, $tmpObj); //push to seatsData array
                    $tmpObj = (object) array(); //make null tmp object
                    $tmpSeats = []; //make null tmp seats array
                }
            }
        }
        $access_token = '';
        //if dubai only
        if ($checked) {
            $nowTime = date("Y-m-d H:i:s");
            $token = $em->getRepository('Admin\Entity\CurlAuth')->checkAccessToken($nowTime);
            if ($token['cnt'] === '0') {
                $token = $em->getRepository('Admin\Entity\CurlAuth')->getAccessToken();
                $access_token = $token->access_token;
                $endTime = date("Y-m-d H:i:s", time() + $token->expires_in);
                $em = $this->getEntityManager();
                try {
                    $curlAuthObj = new Entities\CurlAuth();
                    $curlAuthObj->setToken($access_token);
                    $curlAuthObj->setStartTime($nowTime);
                    $curlAuthObj->setEndTime($endTime);
                    $em->persist($curlAuthObj);
                    $em->flush();
                } catch (Exception $ex) {
                    echo "Caught exception: " . get_class($ex) . "\n";
                    echo "Message: Event Event Map" . $ex->getMessage() . "\n";
                    die();
                }
            } else {
                $access_token = $token['token'];
            }
            //add customer to dtcm if not registered 
            $dtcmCustomerId = '';
            $dtcmCustomerAccount = '';
            $dtcmCustomerAfile = '';
            if ($access_token !== '') {
                $dtcmCustomerId = $usersObj->getDtcmCustomerId();
                $dtcmCustomerAccount = $usersObj->getDtcmCustomerAccount();
                $dtcmCustomerAfile = $usersObj->getDtcmCustomerAfile();
                if (empty($dtcmCustomerId)) {
                    //$str_data = '{"salutation":"Mr","firstname":"Sat","lastname":"kum","nationality":"IN","email":"meet.sathish@gmail.com","dateofbirth":"4-23-2015","internationalcode":"971","areacode":"04","phonenumber":"507156120","addressline1":"Address Line1","addressline2":"Address Line1","addressline3":"Address Line3","city":"dubai","state":"dubai","countrycode":"AE"}';
                    $str_data = json_encode($userData);
                    $customer = $em->getRepository('Admin\Entity\CurlAuth')->addNewCustomer($str_data, $access_token);
                    if (isset($customer->ID)) {
                        $dtcmCustomerId = $customer->ID;
                        $dtcmCustomerAccount = $customer->Account;
                        $dtcmCustomerAfile = $customer->AFile;
                        //update user table
                        $usersObj = $em->getRepository('\Admin\Entity\Users')->find($userId);
                        $usersObj->setDtcmCustomerId($dtcmCustomerId);
                        $usersObj->setDtcmCustomerAccount($dtcmCustomerAccount);
                        $usersObj->setDtcmCustomerAfile($dtcmCustomerAfile);
                        $em->persist($usersObj);
                        $em->flush();
                    } else {
                        $dtcmError = TRUE;
                    }
                }
            }
            //add basket
            $tmpArray = (object) array();
            if (($dtcmCustomerId !== '') && (!$dtcmError)) {
//                $str_data = '{"Channel":"W","Seller":"ATAPE1","Performancecode":"ETES3EL","Area":"SRES","Demand": [{"PriceTypeCode":"T","Quantity":1,"Admits":1,"offerCode":"","qualifierCode":"","entitlement":"","customer":{"Afile":"tel","Account":"600061"}}],"Seats":{"Section":"SRES","Row":"5","Seats":"7-7","RzStr":"5/7-7"},"Fees":[{"Type":5,"Code":"W"}]}';
                $tmpArray->Channel = 'W';
                $tmpArray->Seller = 'ATAPE1';
                $tmpArray->Performancecode = $eventObj->getPerfCode();
                $tmpArray->Area = '';
                $tmpArray->Demand = array(
                    (object) array(
                        'PriceTypeCode' => 'T',
                        'Quantity' => '1',
                        'Admits' => '1',
                        'offerCode' => '',
                        'qualifierCode' => '',
                        'entitlement' => '',
                        'customer' => array(
                            'Afile' => $dtcmCustomerAfile,
                            'Account' => $dtcmCustomerAccount,
                        )
                    ),
                );
                $tmpArray->Seats = array(); //
                $tmpArray->Fees = array(
                    (object) array(
                        'Type' => '5',
                        'Code' => 'W'
                    ),
                );
                $zoneCount = count($seatsData);
                $n = 0;
                for ($i = 0; $i < $zoneCount; $i++) {
                    $area = $seatsData[$i]->zoneDtcm;
                    $tmpArray->Area = $area;
                    $seatIds = $seatsData[$i]->seatIds;
                    foreach ($seatIds as $row) {
                        $rowColId = explode('_', $row['rowColId']);
                        $row = $rowColId[0];
                        $seats = $rowColId[1] . '-' . $rowColId[1];
                        $rzStr = $rowColId[1] . '/' . $seats; //$rowColId[1] //$row
                        $tmpArray->Seats = array(
                            'Section' => $area,
                            'Row' => $row,
                            'Seats' => $seats,
                            'RzStr' => $rzStr
                        );
                        $str_data = json_encode($tmpArray);
//                        echo '<pre>';
//                        print_r($str_data);
//                        echo '</pre>';
                        if ($n === 0) {
                            $basketObj = $em->getRepository('Admin\Entity\CurlAuth')->createNewBasket($str_data, $access_token);
                            if (!empty($basketObj)) {
                                if (isset($basketObj->Id)) {
                                    $basketID = $basketObj->Id; //5562-14730910
                                    $userBookingObj->setBasketId($basketID);
                                    $em->persist($userBookingObj);
                                    $em->flush();
                                } else {
                                    $dtcmError = TRUE;
                                }
                            }
                        } else {
                            $basketObj = $em->getRepository('Admin\Entity\CurlAuth')->addOffersToBasket($str_data, $access_token, $basketID);
                        }
                        $n++;
                    }
                }
                $tmpArray = (object) array();
            }
            if (($basketID !== '') && (!$dtcmError)) {
                $total = str_replace('.', '', $bookingData['totalAmount']);
                //$str_data = '{"Seller":"ATAPE1","customer":{"ID":598699,"Account":600724,"AFile":"tel"},"Payments":[{"Amount":10000,"MeansOfPayment":"EXTERNAL"}]}';
                $tmpArray->Seller = 'ATAPE1';
                $tmpArray->customer = (object) array('ID' => $dtcmCustomerId, 'Account' => $dtcmCustomerAccount, 'AFile' => $dtcmCustomerAfile);
                $tmpArray->Payments = array(
                    (object) array(
                        'Amount' => $total,
                        'MeansOfPayment' => 'EXTERNAL',
                    )
                );
                $str_data = json_encode($tmpArray);
                $orderObj = $em->getRepository('Admin\Entity\CurlAuth')->addNewOrder($str_data, $access_token, $basketID);
                if (!empty($orderObj)) {
                    if (isset($orderObj->OrderId)) {
                        $orderID = $orderObj->OrderId; //20160102,6067
                        $userBookingObj->setOrderId($orderID);
                        $em->persist($userBookingObj);
                        $em->flush();
                    } else {
                        $dtcmError = TRUE;
                    }
                }
                $tmpArray = (object) array();
            }
            if (($orderID !== '') && (!$dtcmError)) {
                $orderViewObj = $em->getRepository('Admin\Entity\CurlAuth')->viewOrder($access_token, $orderID);
                if (!empty($orderViewObj)) {
                    if (isset($orderViewObj->OrderItems)) {
                        $orderItems = $orderViewObj->OrderItems;
                        foreach ($orderItems as $orders) {
                            $tmpSeats = array(); //tmp array
                            $dtcmBarcode = $orders->OrderLineItems[0]->Barcode;
                            $dtcmSeat = $orders->OrderLineItems[0]->Seat;
                            $dtcmSeatRow = $dtcmSeat->Row;
                            $dtcmSeatCol = $dtcmSeat->Seats;
                            if ($dtcmBarcode !== '') {
                                $zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->findOneBy(array(
                                    'userId' => $userId,
                                    'bookingId' => $bookingId,
                                    'scheduleId' => $scheduleId,
                                    'rowId' => $dtcmSeatRow,
                                    'colId' => $dtcmSeatCol
                                ));
                                $seatId = $zoneSeatsObj->getId();
                                $key = 'seatId_' . $seatId; //tmp key
                                $tmpArray->$key = $dtcmBarcode;
                                $zoneSeatsObj->setBarcode($dtcmBarcode);
                                $em->persist($zoneSeatsObj);
                                $em->flush();
                            }
                        }
                        $update = TRUE;
                    } else {
                        $dtcmError = TRUE;
                    }
                }
            }
            //if dtcm error 
            if ($dtcmError) {
                $userBookingObj->getStatus(5);
                $em->persist($userBookingObj);
                $em->flush();
                $view = TRUE;
                $error = TRUE;
                unset($user_session->orderStatus); //unset order Status
                return new ViewModel(array(
                    'error' => $error,
                    'msg' => 'Please login and check later to print your ticket, Sorry for the inconvenience!',
                    'view' => $view,
                    'status' => $status,
                    'bookingData' => $bookingData,
                    'eventData' => $eventData,
                    'userData' => $userData,
                    'seatsData' => $seatsData,
                ));
            }
            //update $seatsData array
            if ($update) {
                foreach ($seatsData as $data) {
                    $seatIds = $data->seatIds;
                    for ($i = 0; $i < count($seatIds); $i++) {
                        $seatIdKey = 'seatId_' . $data->seatIds[$i]['seatId']; //create a key
                        $data->seatIds[$i]['barCode'] = $tmpArray->$seatIdKey;
                    }
                }
            }
            unset($user_session->orderStatus); //unset order Status
            return new ViewModel(array(
                'error' => $error,
                'msg' => 'Please login and check later to print your ticke, Sorry for the inconvenience!',
                'view' => $view,
                'status' => $status,
                'bookingData' => $bookingData,
                'eventData' => $eventData,
                'userData' => $userData,
                'seatsData' => $seatsData,
            ));
        } else {
            return new ViewModel(array(
                'error' => $error,
                'msg' => 'Please login and check later to print your ticket, Sorry for the inconvenience!',
                'view' => $view,
                'status' => $status,
                'bookingData' => $bookingData,
                'eventData' => $eventData,
                'userData' => $userData,
                'seatsData' => $seatsData,
            ));
        }
    }

    /**
     * generate barcode Action
     * @return type image
     * Added by Yesh
     */
    public function generatebarcodeAction() {
        $barcode = $this->params('barcode');
//        echo "json => " . $barcode;
//        die();
        // Only the text to draw is required
        $barcodeOptions = array('text' => $barcode);
        // No required options
        $rendererOptions = array('imageType' => 'png');
        // Draw the barcode in a new image,
        // send the headers and the image
        $barCode = Barcode::render('code39', 'image', $barcodeOptions, $rendererOptions);
        echo $barCode;
        die();
        return $this->getResponse();
    }

    /**
     * get New Custom BarCode
     * @param type $bookingId
     * @param type $eventName
     * @param type $eventDateTime
     * @param type $zoneId
     * @param type $seatId
     * @param type $rowColId
     * @return type $barCode
     * Added by Yesh
     */
    private function getNewCustomBarCode($bookingId, $eventName, $eventDateTime, $zoneId, $seatId, $rowColId) {
        $em = $this->getEntityManager();
        $zoneSeatsObj = $em->getRepository('Admin\Entity\ZoneSeats')->find($seatId);
        $prefix = '' . $bookingId . '' . $eventDateTime . '' . $zoneId . '' . str_replace('_', '', $rowColId) . '';
//        $barCode = uniqid() . $prefix . uniqid();
        $barCode = strtoupper($prefix);
        $zoneSeatsObj->setBarcode($barCode);
        $em->persist($zoneSeatsObj);
        $em->flush();
        return $barCode;
    }

    /**
     * Function to generate ticket's pdf
     * @author Aditya
     */
    public function ticketpdfAction() {
        
        file_put_contents($file_location, $pdfCode);
        die('12');
    }

    public function printticketAction() {
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        if (empty($userid)) {
            /* if not logged in redirect the user to login page */
            return $this->redirect()->toRoute('home');
        }
        $this->layout()->pageTitle = "Payment Details";
        $em = $this->getEntityManager();
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $user_session->userId));
        $bookingId = $this->params('bookingid');
        $seat_obj = array();
        $booking_obj = array();
        $event_obj = array();
        $order_obj = array();
        $ticket_type = "";
        $seat_number = "";
        $i = 1;
        $bookingobj = $em->getRepository('Admin\Entity\UserBooking')->find($bookingId);
        $eventObj = $bookingobj->getEvent();
        $viewModel = new ViewModel();

        if ($user_session->userId == $bookingobj->getUser()->getId()) {
            $ticketsObj = $em->getRepository('Admin\Entity\SeatOrder')->findBy(array('booking' => $bookingobj));
            foreach ($ticketsObj as $ticket) {
                $seat_obj[$i]['seatid'] = $ticket->getId();
                $seat_obj[$i]['seatno'] = $ticket->getSeatNo();
                $seat_obj[$i]['price_single'] = $ticket->getPrice();
                $seat_obj[$i]['entrance'] = $ticket->getEntrance();
                $seat_obj[$i]['ticket_type'] = $ticket->getTicketType();
                $seat_number[$ticket->getTicketType()][] = $ticket->getSeatNo();
                $seat_obj[$i]['redeem_on'] = $ticket->getRedeemOn();
                $seat_obj[$i]['order_date'] = $ticket->getCreatedDate();
                ++$i;
            }
            $booking_obj['total_price'] = $bookingobj->getBookingTotalPrice();
            $booking_obj['event_date'] = $bookingobj->getEventDate();
            $booking_obj['event_time'] = $bookingobj->getEventTime();
            $event_obj['event_name'] = $eventObj->getEventName();
            $event_obj['event_artist'] = $eventObj->getEventArtist();
            $event_obj['event_venue'] = $eventObj->getEventVenueTitle();
            $viewModel->setVariables(array('seatobj' => $seat_obj, 'bookingobj' => $booking_obj, 'eventobj' => $event_obj, 'status' => 1, 'seatnumber' => $seat_number, 'bookingid' => $bookingId))
                    ->setTerminal(true);
        } else {
            $msg = "Invalid Booking Id";
            $status = 0;
            $viewModel->setVariables(array('status' => 0, 'msg' => $msg))
                    ->setTerminal(true);
        }
        return $viewModel;
    }

    /**
     * Function to display MyEvents
     * @author Aditya
     */
    public function myeventAction() {
        $this->layout()->pageTitle = "My Events";
        $em = $this->getEntityManager();
        $commonPlugin = $this->Common();
        $base_url = $commonPlugin->getBasePathOfProj();

        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userid));
        $bookingObj = $em->getRepository('Admin\Entity\UserBooking')->findBy(array('status' => 1, 'user' => $userObj));
        $bookedevents = array();
        $futureevents = array();
        foreach ($bookingObj as $booking) {
            if (date("Y/m/d") > $booking->getEventDate()->format('Y/m/d')) {
                $bookingid = $booking->getId();
                $bookedevents[$bookingid]['id'] = $bookingid;
                $bookedevents[$bookingid]['event_date'] = $booking->getEventDate();
                $bookedevents[$bookingid]['event_time'] = $booking->getEventTime();
                $bookedevents[$bookingid]['event_name'] = $booking->getEvent()->getEventName();
                $bookedevents[$bookingid]['event_venue'] = $booking->getEvent()->getEventVenueTitle();
                $bookedevents[$bookingid]['event_icon'] = $base_url . 'uploads/event/' . $booking->getEvent()->getEventImageSmall();
                $bookedevents[$bookingid]['event_cat_icon'] = $base_url . 'uploads/category/' . $booking->getEvent()->getCategory()->getIcon();
                $bookedevents[$bookingid]['tickets'] = $booking->getBookingSeatCount();
            } else {
                $bookingid = $booking->getId();
                $futureevents[$bookingid]['id'] = $bookingid;
                $futureevents[$bookingid]['event_date'] = $booking->getEventDate();
                $futureevents[$bookingid]['event_time'] = $booking->getEventTime();
                $futureevents[$bookingid]['event_name'] = $booking->getEvent()->getEventName();
                $futureevents[$bookingid]['event_id'] = $booking->getEvent()->getId();
                $futureevents[$bookingid]['event_venue'] = $booking->getEvent()->getEventVenueTitle();
                $futureevents[$bookingid]['event_icon'] = $base_url . 'uploads/event/' . $booking->getEvent()->getEventImageSmall();
                $futureevents[$bookingid]['event_cat_icon'] = $base_url . 'uploads/category/' . $booking->getEvent()->getCategory()->getIcon();
                $futureevents[$bookingid]['tickets'] = $booking->getBookingSeatCount();
            }
        }
        return array('future_events' => $futureevents, 'booked_events' => $bookedevents, 'name' => $username);
    }

    /**
     * Function to display Order History
     * @author Aditya
     */
    public function orderhistoryAction() {
        $this->layout()->pageTitle = "My Orders";
        $em = $this->getEntityManager();
        $commonPlugin = $this->Common();
        $base_url = $commonPlugin->getBasePathOfProj();
        $user_session = new Container('user');
        $userid = $user_session->userId;
        $username = $user_session->userName;
        $userObj = $em->getRepository('Admin\Entity\Users')->findOneBy(array('status' => 1, 'userType' => 'N', 'id' => $userid));
        $bookingObj = $em->getRepository('Admin\Entity\UserBooking')->findBy(array('status' => 1, 'user' => $userObj));
        $bookings = array();
        foreach ($bookingObj as $booking) {
            $bookingid = $booking->getId();
            $bookings[$bookingid]['id'] = $bookingid;
            $bookings[$bookingid]['event_date'] = $booking->getEventDate();
            $bookings[$bookingid]['event_name'] = $booking->getEvent()->getEventName();
            $bookings[$bookingid]['tickets'] = $booking->getBookingSeatCount();
            $bookings[$bookingid]['orderid'] = $booking->getBookingOrderNo();
            $bookings[$bookingid]['total_price'] = $booking->getBookingTotalPrice();
            $bookings[$bookingid]['card'] = $booking->getCardNo();
        }
        return array('booking_history' => $bookings, 'name' => $username);
    }

}
